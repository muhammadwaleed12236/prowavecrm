<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;
use App\Models\User;
use App\Models\CallLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $date = $request->get('date', Carbon::today()->toDateString());

        // Always ensure reports for this date are refreshed
        Artisan::call('reports:generate', ['--date' => $date]);

        $query = DailyReport::with('user')->where('report_date', $date);

        if (!$user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('agent_id')) {
            $query->where('user_id', $request->agent_id);
        }

        $reports = $query->get();

        // Totals for this date
        $summary = [
            'total_calls' => $reports->sum('total_calls'),
            'interested' => $reports->sum('interested_count'),
            'callbacks' => $reports->sum('callbacks_count'),
            'meetings' => $reports->sum('meetings_booked'),
            'deals_won' => $reports->sum('deals_won'),
        ];

        // Also fetch detailed call logs of this date for audit
        $callLogsQuery = CallLog::with(['lead', 'user'])->whereDate('created_at', $date);
        if (!$user->isSuperAdmin()) {
            $callLogsQuery->where('user_id', $user->id);
        } elseif ($request->filled('agent_id')) {
            $callLogsQuery->where('user_id', $request->agent_id);
        }
        $detailedLogs = $callLogsQuery->latest()->take(50)->get();

        $agents = $user->isSuperAdmin() ? User::assignableAgents()->get() : collect();

        return view('reports.index', compact('reports', 'summary', 'detailedLogs', 'agents', 'date'));
    }

    public function generateNow(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $date = $request->get('date', Carbon::today()->toDateString());
        Artisan::call('reports:generate', ['--date' => $date]);

        return back()->with('success', "Daily reports for {$date} re-generated successfully!");
    }
}
