<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\CallLog;
use App\Models\Meeting;
use App\Models\Deal;
use App\Models\User;
use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        }

        return $this->salesAgentDashboard($user);
    }

    private function superAdminDashboard()
    {
        $totalLeads = Lead::count();
        $newLeadsCount = Lead::where('status', 'new')->count();
        $interestedCount = Lead::whereIn('status', ['interested', 'callback'])->count();
        $meetingsScheduledCount = Lead::where('status', 'meeting_scheduled')->count();
        $dealsWonCount = Deal::count();
        $totalRevenueWon = Deal::sum('deal_amount');

        // Upcoming meetings across company
        $upcomingMeetings = Meeting::with(['lead', 'user'])
            ->where('scheduled_at', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at', 'asc')
            ->take(5)
            ->get();

        // Today's meetings alert
        $todayMeetings = Meeting::with(['lead', 'user'])
            ->whereDate('scheduled_at', Carbon::today())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        // Recent live team calls
        $recentCallLogs = CallLog::with(['lead', 'user'])
            ->latest()
            ->take(8)
            ->get();

        // Recent won deals with budgets
        $recentDeals = Deal::with(['lead', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // Team performance overview
        $agents = User::assignableAgents()
            ->withCount([
                'leads',
                'callLogs as calls_today' => function ($query) {
                    $query->whereDate('created_at', Carbon::today());
                },
                'meetings as meetings_today' => function ($query) {
                    $query->whereDate('scheduled_at', Carbon::today());
                },
                'deals as deals_won_count',
            ])
            ->withSum('deals as total_revenue', 'deal_amount')
            ->get();

        return view('dashboard.admin', compact(
            'totalLeads',
            'newLeadsCount',
            'interestedCount',
            'meetingsScheduledCount',
            'dealsWonCount',
            'totalRevenueWon',
            'upcomingMeetings',
            'todayMeetings',
            'recentCallLogs',
            'recentDeals',
            'agents'
        ));
    }

    private function salesAgentDashboard(User $user)
    {
        $myLeadsCount = Lead::where('assigned_to', $user->id)->count();
        $myNewLeads = Lead::where('assigned_to', $user->id)->where('status', 'new')->count();
        $myCallsToday = CallLog::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        $myMeetingsScheduled = Meeting::where('user_id', $user->id)
            ->where('scheduled_at', '>=', now())
            ->where('status', 'scheduled')
            ->count();

        $myDealsWon = Deal::where('user_id', $user->id)->count();
        $myRevenueWon = Deal::where('user_id', $user->id)->sum('deal_amount');

        // Today's meetings alert specifically for this agent
        $todayMeetings = Meeting::with('lead')
            ->where('user_id', $user->id)
            ->whereDate('scheduled_at', Carbon::today())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        // Upcoming meetings for this agent
        $upcomingMeetings = Meeting::with('lead')
            ->where('user_id', $user->id)
            ->where('scheduled_at', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at', 'asc')
            ->take(5)
            ->get();

        // Leads needing attention (New, Callback, or Interested)
        $pendingLeads = Lead::where('assigned_to', $user->id)
            ->whereIn('status', ['new', 'callback', 'interested'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        // Agent's recent call history
        $recentCallLogs = CallLog::where('user_id', $user->id)
            ->with('lead')
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.agent', compact(
            'myLeadsCount',
            'myNewLeads',
            'myCallsToday',
            'myMeetingsScheduled',
            'myDealsWon',
            'myRevenueWon',
            'todayMeetings',
            'upcomingMeetings',
            'pendingLeads',
            'recentCallLogs'
        ));
    }
}
