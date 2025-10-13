<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Essentials\Entities\EssentialsPerformanceReview;
use App\Models\Performance\PerformanceGoal;

class PerformanceController extends Controller
{
    /**
     * Display the performance index
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user = $request->user();
        
        // Get performance data
        $performanceData = [
            'personal_performance' => $this->getPersonalPerformance($user),
            'department_average' => $this->getDepartmentAverage($user),
            'recent_reviews' => $this->getRecentReviews($user),
            'upcoming_deadlines' => $this->getUpcomingDeadlines($user),
        ];
        
        return view('performance.index', compact('performanceData'));
    }
    
    /**
     * Get personal performance data
     */
    private function getPersonalPerformance($user)
    {
        $latestReview = EssentialsPerformanceReview::where('user_id', $user->id)
            ->whereNotNull('overall_score')
            ->orderBy('review_date', 'desc')
            ->first();
            
        $previousReview = EssentialsPerformanceReview::where('user_id', $user->id)
            ->whereNotNull('overall_score')
            ->where('id', '!=', $latestReview->id ?? 0)
            ->orderBy('review_date', 'desc')
            ->first();
            
        $currentScore = $latestReview ? $latestReview->overall_score : 0;
        $previousScore = $previousReview ? $previousReview->overall_score : 0;
        $improvement = $currentScore - $previousScore;
        
        // Get goals data
                $totalGoals = PerformanceGoal::where('employee_id', $user->id)->count();
                $completedGoals = PerformanceGoal::where('employee_id', $user->id)
            ->where('status', 'achieved')->count();
        
        return [
            'current_score' => $currentScore,
            'previous_score' => $previousScore,
            'improvement' => $improvement,
            'goals_completed' => $completedGoals,
            'total_goals' => $totalGoals,
            'completion_rate' => $totalGoals > 0 ? round(($completedGoals / $totalGoals) * 100, 1) : 0,
        ];
    }
    
    /**
     * Get department average
     */
    private function getDepartmentAverage($user)
    {
        // Sample data - replace with actual calculation
        return [
            'average_score' => 83.8,
            'ranking' => 2, // User's rank in department
            'total_members' => 15,
        ];
    }
    
    /**
     * Get recent performance reviews
     */
    private function getRecentReviews($user)
    {
        // Sample data - replace with actual database queries
        return [
            [
                'title' => 'Q4 2023 Performance Review',
                'score' => 87.5,
                'date' => '2024-01-15',
                'status' => 'completed',
                'reviewer' => 'John Manager'
            ],
            [
                'title' => 'Mid-Year Review 2023',
                'score' => 84.2,
                'date' => '2023-06-30',
                'status' => 'completed',
                'reviewer' => 'John Manager'
            ],
        ];
    }
    
    /**
     * Get upcoming deadlines
     */
    private function getUpcomingDeadlines($user)
    {
        // Sample data - replace with actual database queries
        return [
            [
                'title' => 'Q1 2024 Self Assessment',
                'due_date' => '2024-03-31',
                'days_remaining' => 15,
                'priority' => 'high'
            ],
            [
                'title' => 'Goal Setting for Q2',
                'due_date' => '2024-04-15',
                'days_remaining' => 30,
                'priority' => 'medium'
            ],
        ];
    }
}
