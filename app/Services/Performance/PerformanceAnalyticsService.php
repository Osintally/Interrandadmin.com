<?php

namespace App\Services\Performance;

use App\Models\Performance\PerformanceKPI;
use App\Models\Performance\PerformanceAggregation;
use App\Models\Performance\PerformanceGoal;
use App\Models\Performance\PerformanceAlert;
use Modules\Essentials\Entities\EssentialsPerformanceReview;
use App\User;
use App\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PerformanceAnalyticsService
{
    /**
     * Get organization-wide performance summary
     */
    public function getOrganizationSummary(): array
    {
        $totalEmployees = User::where('user_type', 'user')->count();
        $submittedReports = EssentialsPerformanceReview::where('status', '!=', 'draft')->count();
        $organizationKPI = $this->getOrganizationAverageKPI();
        $goalAchievement = $this->getGoalAchievementRate();

        return [
            'total_employees' => $totalEmployees,
            'submitted_reports' => $submittedReports,
            'organization_kpi' => $organizationKPI,
            'goal_achievement' => $goalAchievement,
            'trend_direction' => $this->getPerformanceTrendDirection(),
        ];
    }

    /**
     * Get department KPI data
     */
    public function getDepartmentKPIs(): array
    {
        $departments = Category::where('category_type', 'department')->get();
        $departmentKPIs = [];

        foreach ($departments as $department) {
            $performance = $this->getDepartmentPerformanceSummary($department->id);
            $departmentKPIs[] = [
                'department' => $department->name,
                'department_id' => $department->id,
                'avg_kpi' => $performance['average_score'],
                'employee_count' => $performance['employee_count'],
                'goal_achievement' => $performance['goal_achievement'],
                'trend' => $performance['trend'],
                'top_kpis' => $performance['top_kpis'],
                'needs_improvement' => $performance['needs_improvement'],
            ];
        }

        return $departmentKPIs;
    }

    /**
     * Get performance trends over time
     */
    public function getPerformanceTrends($periods = 6): array
    {
        $trends = [];
        $months = [];

        for ($i = $periods - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();

            $reviews = EssentialsPerformanceReview::where('status', 'approved')
                ->whereBetween('review_date', [$startDate, $endDate])
                ->get();

            $averageScore = $reviews->avg('overall_score') ?? 0;
            $totalReviews = $reviews->count();

            $trends[] = [
                'month' => $date->format('M Y'),
                'date' => $date->format('Y-m'),
                'average_score' => round($averageScore, 2),
                'total_reviews' => $totalReviews,
            ];

            $months[] = $date->format('M Y');
        }

        return [
            'trends' => $trends,
            'months' => $months,
            'overall_trend' => $this->calculateTrendDirection($trends),
        ];
    }

    /**
     * Get top performers
     */
    public function getTopPerformers($limit = 10): array
    {
        $topPerformers = EssentialsPerformanceReview::where('status', 'approved')
            ->with(['user', 'template'])
            ->orderBy('overall_score', 'desc')
            ->limit($limit)
            ->get();

        return $topPerformers->map(function ($review) {
            return [
                'employee_id' => $review->user_id,
                'employee_name' => $review->user->first_name . ' ' . $review->user->last_name,
                'department' => $review->user->department->name ?? 'N/A',
                'score' => $review->overall_score,
                'period' => $review->review_period,
                'review_date' => $review->review_date,
                'template_name' => $review->template->name,
            ];
        })->toArray();
    }

    /**
     * Get performance distribution
     */
    public function getPerformanceDistribution(): array
    {
        $reviews = EssentialsPerformanceReview::where('status', 'approved')->get();
        $scores = $reviews->pluck('overall_score');

        return [
            'excellent' => $scores->where('overall_score', '>=', 9.0)->count(),
            'good' => $scores->whereBetween('overall_score', [7.5, 8.9])->count(),
            'satisfactory' => $scores->whereBetween('overall_score', [6.0, 7.4])->count(),
            'needs_improvement' => $scores->where('overall_score', '<', 6.0)->count(),
            'total' => $scores->count(),
        ];
    }

    /**
     * Get KPI breakdown
     */
    public function getKPIBreakdown(): array
    {
        $kpiScores = DB::table('kpi_scores')
            ->join('performance_kpis', 'kpi_scores.kpi_id', '=', 'performance_kpis.id')
            ->select(
                'performance_kpis.name as kpi_name',
                'performance_kpis.id as kpi_id',
                DB::raw('AVG(kpi_scores.calculated_score) as average_score'),
                DB::raw('COUNT(*) as total_scores'),
                DB::raw('MAX(kpi_scores.calculated_score) as max_score'),
                DB::raw('MIN(kpi_scores.calculated_score) as min_score')
            )
            ->groupBy('performance_kpis.id', 'performance_kpis.name')
            ->orderBy('average_score', 'desc')
            ->get();

        return $kpiScores->map(function ($kpi) {
            return [
                'kpi_id' => $kpi->kpi_id,
                'kpi_name' => $kpi->kpi_name,
                'average_score' => round($kpi->average_score, 2),
                'total_scores' => $kpi->total_scores,
                'max_score' => $kpi->max_score,
                'min_score' => $kpi->min_score,
                'score_range' => $kpi->max_score - $kpi->min_score,
            ];
        })->toArray();
    }

    /**
     * Get department summary
     */
    public function getDepartmentSummary($departmentId): array
    {
        $department = Category::find($departmentId);
        if (!$department) {
            return [];
        }

        $employees = User::where('essentials_department_id', $departmentId)->get();
        $reviews = EssentialsPerformanceReview::whereHas('user', function ($query) use ($departmentId) {
            $query->where('essentials_department_id', $departmentId);
        })->where('status', 'approved')->get();

        $averageScore = $reviews->avg('overall_score') ?? 0;
        $goalAchievement = $this->getDepartmentGoalAchievement($departmentId);

        return [
            'department_name' => $department->name,
            'employee_count' => $employees->count(),
            'average_score' => round($averageScore, 2),
            'total_reviews' => $reviews->count(),
            'goal_achievement' => $goalAchievement,
            'performance_trend' => $this->getDepartmentTrend($departmentId),
        ];
    }

    /**
     * Get team performance
     */
    public function getTeamPerformance($departmentId): array
    {
        $employees = User::where('essentials_department_id', $departmentId)->get();
        $teamPerformance = [];

        foreach ($employees as $employee) {
            $latestReview = EssentialsPerformanceReview::where('user_id', $employee->id)
                ->where('status', 'approved')
                ->orderBy('review_date', 'desc')
                ->first();

            $teamPerformance[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'latest_score' => $latestReview ? $latestReview->overall_score : null,
                'latest_period' => $latestReview ? $latestReview->review_period : null,
                'review_date' => $latestReview ? $latestReview->review_date : null,
                'status' => $latestReview ? $this->getPerformanceStatus($latestReview->overall_score) : 'no_review',
            ];
        }

        return collect($teamPerformance)->sortByDesc('latest_score')->values()->toArray();
    }

    /**
     * Get department trends
     */
    public function getDepartmentTrends($departmentId, $periods = 6): array
    {
        $trends = [];

        for ($i = $periods - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();

            $reviews = EssentialsPerformanceReview::whereHas('user', function ($query) use ($departmentId) {
                $query->where('essentials_department_id', $departmentId);
            })
            ->where('status', 'approved')
            ->whereBetween('review_date', [$startDate, $endDate])
            ->get();

            $averageScore = $reviews->avg('overall_score') ?? 0;

            $trends[] = [
                'month' => $date->format('M Y'),
                'date' => $date->format('Y-m'),
                'average_score' => round($averageScore, 2),
                'total_reviews' => $reviews->count(),
            ];
        }

        return $trends;
    }

    /**
     * Get pending reviews
     */
    public function getPendingReviews($departmentId): array
    {
        $pendingReviews = EssentialsPerformanceReview::whereHas('user', function ($query) use ($departmentId) {
            $query->where('essentials_department_id', $departmentId);
        })
        ->whereIn('status', ['draft', 'submitted'])
        ->with(['user', 'template'])
        ->orderBy('created_at', 'desc')
        ->get();

        return $pendingReviews->map(function ($review) {
            return [
                'review_id' => $review->id,
                'employee_name' => $review->user->first_name . ' ' . $review->user->last_name,
                'template_name' => $review->template->name,
                'status' => $review->status,
                'created_at' => $review->created_at,
                'submitted_at' => $review->submitted_at,
            ];
        })->toArray();
    }

    /**
     * Get department alerts
     */
    public function getDepartmentAlerts($departmentId): array
    {
        $alerts = PerformanceAlert::whereHas('employee', function ($query) use ($departmentId) {
            $query->where('essentials_department_id', $departmentId);
        })
        ->unread()
        ->with(['employee', 'kpi'])
        ->orderBy('triggered_at', 'desc')
        ->limit(10)
        ->get();

        return $alerts->map(function ($alert) {
            return [
                'alert_id' => $alert->id,
                'type' => $alert->type,
                'type_label' => $alert->getTypeLabel(),
                'type_color' => $alert->getTypeColor(),
                'employee_name' => $alert->employee->first_name . ' ' . $alert->employee->last_name,
                'kpi_name' => $alert->kpi ? $alert->kpi->name : null,
                'message' => $alert->message,
                'triggered_at' => $alert->triggered_at,
                'priority' => $alert->getPriority(),
            ];
        })->toArray();
    }

    /**
     * Get personal KPIs for employee
     */
    public function getPersonalKPIs($employeeId): array
    {
        $latestReview = EssentialsPerformanceReview::where('user_id', $employeeId)
            ->where('status', 'approved')
            ->orderBy('review_date', 'desc')
            ->first();

        if (!$latestReview || !$latestReview->calculated_kpis) {
            return [];
        }

        return $latestReview->calculated_kpis;
    }

    /**
     * Get performance history for employee
     */
    public function getPerformanceHistory($employeeId, $limit = 5): array
    {
        $reviews = EssentialsPerformanceReview::where('user_id', $employeeId)
            ->where('status', 'approved')
            ->orderBy('review_date', 'desc')
            ->limit($limit)
            ->get();

        return $reviews->map(function ($review) {
            return [
                'review_id' => $review->id,
                'period' => $review->review_period,
                'score' => $review->overall_score,
                'review_date' => $review->review_date,
                'template_name' => $review->template->name,
                'status' => $this->getPerformanceStatus($review->overall_score),
            ];
        })->toArray();
    }

    /**
     * Get current goals for employee
     */
    public function getCurrentGoals($employeeId): array
    {
        $goals = PerformanceGoal::forEmployee($employeeId)
            ->active()
            ->with('kpi')
            ->get();

        return $goals->map(function ($goal) {
            return [
                'goal_id' => $goal->id,
                'kpi_name' => $goal->kpi->name,
                'target_score' => $goal->target_score,
                'current_score' => $goal->current_score,
                'achievement_percentage' => $goal->achievement_percentage,
                'period_start' => $goal->period_start,
                'period_end' => $goal->period_end,
                'days_remaining' => $goal->getDaysRemaining(),
                'progress_percentage' => $goal->getProgressPercentage(),
                'status' => $goal->status,
                'status_color' => $goal->getStatusColor(),
                'achievement_color' => $goal->getAchievementColor(),
            ];
        })->toArray();
    }

    /**
     * Get peer comparison for employee
     */
    public function getPeerComparison($employeeId): array
    {
        $employee = User::find($employeeId);
        if (!$employee || !$employee->essentials_department_id) {
            return [];
        }

        $departmentId = $employee->essentials_department_id;
        $latestReview = EssentialsPerformanceReview::where('user_id', $employeeId)
            ->where('status', 'approved')
            ->orderBy('review_date', 'desc')
            ->first();

        if (!$latestReview) {
            return [];
        }

        $departmentReviews = EssentialsPerformanceReview::whereHas('user', function ($query) use ($departmentId) {
            $query->where('essentials_department_id', $departmentId);
        })
        ->where('status', 'approved')
        ->where('review_date', '>=', $latestReview->review_date->subMonths(3))
        ->get();

        $employeeScore = $latestReview->overall_score;
        $departmentAverage = $departmentReviews->avg('overall_score');
        $percentile = $this->calculatePercentile($employeeScore, $departmentReviews->pluck('overall_score'));

        return [
            'employee_score' => $employeeScore,
            'department_average' => round($departmentAverage, 2),
            'percentile' => $percentile,
            'ranking' => $this->getEmployeeRanking($employeeId, $departmentId),
            'total_peers' => $departmentReviews->count(),
        ];
    }

    /**
     * Get improvement areas for employee
     */
    public function getImprovementAreas($employeeId): array
    {
        $latestReview = EssentialsPerformanceReview::where('user_id', $employeeId)
            ->where('status', 'approved')
            ->orderBy('review_date', 'desc')
            ->first();

        if (!$latestReview || !$latestReview->calculated_kpis) {
            return [];
        }

        $kpis = collect($latestReview->calculated_kpis)
            ->where('calculated_score', '<', 7.0)
            ->sortBy('calculated_score')
            ->take(3)
            ->values();

        return $kpis->map(function ($kpi) {
            return [
                'kpi_name' => $kpi['kpi_name'],
                'score' => $kpi['calculated_score'],
                'threshold' => $kpi['threshold'],
                'threshold_color' => $kpi['threshold_color'],
                'improvement_needed' => 7.0 - $kpi['calculated_score'],
            ];
        })->toArray();
    }

    /**
     * Get pending reports for employee
     */
    public function getPendingReports($employeeId): array
    {
        $pendingReports = EssentialsPerformanceReview::where('user_id', $employeeId)
            ->whereIn('status', ['draft', 'submitted'])
            ->with('template')
            ->orderBy('created_at', 'desc')
            ->get();

        return $pendingReports->map(function ($report) {
            return [
                'report_id' => $report->id,
                'template_name' => $report->template->name,
                'status' => $report->status,
                'created_at' => $report->created_at,
                'submitted_at' => $report->submitted_at,
                'due_date' => $report->review_date,
            ];
        })->toArray();
    }

    // Helper methods

    private function getOrganizationAverageKPI(): float
    {
        $averageScore = EssentialsPerformanceReview::where('status', 'approved')
            ->avg('overall_score');
        
        return round($averageScore ?? 0, 2);
    }

    private function getGoalAchievementRate(): float
    {
        $totalGoals = PerformanceGoal::count();
        $achievedGoals = PerformanceGoal::achieved()->count();
        
        return $totalGoals > 0 ? round(($achievedGoals / $totalGoals) * 100, 2) : 0;
    }

    private function getPerformanceTrendDirection(): string
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $currentScore = EssentialsPerformanceReview::where('status', 'approved')
            ->where('review_date', '>=', $currentMonth)
            ->avg('overall_score') ?? 0;

        $lastScore = EssentialsPerformanceReview::where('status', 'approved')
            ->whereBetween('review_date', [$lastMonth, $currentMonth])
            ->avg('overall_score') ?? 0;

        if ($currentScore > $lastScore + 0.1) {
            return 'up';
        } elseif ($currentScore < $lastScore - 0.1) {
            return 'down';
        } else {
            return 'stable';
        }
    }

    private function getDepartmentPerformanceSummary($departmentId): array
    {
        $employees = User::where('essentials_department_id', $departmentId)->count();
        $reviews = EssentialsPerformanceReview::whereHas('user', function ($query) use ($departmentId) {
            $query->where('essentials_department_id', $departmentId);
        })->where('status', 'approved')->get();

        $averageScore = $reviews->avg('overall_score') ?? 0;
        $goalAchievement = $this->getDepartmentGoalAchievement($departmentId);

        return [
            'average_score' => round($averageScore, 2),
            'employee_count' => $employees,
            'goal_achievement' => $goalAchievement,
            'trend' => $this->getDepartmentTrend($departmentId),
            'top_kpis' => $this->getDepartmentTopKPIs($departmentId),
            'needs_improvement' => $this->getDepartmentImprovementAreas($departmentId),
        ];
    }

    private function getDepartmentGoalAchievement($departmentId): float
    {
        $totalGoals = PerformanceGoal::whereHas('employee', function ($query) use ($departmentId) {
            $query->where('essentials_department_id', $departmentId);
        })->count();

        $achievedGoals = PerformanceGoal::whereHas('employee', function ($query) use ($departmentId) {
            $query->where('essentials_department_id', $departmentId);
        })->achieved()->count();

        return $totalGoals > 0 ? round(($achievedGoals / $totalGoals) * 100, 2) : 0;
    }

    private function getDepartmentTrend($departmentId): string
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $currentScore = EssentialsPerformanceReview::whereHas('user', function ($query) use ($departmentId) {
            $query->where('essentials_department_id', $departmentId);
        })
        ->where('status', 'approved')
        ->where('review_date', '>=', $currentMonth)
        ->avg('overall_score') ?? 0;

        $lastScore = EssentialsPerformanceReview::whereHas('user', function ($query) use ($departmentId) {
            $query->where('essentials_department_id', $departmentId);
        })
        ->where('status', 'approved')
        ->whereBetween('review_date', [$lastMonth, $currentMonth])
        ->avg('overall_score') ?? 0;

        if ($currentScore > $lastScore + 0.1) {
            return 'up';
        } elseif ($currentScore < $lastScore - 0.1) {
            return 'down';
        } else {
            return 'stable';
        }
    }

    private function getDepartmentTopKPIs($departmentId): array
    {
        // Implementation for getting top KPIs in department
        return [];
    }

    private function getDepartmentImprovementAreas($departmentId): array
    {
        // Implementation for getting improvement areas in department
        return [];
    }

    private function calculateTrendDirection(array $trends): string
    {
        if (count($trends) < 2) {
            return 'stable';
        }

        $first = $trends[0]['average_score'];
        $last = $trends[count($trends) - 1]['average_score'];

        if ($last > $first + 0.1) {
            return 'up';
        } elseif ($last < $first - 0.1) {
            return 'down';
        } else {
            return 'stable';
        }
    }

    private function getPerformanceStatus(float $score): string
    {
        if ($score >= 9.0) {
            return 'excellent';
        } elseif ($score >= 7.5) {
            return 'good';
        } elseif ($score >= 6.0) {
            return 'satisfactory';
        } else {
            return 'needs_improvement';
        }
    }

    private function calculatePercentile(float $score, $scores): float
    {
        $scores = $scores->sort()->values();
        $count = $scores->count();
        
        if ($count === 0) {
            return 0;
        }

        $belowCount = $scores->where('overall_score', '<', $score)->count();
        return round(($belowCount / $count) * 100, 2);
    }

    private function getEmployeeRanking($employeeId, $departmentId): int
    {
        $employee = User::find($employeeId);
        if (!$employee) {
            return 0;
        }

        $latestReview = EssentialsPerformanceReview::where('user_id', $employeeId)
            ->where('status', 'approved')
            ->orderBy('review_date', 'desc')
            ->first();

        if (!$latestReview) {
            return 0;
        }

        $betterPerformers = EssentialsPerformanceReview::whereHas('user', function ($query) use ($departmentId) {
            $query->where('essentials_department_id', $departmentId);
        })
        ->where('status', 'approved')
        ->where('overall_score', '>', $latestReview->overall_score)
        ->count();

        return $betterPerformers + 1;
    }
}
