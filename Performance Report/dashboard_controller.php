<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\PerformanceReview;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getDashboardStats();
        $topPerformers = $this->getTopPerformers();
        $performanceTrends = $this->getPerformanceTrends();
        $recommendations = $this->getSmartRecommendations();
        $departments = Department::active()->get();

        return view('dashboard', compact(
            'stats',
            'topPerformers', 
            'performanceTrends',
            'recommendations',
            'departments'
        ));
    }

    public function getStats()
    {
        $stats = $this->getDashboardStats();
        return response()->json($stats);
    }

    public function refreshData()
    {
        $stats = $this->getDashboardStats();
        $topPerformers = $this->getTopPerformers();
        
        return response()->json([
            'success' => true,
            'message' => 'Data refreshed successfully',
            'stats' => $stats,
            'top_performers' => $topPerformers,
            'timestamp' => now()->toISOString()
        ]);
    }

    private function getDashboardStats()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // Total employees
        $totalEmployees = Employee::active()->count();
        $lastMonthEmployees = Employee::active()
            ->where('created_at', '<', $currentMonth)
            ->count();
        $employeeGrowth = $lastMonthEmployees > 0 
            ? (($totalEmployees - $lastMonthEmployees) / $lastMonthEmployees) * 100 
            : 0;

        // Average performance
        $avgPerformance = PerformanceReview::approved()
            ->where('review_date', '>=', $currentMonth)
            ->avg('overall_score') ?? 0;
        $lastMonthAvgPerformance = PerformanceReview::approved()
            ->whereBetween('review_date', [$lastMonth, $currentMonth])
            ->avg('overall_score') ?? 0;
        $performanceChange = $lastMonthAvgPerformance > 0
            ? (($avgPerformance - $lastMonthAvgPerformance) / $lastMonthAvgPerformance) * 100
            : 0;

        // Reports completed this month
        $reportsCompleted = PerformanceReview::where('created_at', '>=', $currentMonth)
            ->count();
        $lastMonthReports = PerformanceReview::whereBetween('created_at', [$lastMonth, $currentMonth])
            ->count();
        $reportsChange = $lastMonthReports > 0
            ? (($reportsCompleted - $lastMonthReports) / $lastMonthReports) * 100
            : 0;

        // Top performer
        $topPerformer = Employee::select('employees.*')
            ->join('performance_reviews', 'employees.id', '=', 'performance_reviews.employee_id')
            ->where('performance_reviews.status', 'approved')
            ->where('performance_reviews.review_date', '>=', $currentMonth->copy()->subMonths(3))
            ->groupBy('employees.id')
            ->orderByRaw('AVG(performance_reviews.overall_score) DESC')
            ->first();

        return [
            'total_employees' => $totalEmployees,
            'employee_growth' => round($employeeGrowth, 1),
            'avg_performance' => round($avgPerformance, 1),
            'performance_change' => round($performanceChange, 1),
            'reports_completed' => $reportsCompleted,
            'reports_change' => round($reportsChange, 1),
            'top_performer' => $topPerformer ? $topPerformer->first_name . ' ' . substr($topPerformer->last_name, 0, 1) . '.' : 'N/A'
        ];
    }

    private function getTopPerformers($limit = 5)
    {
        return Employee::select('employees.*')
            ->join('performance_reviews', 'employees.id', '=', 'performance_reviews.employee_id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->where('performance_reviews.status', 'approved')
            ->where('performance_reviews.review_date', '>=', now()->subMonths(6))
            ->groupBy('employees.id')
            ->orderByRaw('AVG(performance_reviews.overall_score) DESC')
            ->limit($limit)
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                    'department' => $employee->department->name,
                    'score' => round($employee->getAverageScore(6), 1),
                    'avatar' => $employee->avatar
                ];
            });
    }

    private function getPerformanceTrends($months = 12)
    {
        $trends = PerformanceReview::approved()
            ->where('review_date', '>=', now()->subMonths($months))
            ->selectRaw('DATE_FORMAT(review_date, "%Y-%m") as month, AVG(overall_score) as avg_score')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('avg_score', 'month')
            ->map(function ($score) {
                return round($score, 1);
            });

        $labels = [];
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthLabel = Carbon::now()->subMonths($i)->format('M Y');
            
            $labels[] = $monthLabel;
            $data[] = $trends[$month] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getSmartRecommendations()
    {
        $recommendations = [];

        // Performance improvement recommendations
        $lowPerformers = Employee::select('employees.*')
            ->join('performance_reviews', 'employees.id', '=', 'performance_reviews.employee_id')
            ->where('performance_reviews.status', 'approved')
            ->where('performance_reviews.review_date', '>=', now()->subMonths(3))
            ->groupBy('employees.id')
            ->havingRaw('AVG(performance_reviews.overall_score) < 75')
            ->get();

        if ($lowPerformers->count() > 0) {
            $recommendations[] = [
                'type' => 'training',
                'title' => 'Skills Development Needed',
                'description' => $lowPerformers->count() . ' employees would benefit from additional training and mentoring programs.',
                'priority' => 'high',
                'count' => $lowPerformers->count()
            ];
        }

        // Promotion candidates
        $highPerformers = Employee::select('employees.*')
            ->join('performance_reviews', 'employees.id', '=', 'performance_reviews.employee_id')
            ->where('performance_reviews.status', 'approved')
            ->where('performance_reviews.review_date', '>=', now()->subMonths(6))
            ->groupBy('employees.id')
            ->havingRaw('AVG(performance_reviews.overall_score) >= 90')
            ->get();

        if ($highPerformers->count() > 0) {
            $recommendations[] = [
                'type' => 'promotion',
                'title' => 'Promotion Candidates',
                'description' => $highPerformers->count() . ' employees are performing exceptionally and may be ready for advancement.',
                'priority' => 'medium',
                'count' => $highPerformers->count()
            ];
        }

        // Recognition opportunities
        $recentHighScores = PerformanceReview::approved()
            ->where('review_date', '>=', now()->subMonth())
            ->where('overall_score', '>=', 85)
            ->count();

        if ($recentHighScores > 0) {
            $recommendations[] = [
                'type' => 'recognition',
                'title' => 'Recognition Programs',
                'description' => 'Consider implementing team celebrations and recognition programs to maintain high performance momentum.',
                'priority' => 'low',
                'count' => $recentHighScores
            ];
        }

        // Review completion tracking
        $pendingReviews = PerformanceReview::where('status', 'draft')
            ->where('created_at', '<=', now()->subDays(7))
            ->count();

        if ($pendingReviews > 0) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Pending Reviews',
                'description' => $pendingReviews . ' performance reviews have been pending for over a week and need attention.',
                'priority' => 'high',
                'count' => $pendingReviews
            ];
        }

        return $recommendations;
    }

    public function getDepartmentStats(Request $request)
    {
        $departmentId = $request->input('department_id');
        $period = $request->input('period', 12); // months

        $query = PerformanceReview::approved()
            ->where('review_date', '>=', now()->subMonths($period));

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $reviews = $query->with(['employee', 'employee.department'])->get();

        $departmentStats = $reviews->groupBy('employee.department.name')
            ->map(function ($deptReviews, $deptName) {
                return [
                    'department' => $deptName,
                    'average_score' => round($deptReviews->avg('overall_score'), 1),
                    'review_count' => $deptReviews->count(),
                    'employee_count' => $deptReviews->unique('employee_id')->count()
                ];
            })
            ->values();

        return response()->json([
            'department_stats' => $departmentStats,
            'period' => $period
        ]);
    }
}