<?php

namespace App\Http\Controllers\PSSK;

use App\Exports\ExamQuestionsExport;
use App\Http\Controllers\Controller;
use App\Imports\ExamQuestionTemplateImport;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionAnswer;
use App\Models\ExamQuestionCategory;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\Title;

class ExamQuestionController extends Controller
{

    public function index(Request $request, $exam_code)
    {
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

        return view('pssk.exams.questions', compact('exam', 'questions', 'categories', 'status'));
    }

    public function newQuestionModal(Request $request, $examCode)
    {
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();

        // VALIDASI
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'badan_soal' => 'required|string',
            'kalimat_tanya' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',

            'options' => 'required|array|min:1',
            'options.*.text' => 'required|string',
            'options.*.image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
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
            'cpmk' => 'required',
            'badan_soal' => 'required|string',
            'kalimat_tanya' => 'nullable|string',
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
            'cpmk' => $request->cpmk,
            'badan_soal' => $request->badan_soal,
            'kalimat_tanya' => $request->kalimat_tanya ?: '',
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
        ExamQuestionCategory::where('exam_id', $exam->id)->delete();

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
            $correctAnswers = strtoupper($row[9] ?? '');
            $options = ['A', 'B', 'C', 'D', 'E'];
            foreach ($options as $i => $opt) {
                if (isset($row[4 + $i]) && $row[4 + $i] !== '') {
                    ExamQuestionAnswer::create([
                        'exam_question_id' => $question->id,
                        'option' => $opt,
                        'text' => $row[4 + $i],
                        'is_correct' => str_contains($correctAnswers, $opt) ? 1 : 0,
                    ]);
                }
            }
        }

