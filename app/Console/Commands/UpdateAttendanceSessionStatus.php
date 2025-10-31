<?php

namespace App\Console\Commands;

use App\Models\AttendanceSessions;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateAttendanceSessionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-attendance-session-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status of attendance sessions that have ended';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $updated = AttendanceSessions::where('status', 'active')
            ->where('end_time', '<', Carbon::now())
            ->update(['status' => 'finished']);

        $this->info("Updated {$updated} attendance sessions to finished status.");
        
        return 0;
    }
}
