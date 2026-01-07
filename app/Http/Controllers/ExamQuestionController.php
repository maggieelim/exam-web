<?php

namespace App\Http\Controllers;

use App\Exports\ExamQuestionsExport;
use App\Imports\ExamQuestionTemplateImport;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionAnswer;
use App\Models\ExamQuestionCategory;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;

class ExamQuestionController extends Controller
{

    public function index(Request $request, $exam_code)
    {
        $agent = new Agent();
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $categories = ExamQuestionCategory::where('exam_id', $exam->id)->get();
        $query = $exam->questions()->with('options');

        if ($exam->status === 'ended') {
            $status = 'previous';
        } else {
            $status = $exam->status; // upcoming / ongoing
        }
        // 🔍 Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('badan_soal', 'like', "%$search%")->orWhere('kalimat_tanya', 'like', "%$search%");
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
        if ($agent->isMobile()) {
            return view('exams.mobile.questions_mobile', compact('exam', 'questions', 'categories', 'status'));
        }
        return view('exams.questions', compact('exam', 'questions', 'categories', 'status'));
    }

    public function newQuestionModal(Request $request, $examCode)
    {
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();

        // VALIDASI
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'badan_soal' => 'required|string',
            'kalimat_tanya' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',

            'options' => 'required|array|min:1',
            'options.*.text' => 'required|string',
            'options.*.image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'options.*.is_correct' => 'required',
        ]);

        $category = ExamQuestionCategory::firstOrCreate([
            'exam_id' => $exam->id,
            'name' => trim($validated['category_name']),
        ]);
        // SIMPAN SOAL
        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'category_id' => $category->id,
            'badan_soal' => $validated['badan_soal'],
            'kalimat_tanya' => $validated['kalimat_tanya'],
            'kode_soal' => $exam->exam_code . '-' .
                str_pad(
                    ExamQuestion::where('exam_id', $exam->id)->count() + 1,
                    3,
                    '0',
                    STR_PAD_LEFT
                ),
            'image' => isset($validated['image'])
                ? $validated['image']->store('questions', 'public')
                : null,
        ]);

        // SIMPAN OPSI JAWABAN
        $optionImages = [];
        $optionIndex = 0;

        foreach ($validated['options'] as $opt) {
            $imagePath = isset($opt['image'])
                ? $opt['image']->store('question-options', 'public')
                : null;

            $option = ExamQuestionAnswer::create([
                'exam_question_id' => $question->id,
                'option' => chr(65 + $optionIndex), // A, B, C, ...
                'text' => $opt['text'],
                'image' => $imagePath,
                'is_correct' => isset($opt['is_correct']) ? 1 : 0,
            ]);

            $optionImages[$option->id] = [
                'image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
                'has_image' => !empty($imagePath),
            ];

            $optionIndex++;
        }

        return redirect()
            ->route('exams.questions.' . $exam->status, $exam->exam_code)
            ->with('success', 'Soal baru berhasil ditambahkan!');
    }

    //dipakai atau tidak??
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'exam_id' => 'required|exist:exams,id',
        ]);
        Excel::import(new ExamQuestionTemplateImport($request->exam_id), $request->file('file'));
        return redirect()->route('exams.index')->with('success', 'Soal berhasil diimport dari Excel');
    }

    public function export($exam_code)
    {
        $exam = Exam::with('questions.options', 'questions.category')->where('exam_code', $exam_code)->firstOrFail();
        $fileName = "Soal-{$exam->title}.xlsx";
        return Excel::download(new ExamQuestionsExport($exam), $fileName);
    }

    public function edit(string $id)
    {
        //
    }
    public function newQuestion(Request $request, $examCode) {}

    public function update(Request $request, $examCode, $questionId)
    {
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();
        $question = ExamQuestion::where('exam_id', $exam->id)->findOrFail($questionId);

        if ($request->input('action') === 'anulir') {
            // Semua opsi jadi benar
            $question->update([
                'is_anulir' => true,
            ]);
            foreach ($question->options as $option) {
                $option->update(['is_correct' => 1]);
            }

            // Semua jawaban mahasiswa jadi benar
            foreach ($question->answers as $answer) {
                $answer->update([
                    'is_correct' => 1,
                    'score' => 1,
                ]);
            }

            $exam->update([
                'updated_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil dianulir!',
                'data' => [
                    'question_id' => $question->id,
                    'action' => 'anulir',
                ],
            ]);
        }

        $request->validate([
            'category_id' => 'nullable|exists:exam_question_categories,id',
            'badan_soal' => 'required|string',
            'kalimat_tanya' => 'required|string',
            'options.*.text' => 'nullable|string',
            'options.*.image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $imagePath = $question->image;

        if ($request->filled('delete_image') && $request->delete_image == 1) {
            if ($question->image && \Storage::disk('public')->exists($question->image)) {
                \Storage::delete($question->image);
            }
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            if ($question->image && \Storage::disk('public')->exists($question->image)) {
                \Storage::disk('public')->delete($question->image);
            }
            $imagePath = $request->file('image')->store('questions', 'public');
        }

        $question->update([
            'badan_soal' => $request->badan_soal,
            'kalimat_tanya' => $request->kalimat_tanya,
            'image' => $imagePath,
        ]);

        $correctOptionIds = [];
        foreach ($request->options as $id => $opt) {
            $option = ExamQuestionAnswer::find($id);

            if (!$option || $option->exam_question_id != $question->id) {
                continue;
            }

            $isCorrect = isset($opt['is_correct']) ? 1 : 0;
            $imagePath1 = $option->image;

            // --- DELETE IMAGE ---
            if (isset($opt['delete_image']) && $opt['delete_image'] == 1) {
                if ($option->image && \Storage::disk('public')->exists($option->image)) {
                    \Storage::disk('public')->delete($option->image);
                }
                $imagePath1 = null;
            }

            // --- UPLOAD IMAGE BARU ---
            if ($request->hasFile("options.$id.image")) {
                if ($option->image && \Storage::disk('public')->exists($option->image)) {
                    \Storage::disk('public')->delete($option->image);
                }

                $imagePath1 = $request->file("options.$id.image")
                    ->store('question-options', 'public');
            }

            $option->update([
                'text' => $opt['text'],
                'image' => $imagePath1,
                'is_correct' => $isCorrect,
            ]);

            if ($isCorrect) {
                $correctOptionIds[] = $option->id;
            }
            $optionImages[$option->id] = $imagePath1
                ? asset('storage/' . $imagePath1)
                : null;
        }

        // --- UPDATE JAWABAN MAHASISWA SESUAI OPSI BARU ---
        foreach ($question->answers as $answer) {
            $answer->update([
                'is_correct' => in_array($answer->answer, $correctOptionIds) ? 1 : 0,
                'score' => in_array($answer->answer, $correctOptionIds) ? 1 : 0,
            ]);
        }

        // --- UPDATE META EXAM ---
        $exam->update([
            'updated_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil diperbarui!',
            'data' => [
                'question_id' => $question->id,
                'action' => 'update',
                'image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
                'has_image' => !empty($imagePath),
                'options' => $question->options->mapWithKeys(function ($option) {
                    return [
                        $option->id => [
                            'image_url' => $option->image ? asset('storage/' . $option->image) : null,
                            'has_image' => !empty($option->image)
                        ]
                    ];
                })->toArray()
            ]
        ]);
    }

    public function updateByExcel(Request $request, $examCode)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx',
        ]);

        $exam = Exam::where('exam_code', $examCode)->firstOrFail();
        $rows = Excel::toArray([], $request->file('file'))[0];

        ExamQuestion::where('exam_id', $exam->id)->delete();

        // Loop data excel, skip header (row pertama)
        foreach ($rows as $index => $row) {
            if ($index == 0) {
                continue;
            }

            $category = null;
            if (!empty($row[1])) {
                $category = ExamQuestionCategory::firstOrCreate(['exam_id' => $exam->id, 'name' => trim($row[1])]);
            }

            $question = ExamQuestion::create([
                'exam_id' => $exam->id,
                'category_id' => $category ? $category->id : null,
                'badan_soal' => $row[2] ?? '',
                'kalimat_tanya' => $row[3] ?? '',
                'kode_soal' => $exam->exam_code . '-' . str_pad($index, 3, '0', STR_PAD_LEFT),
            ]);

            // Insert options (A-E)
            $options = ['A', 'B', 'C', 'D', 'E'];
            foreach ($options as $i => $opt) {
                if (isset($row[4 + $i]) && $row[4 + $i] !== '') {
                    ExamQuestionAnswer::create([
                        'exam_question_id' => $question->id,
                        'option' => $opt,
                        'text' => $row[4 + $i],
                        'is_correct' => str_contains($row[9] ?? '', $opt) ? 1 : 0,
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
        try {
            $exam = Exam::where('exam_code', $examCode)->firstOrFail();
            $question = ExamQuestion::where('exam_id', $exam->id)->findOrFail($questionId);

            // Hapus gambar jika ada
            if ($question->image) {
                \Storage::disk('public')->delete($question->image);
            }

            // Hapus semua opsi jawaban terkait
            $question->options()->delete();

            // Hapus soal
            $question->delete();

            // Check if request is AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Soal berhasil dihapus!',
                ]);
            }

            return redirect()
                ->route('exams.questions.' . $exam->status, $exam->exam_code)
                ->with('success', 'Soal berhasil dihapus!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Soal tidak ditemukan',
                    ],
                    404,
                );
            }
            return redirect()->back()->with('error', 'Soal tidak ditemukan');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                    ],
                    500,
                );
            }
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