        return back()->with('success', 'Soal berhasil diupdate dari Excel!');
    }

    public function updateByWord(Request $request, $examCode)
    {
        $request->validate([
            'word_file' => 'required|mimes:docx',
        ]);

        $exam = Exam::where('exam_code', $examCode)->firstOrFail();

        ExamQuestion::where('exam_id', $exam->id)->delete();
        ExamQuestionCategory::where('exam_id', $exam->id)->delete();

        // Load dokumen Word
        $phpWord = PhpWordIOFactory::load($request->file('word_file')->getPathname());

        // Ekstrak semua teks dari dokumen
        $fullText = $this->extractTextFromPhpWord($phpWord);
        $fullText = preg_replace('/\r\n?/', "\n", $fullText); // Normalize line endings
        $fullText = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F\xA0]/u', ' ', $fullText); // Remove non-printable chars

        $questions = $this->parseQuestionsFromText($fullText);

        if (empty($questions)) {
            return back()->with('error', 'Tidak ada soal yang terdeteksi. Pastikan format dokumen sesuai template!');
        }

        $this->saveQuestionsToDatabase($questions, $exam);

        return back()->with('success', count($questions) . ' soal berhasil diupload dari Word!');
    }

    private function extractTextFromPhpWord($phpWord): string
    {
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {

                if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                    $text .= $element->getText() . "\n";
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    $lineText = '';
                    foreach ($element->getElements() as $textElement) {
                        if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                            $lineText .= $textElement->getText();
                        }
                    }
                    if (!empty(trim($lineText))) {
                        $text .= $lineText . "\n";
                    }
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\Title) {
                    if ($element->getText() instanceof \PhpOffice\PhpWord\Element\TextRun) {
                        foreach ($element->getText()->getElements() as $textElement) {
                            if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                                $text .= $textElement->getText();
                            }
                        }
                    } else {
                        $text .= (string) $element->getText();
                    }
                    $text .= "\n";
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\ListItem) {
                    // Handle ListItem dengan lebih baik
                    $depth = $element->getDepth();
                    $listText = $element->getText();

                    // Coba deteksi style numbering
                    $style = $element->getStyle();
                    $styleName = $style ? $style->getStyleName() : '';

                    // Deteksi tipe numbering dari style name
                    if (preg_match('/List(Number|Alpha|Bullet)/i', $styleName, $matches)) {
                        $type = strtolower($matches[1]);

                        // Gunakan static counter per depth
                        static $counters = [];
                        $key = $depth . '_' . $type;

                        if (!isset($counters[$key])) {
                            $counters[$key] = 1;
                        } else {
                            $counters[$key]++;
                        }

                        switch ($type) {
                            case 'number':
                                $prefix = $counters[$key] . '. ';
                                break;
                            case 'alpha':
                                // A, B, C, ...
                                $prefix = chr(64 + $counters[$key]) . '. ';
                                break;
                            case 'bullet':
                            default:
                                $prefix = '• ';
                                break;
                        }
                    } else {
                        // Fallback: lihat dari teks asli
                        if (preg_match('/^[A-Ea-e][.)]/', $listText)) {
                            // Sudah ada format A. di teks, pertahankan
                            $prefix = '';
                        } else {
                            // Default ke bullet
                            $prefix = '• ';
                        }
                    }

                    $text .= $prefix . $listText . "\n";
                }
            }
        }

        return $text;
    }
    private function parseQuestionsFromText(string $text): array
    {
        $questions = [];
        $currentCategory = '-';
        $questionRegex = '/(?:### Kategori:\s*(.+?)\n)?### Soal (\d+):\s*\n([\s\S]*?)\*Kunci:\s*([A-E,\s]+)/i';
        preg_match_all($questionRegex, $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {

            if (!empty($match[1])) {
                $currentCategory = trim($match[1]);
            }

            $questionNumber = $match[2];
            $content = trim($match[3]);
            $keyStr = strtoupper(trim($match[4]));

            preg_match_all('/[A-E]/', $keyStr, $keyMatches);
            $correctLetters = $keyMatches[0] ?? [];

            if (empty($correctLetters)) {
                continue;
            }

            $lines = array_values(array_filter(
                array_map('trim', explode("\n", $content)),
                fn($line) => $line !== ''
            ));

            $options = [];
            $letters = ['A', 'B', 'C', 'D', 'E'];

            /*
        |--------------------------------------------------------------------------
        | STEP 1 — Coba baca format manual (A. B. C.)
        |--------------------------------------------------------------------------
        */
            foreach ($lines as $line) {
                if (preg_match('/^\s*([A-E])[\.\)\:\-]\s*(.+)$/', $line, $m)) {
                    $options[strtoupper($m[1])] = trim($m[2]);
                }
            }

            /*
        |--------------------------------------------------------------------------
        | STEP 2 — Jika tidak ada opsi manual → pakai auto detect
        |--------------------------------------------------------------------------
        */
            if (empty($options)) {

                // Ambil 5 baris terakhir sebagai opsi
                $lastFive = array_slice($lines, -5);

                if (count($lastFive) >= 4) {

                    foreach ($lastFive as $index => $opt) {
                        if (isset($letters[$index])) {
                            $options[$letters[$index]] = $opt;
                        }
                    }

                    // Hapus opsi dari lines agar tersisa body + question
                    $lines = array_slice($lines, 0, count($lines) - count($lastFive));
                }
            }

            /*
        |--------------------------------------------------------------------------
        | STEP 3 — Pisahkan body dan question
        |--------------------------------------------------------------------------
        */
            $bodyText = '';
            $questionText = '';

            foreach ($lines as $line) {

                if (
                    empty($questionText) &&
                    (str_contains($line, '?') ||
                        preg_match('/\b(siapa|apa|dimana|kapan|mengapa|bagaimana|berapa)\b/i', $line))
                ) {
                    $questionText = $line;
                } else {
                    $bodyText .= ($bodyText ? "\n" : '') . $line;
                }
            }

            if (empty($questionText) && !empty($lines)) {
                $questionText = end($lines);
                array_pop($lines);
                $bodyText = implode("\n", $lines);
            }

            if (empty($options) || empty($correctLetters)) {
                continue;
            }

            $questions[] = [
                'category' => $currentCategory,
                'number' => $questionNumber,
                'body' => trim($bodyText),
                'question' => trim($questionText),
                'options' => $options,
                'correct_letters' => $correctLetters,
            ];
        }

        return $questions;
    }

    private function saveQuestionsToDatabase(array $questions, $exam): void
    {
        foreach ($questions as $questionData) {
            $examCategory = ExamQuestionCategory::firstOrCreate([
                'exam_id' => $exam->id,
                'name' => $questionData['category']
            ]);

            $examQuestion = ExamQuestion::create([
                'exam_id' => $exam->id,
                'category_id' => $examCategory->id,
                'badan_soal' => $questionData['body'],
                'kalimat_tanya' => $questionData['question'],
                'kode_soal' => $exam->exam_code . '-' . str_pad(
                    $questionData['number'],
                    3,
                    '0',
                    STR_PAD_LEFT
                ),
            ]);

            // Simpan opsi jawaban
            foreach ($questionData['options'] as $letter => $optionText) {
                ExamQuestionAnswer::create([
                    'exam_question_id' => $examQuestion->id,
                    'option' => $letter,
                    'text' => trim($optionText),
                    'is_correct' => in_array($letter, $questionData['correct_letters']) ? 1 : 0,
                ]);
            }
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($examCode, $questionId)
    {
        try {
            $exam = Exam::where('exam_code', $examCode)->firstOrFail();
            $question = ExamQuestion::where('exam_id', $exam->id)->findOrFail($questionId);

            if ($question->image) {
                \Storage::disk('public')->delete($question->image);
            }

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
