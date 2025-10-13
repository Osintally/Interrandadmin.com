<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Essentials\Entities\EssentialsPerformanceReview;
use Modules\Essentials\Entities\EssentialsPerformanceTemplate;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the performance dashboard
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user = $request->user();
        
        // Get dashboard statistics
        $stats = [
            'total_employees' => \App\User::where('business_id', $business_id)->count(),
            'active_templates' => $this->getActiveTemplatesCount($business_id),
            'pending_reports' => $this->getPendingReportsCount($user),
            'completion_rate' => $this->calculateCompletionRate($business_id),
        ];
        
        // Get recent performance data for trends
        $trendsData = $this->getPerformanceTrends($business_id);
        
        // Get top performers
        $topPerformers = $this->getTopPerformers($business_id);
        
        // Get recent alerts or activities
        $recentActivity = $this->getRecentActivity($business_id, $user);
        
        return view('performance.dashboard', compact('stats', 'trendsData', 'topPerformers', 'recentActivity'));
    }

    /**
     * Get active templates count
     */
    private function getActiveTemplatesCount($business_id)
    {
        return EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->where('is_active', 1)
            ->count();
    }
    
    /**
     * Get pending reports count for user
     */
    private function getPendingReportsCount($user)
    {
        return EssentialsPerformanceReview::where('user_id', $user->id)
            ->whereIn('status', ['draft', 'pending'])
            ->count();
    }
    
    /**
     * Calculate completion rate
     */
    private function calculateCompletionRate($business_id)
    {
        $total = EssentialsPerformanceReview::where('business_id', $business_id)->count();
        
        if ($total > 0) {
            $completed = EssentialsPerformanceReview::where('business_id', $business_id)
                ->whereIn('status', ['completed', 'approved'])
                ->count();
            
            return round(($completed / $total) * 100, 1);
        }
        
        return 0.0;
    }
    
    /**
     * Get performance trends for charts
     */
    private function getPerformanceTrends($business_id)
    {
        $months = [];
        $scores = [];
        
        // Get last 6 months data
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M');
            
            $monthlyScore = EssentialsPerformanceReview::where('business_id', $business_id)
                ->whereYear('review_date', $month->year)
                ->whereMonth('review_date', $month->month)
                ->whereNotNull('overall_score')
                ->avg('overall_score');
                
            $scores[] = $monthlyScore ? round($monthlyScore, 1) : 0;
        }
        
        return [
            'labels' => $months,
            'monthly_scores' => $scores
        ];
    }
    
    /**
     * Get top performers
     */
    private function getTopPerformers($business_id)
    {
        return EssentialsPerformanceReview::with('user')
            ->where('business_id', $business_id)
            ->whereIn('status', ['completed', 'approved'])
            ->whereNotNull('overall_score')
            ->orderBy('overall_score', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return [
                    'name' => $review->user->first_name . ' ' . $review->user->last_name,
                    'score' => $review->overall_score,
                    'department' => $review->user->department ?? 'Unknown',
                    'review_date' => $review->review_date->format('M Y')
                ];
            });
    }
    
    /**
     * Get recent activity
     */
    private function getRecentActivity($business_id, $user)
    {
        return EssentialsPerformanceReview::with(['user', 'reviewer'])
            ->where('business_id', $business_id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('reviewer_id', $user->id);
            })
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return [
                    'type' => $review->status,
                    'description' => "Performance review for {$review->user->first_name} {$review->user->last_name}",
                    'date' => $review->updated_at->diffForHumans(),
                    'status' => $review->status,
                    'score' => $review->overall_score
                ];
            });
    }
    
    // API Methods for AJAX calls
    
    /**
     * Get dashboard summary data
     */
    public function getSummary(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user = $request->user();
        
        $stats = [
            'total_employees' => \App\User::where('business_id', $business_id)->count(),
            'active_templates' => $this->getActiveTemplatesCount($business_id),
            'pending_reports' => $this->getPendingReportsCount($user),
            'completion_rate' => $this->calculateCompletionRate($business_id),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Get performance trends data
     */
    public function getTrends(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $trendsData = $this->getPerformanceTrends($business_id);
        
        return response()->json([
            'success' => true,
            'data' => $trendsData
        ]);
    }
    
    /**
     * Get top performers data (API endpoint)
     */
    public function getTopPerformersAPI(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $topPerformers = $this->getTopPerformers($business_id);
        
        return response()->json([
            'success' => true,
            'data' => $topPerformers
        ]);
    }
    
    /**
     * Get KPI breakdown data
     */
    public function getKPIBreakdown(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Get KPI performance breakdown
        $kpiBreakdown = \App\Models\Performance\PerformanceKPI::with(['kpiScores' => function($query) use ($business_id) {
                $query->whereHas('report', function($q) use ($business_id) {
                    $q->where('business_id', $business_id);
                });
            }])
            ->get()
            ->map(function ($kpi) {
                $avgScore = $kpi->kpiScores->avg('score') ?? 0;
                return [
                    'name' => $kpi->name,
                    'average_score' => round($avgScore, 1),
                    'target' => $kpi->target_value,
                    'performance' => $avgScore >= $kpi->target_value ? 'good' : 'needs_improvement'
                ];
            });
            
        return response()->json([
            'success' => true,
            'data' => $kpiBreakdown
        ]);
    }
    
    /**
     * Get alerts data
     */
    public function getAlerts(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user = $request->user();
        
        $alerts = [];
        
        // Check for overdue reviews
        $overdueReviews = EssentialsPerformanceReview::where('business_id', $business_id)
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->where('review_date', '<', now())
            ->count();
            
        if ($overdueReviews > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Overdue Reviews',
                'message' => "You have {$overdueReviews} overdue performance review(s)",
                'action_url' => route('performance.reports.index')
            ];
        }
        
        // Check for upcoming deadlines
        $upcomingDeadlines = EssentialsPerformanceReview::where('business_id', $business_id)
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->whereBetween('review_date', [now(), now()->addDays(7)])
            ->count();
            
        if ($upcomingDeadlines > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Upcoming Deadlines',
                'message' => "You have {$upcomingDeadlines} review(s) due in the next 7 days",
                'action_url' => route('performance.reports.index')
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $alerts
        ]);
    }
}