<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CallLog;
use App\Models\Meeting;
use App\Models\Deal;
use App\Models\DailyReport;
use Carbon\Carbon;

class GenerateDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate {--date= : The date for the report (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily sales team activity reports automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateString = $this->option('date') ?: Carbon::today()->toDateString();
        $targetDate = Carbon::parse($dateString);

        $this->info("Generating daily reports for date: {$targetDate->toDateString()}...");

        $salesAgents = User::where('role', 'sales_agent')->get();

        foreach ($salesAgents as $agent) {
            $totalCalls = CallLog::where('user_id', $agent->id)
                ->whereDate('created_at', $targetDate)
                ->count();

            $interestedCount = CallLog::where('user_id', $agent->id)
                ->whereDate('created_at', $targetDate)
                ->where('call_status', 'interested')
                ->count();

            $callbacksCount = CallLog::where('user_id', $agent->id)
                ->whereDate('created_at', $targetDate)
                ->where('call_status', 'callback')
                ->count();

            $meetingsBooked = Meeting::where('user_id', $agent->id)
                ->whereDate('created_at', $targetDate)
                ->count();

            $dealsWon = Deal::where('user_id', $agent->id)
                ->whereDate('closed_date', $targetDate)
                ->count();

            DailyReport::updateOrCreate(
                [
                    'user_id' => $agent->id,
                    'report_date' => $targetDate->toDateString(),
                ],
                [
                    'total_calls' => $totalCalls,
                    'interested_count' => $interestedCount,
                    'callbacks_count' => $callbacksCount,
                    'meetings_booked' => $meetingsBooked,
                    'deals_won' => $dealsWon,
                    'notes' => "Auto-compiled on " . now()->toDateTimeString(),
                ]
            );

            $this->line("Agent [{$agent->name}]: {$totalCalls} calls, {$meetingsBooked} meetings, {$dealsWon} deals won.");
        }

        $this->info('Daily reports generated successfully!');
        return Command::SUCCESS;
    }
}
