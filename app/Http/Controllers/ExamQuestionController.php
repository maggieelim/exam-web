<?php

namespace App\Http\Controllers;

use App\Imports\ExamQuestionTemplateImport;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionAnswer;
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

        $query = $exam->questions()->with('options');

        // 🔍 Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('badan_soal', 'like', "%$search%")
                    ->orWhere('kalimat_tanya', 'like', "%$search%");
            });
        }

        // default urut berdasarkan id ASC
        $questions = $query->orderBy('id', 'asc')->paginate(10);

        return view('exams.questions', compact('exam', 'questions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', ExamQuestion::class);

        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'badan_soal' => 'required|string',
            'kalimat_tanya' => 'required|string',
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'opsi_e' => 'nullable|string',
            'jawaban' => 'required|string',
            'kode_soal' => 'required|string|max:50',
        ]);

        ExamQuestion::create($request->all());

        return redirect()->route('exams.questions.index')->with('success', 'Soal berhasil dibuat');
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
            'exam_id' => 'required|exist:exams,d',
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
        $request->validate([
            'badan_soal'    => 'required|string',
            'kalimat_tanya' => 'required|string',
            'options.*.text' => 'required|string',
        ]);

        $exam = Exam::where('exam_code', $examCode)->firstOrFail();
        $question = ExamQuestion::where('exam_id', $exam->id)->findOrFail($questionId);

        // update soal
        $question->update([
            'badan_soal'    => $request->badan_soal,
            'kalimat_tanya' => $request->kalimat_tanya,
        ]);

        // update options
        foreach ($request->options as $id => $opt) {
            $option = ExamQuestionAnswer::find($id);
            if ($option && $option->exam_question_id == $question->id) {
                $option->update([
                    'text' => $opt['text'],
                    'is_correct' => isset($opt['is_correct']) ? 1 : 0,
                ]);
            }
        }

        // update exam meta
        $exam->update([
            'updated_at' => Carbon::now(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('exams.questions', $exam->exam_code)
            ->with('success', 'Soal berhasil diperbarui!');
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

            $question = ExamQuestion::create([
                'exam_id'       => $exam->id,
                'badan_soal'    => $row[1] ?? '',
                'kalimat_tanya' => $row[2] ?? '',
                'kode_soal'     => $exam->exam_code . '-' . str_pad($index, 3, '0', STR_PAD_LEFT),
            ]);

            // Insert options (A-E)
            $options = ['A', 'B', 'C', 'D', 'E'];
            foreach ($options as $i => $opt) {
                if (!empty($row[3 + $i])) {
                    ExamQuestionAnswer::create([
                        'exam_question_id' => $question->id,
                        'option'           => $opt,
                        'text'             => $row[3 + $i],
                        'is_correct'       => (str_contains($row[8] ?? '', $opt)) ? 1 : 0,
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
