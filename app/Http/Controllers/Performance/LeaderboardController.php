<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * Display the leaderboards
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Sample leaderboard data - replace with actual data
        $leaderboards = [
            'department' => $this->getDepartmentLeaderboard($business_id),
            'company' => $this->getCompanyLeaderboard($business_id),
            'personal' => $this->getPersonalRanking($request->user()),
        ];
        
        return view('performance.leaderboards.index', compact('leaderboards'));
    }
    
    /**
     * Get department leaderboard data
     */
    private function getDepartmentLeaderboard($business_id)
    {
        // Sample data - replace with actual database queries
        return [
            ['rank' => 1, 'name' => 'Sarah Johnson', 'department' => 'Sales', 'score' => 98.5],
            ['rank' => 2, 'name' => 'Michael Chen', 'department' => 'Engineering', 'score' => 96.2],
            ['rank' => 3, 'name' => 'Emily Rodriguez', 'department' => 'Marketing', 'score' => 94.8],
            ['rank' => 4, 'name' => 'David Kim', 'department' => 'Operations', 'score' => 92.1],
            ['rank' => 5, 'name' => 'Lisa Thompson', 'department' => 'HR', 'score' => 89.7],
        ];
    }
    
    /**
     * Get company leaderboard data
     */
    private function getCompanyLeaderboard($business_id)
    {
        // Sample data - replace with actual database queries
        return [
            ['rank' => 1, 'name' => 'Sales Department', 'average_score' => 92.3, 'members' => 25],
            ['rank' => 2, 'name' => 'Engineering Department', 'average_score' => 91.8, 'members' => 45],
            ['rank' => 3, 'name' => 'Marketing Department', 'average_score' => 89.5, 'members' => 15],
            ['rank' => 4, 'name' => 'Operations Department', 'average_score' => 87.2, 'members' => 20],
            ['rank' => 5, 'name' => 'HR Department', 'average_score' => 85.9, 'members' => 8],
        ];
    }
    
    /**
     * Get personal ranking for the user
     */
    private function getPersonalRanking($user)
    {
        // Sample data - replace with actual calculation
        return [
            'current_rank' => 15,
            'total_employees' => 150,
            'current_score' => 87.5,
            'previous_rank' => 18,
            'rank_change' => 3, // Positive means improvement
        ];
    }
}
