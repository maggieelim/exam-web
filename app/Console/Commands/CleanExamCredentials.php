<?php

namespace App\Console\Commands;

use App\Models\ExamCredential;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class CleanExamCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-exam-credentials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Hapus yang sudah dipakai > 7 hari
        ExamCredential::where('is_used', true)
            ->whereNotNull('used_at')
            ->where('used_at', '<=', Date::now()->subDays(7))
            ->delete();

        // 2. Hapus yang belum pernah dipakai > 30 hari
        ExamCredential::where('is_used', false)
            ->whereHas('exam', function ($query) {
                $query->where('status', 'ended');
            })
            ->where('created_at', '<=', Date::now()->subDays(30))
            ->delete();
    }
}
