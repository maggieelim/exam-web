<?php

namespace App\Http\Controllers;

use App\Imports\ExamQuestionTemplateImport;
use App\Models\Category;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionAnswer;
use App\Models\ExamQuestionCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExamQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $categories = ExamQuestionCategory::where('exam_id', $exam->id)->get();
        $query = $exam->questions()->with('options');

        // 🔍 Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('badan_soal', 'like', "%$search%")
                    ->orWhere('kalimat_tanya', 'like', "%$search%");
            });
        }
        if ($request->filled('category')) {
            $category = $request->category;
            $query->where(function ($q) use ($category) {
                $q->where('category_id', 'like', "%$category%");
            });
        }

        // default urut berdasarkan id ASC
        $questions = $query->orderBy('id', 'asc')->paginate(10);

        return view('exams.questions', compact('exam', 'questions', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', ExamQuestion::class);

        $request->validate([
            'exam_id'       => 'required|exists:exams,id',
            'category_name' => 'required|string|max:255', // pakai nama kategori dari form
            'badan_soal'    => 'required|string',
            'kalimat_tanya' => 'required|string',
            'opsi_a'        => 'required|string',
            'opsi_b'        => 'required|string',
            'opsi_c'        => 'required|string',
            'opsi_d'        => 'required|string',
            'opsi_e'        => 'nullable|string',
            'jawaban'       => 'required|string',
            'kode_soal'     => 'required|string|max:50',
        ]);

        // cari kategori atau buat baru
        $category = ExamQuestionCategory::firstOrCreate(
            ['exam_id' => $request->exam_id, 'name' => $request->category_name]
        );

        ExamQuestion::create([
            'exam_id'       => $request->exam_id,
            'category_id'   => $category->id,
            'badan_soal'    => $request->badan_soal,
            'kalimat_tanya' => $request->kalimat_tanya,
            'kode_soal'     => $request->kode_soal,
            'created_by'    => auth()->id(),
            'updated_by'    => auth()->id(),
        ]);

        return redirect()->route('exams.questions.index')
            ->with('success', 'Soal berhasil dibuat');
    }

    public function showByKode($kode)
    {
        $soals = ExamQuestion::where('kode_soal', $kode)->get();
        return view('soal.show_by_kode', compact('soals', 'kode'));
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'exam_id' => 'required|exist:exams,id',
        ]);
        Excel::import(new ExamQuestionTemplateImport($request->exam_id), $request->file('file'));
        return redirect()->route('exams.index')->with('success', 'Soal berhasil diimport dari Excel');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $questions = ExamQuestion::where('kode_soal', $id)->get();
        return view('exams.questions.show', compact('questions', 'kode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $examCode, $questionId)
    {
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();
        $question = ExamQuestion::where('exam_id', $exam->id)->findOrFail($questionId);

        if ($request->input('action') === 'anulir') {
            // Semua opsi jadi benar
            foreach ($question->options as $option) {
                $option->update(['is_correct' => 1]);
            }

            // Semua jawaban mahasiswa jadi benar
            foreach ($question->answers as $answer) {
                $answer->update([
                    'is_correct' => 1,
                    'score'      => 1, // kalau bobot skor = 1
                ]);
            }

            $exam->update([
                'updated_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            return back()->with('success', 'Soal berhasil dianulir!');
        }

        // --- VALIDASI ---
        $request->validate([
            'category_id'     => 'nullable|exists:exam_question_categories,id',
            'badan_soal'      => 'required|string',
            'kalimat_tanya'   => 'required|string',
            'options.*.text'  => 'required|string',
            'image'           => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $imagePath = $question->image;

        if ($request->filled('delete_image') && $request->delete_image == 1) {
            if ($question->image && \Storage::disk('public')->exists($question->image)) {
                \Storage::disk('public')->delete($question->image);
            }
            $imagePath = null;
        }


        // --- UPLOAD GAMBAR ---
        if ($request->hasFile('image')) {
            if ($question->image && \Storage::disk('public')->exists($question->image)) {
                \Storage::disk('public')->delete($question->image);
            }
            $imagePath = $request->file('image')->store('questions', 'public');
        }

        // --- UPDATE SOAL ---
        $question->update([
            'badan_soal'    => $request->badan_soal,
            'kalimat_tanya' => $request->kalimat_tanya,
            'image'         => $imagePath,
        ]);

        // --- UPDATE OPTIONS ---
        $correctOptionIds = [];
        foreach ($request->options as $id => $opt) {
            $option = ExamQuestionAnswer::find($id);
            if ($option && $option->exam_question_id == $question->id) {
                $isCorrect = isset($opt['is_correct']) ? 1 : 0;
                $option->update([
                    'text'      => $opt['text'],
                    'is_correct' => $isCorrect,
                ]);

                if ($isCorrect) {
                    $correctOptionIds[] = $option->id;
                }
            }
        }

        // --- UPDATE JAWABAN MAHASISWA SESUAI OPSI BARU ---
        foreach ($question->answers as $answer) {
            $answer->update([
                'is_correct' => in_array($answer->answer, $correctOptionIds) ? 1 : 0,
                'score'      => in_array($answer->answer, $correctOptionIds) ? 1 : 0,
            ]);
        }

        // --- UPDATE META EXAM ---
        $exam->update([
            'updated_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Soal berhasil diperbarui!');
    }

    public function updateByExcel(Request $request, $examCode)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        $exam = Exam::where('exam_code', $examCode)->firstOrFail();

        // Baca file Excel
        $rows = Excel::toArray([], $request->file('file'))[0];

        // Hapus semua soal lama
        ExamQuestion::where('exam_id', $exam->id)->delete();

        // Loop data excel, skip header (row pertama)
        foreach ($rows as $index => $row) {
            if ($index == 0) continue;

            $category = null;
            if (!empty($row[1])) {
                $category = ExamQuestionCategory::firstOrCreate(
                    ['exam_id' => $exam->id, 'name' => trim($row[1])]
                );
            }

            $question = ExamQuestion::create([
                'exam_id'       => $exam->id,
                'category_id'      => $category ? $category->id : null,
                'badan_soal'    => $row[2] ?? '',
                'kalimat_tanya' => $row[3] ?? '',
                'kode_soal'     => $exam->exam_code . '-' . str_pad($index, 3, '0', STR_PAD_LEFT),
            ]);

            // Insert options (A-E)
            $options = ['A', 'B', 'C', 'D', 'E'];
            foreach ($options as $i => $opt) {
                if (!empty($row[4 + $i])) {
                    ExamQuestionAnswer::create([
                        'exam_question_id' => $question->id,
                        'option'           => $opt,
                        'text'             => $row[4 + $i],
                        'is_correct'       => (str_contains($row[9] ?? '', $opt)) ? 1 : 0,
                    ]);
                }
            }
        }

        return back()->with('success', 'Soal berhasil diupdate dari Excel!');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($examCode, $questionId)
    {
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();
        $question = ExamQuestion::where('exam_id', $exam->id)->findOrFail($questionId);

        // hapus semua opsi jawaban terkait
        $question->options()->delete();

        // hapus soal
        $question->delete();

        return redirect()->route('exams.questions', $exam->exam_code)
            ->with('success', 'Soal berhasil dihapus!');
    }
}
