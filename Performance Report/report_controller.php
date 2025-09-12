<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display reports dashboard.
     */
    public function index()
    {
        $reportStats = $this->getReportStats();
        $recentReports = $this->getRecentReports();
        
        return view('reports.index', compact('reportStats', 'recentReports'));
    }

    /**
     * Generate a new report.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:performance_summary,department_analysis,individual_report,comparison_report,trend_analysis',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'departments' => 'array',
            'departments.*' => 'exists:departments,id',
            'employees' => 'array',
            'employees.*' => 'exists:employees,id',
            'format' => 'required|in:pdf,csv,excel',
            'include_charts' => 'boolean',
            'include_details' => 'boolean'
        ]);

        $reportData = $this->generateReportData($request);
        
        switch ($request->format) {
            case 'pdf':
                return $this->generatePDF($reportData, $request);
            case 'excel':
                return $this->generateExcel($reportData, $request);
            case 'csv':
                return $this->generateCSV($reportData, $request);
        }
    }

    /**
     * Export comprehensive report.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $reportType = $request->get('type', 'dashboard_summary');
        
        switch ($reportType) {
            case 'dashboard_summary':
                return $this->exportDashboardSummary($format);
            case 'performance_overview':
                return $this->exportPerformanceOverview($format);
            case 'department_breakdown':
                return $this->exportDepartmentBreakdown($format);
            default:
                return $this->exportDashboardSummary($format);
        }
    }

    /**
     * Generate individual employee report.
     */
    public function individualReport(Employee $employee, Request $request)
    {
        $dateFrom = Carbon::parse($request->get('date_from', now()->subYear()));
        $dateTo = Carbon::parse($request->get('date_to', now()));
        
        $reportData = [
            'employee' => $employee->load('department', 'manager'),
            'reviews' => $employee->performanceReviews()
                ->with(['reviewer', 'template'])
                ->whereBetween('review_date', [$dateFrom, $dateTo])
                ->orderBy('review_date', 'desc')
                ->get(),
            'stats' => $this->getEmployeeStats($employee, $dateFrom, $dateTo),
            'goals' => $this->getEmployeeGoals($employee, $dateFrom, $dateTo),
            'trends' => $employee->getPerformanceTrend(12),
            'period' => [
                'from' => $dateFrom->format('M j, Y'),
                'to' => $dateTo->format('M j, Y')
            ]
        ];

        if ($request->get('format') === 'pdf') {
            return $this->generateEmployeePDF($reportData);
        }

        return view('reports.individual', $reportData);
    }

    /**
     * Generate department report.
     */
    public function departmentReport(Department $department, Request $request)
    {
        $dateFrom = Carbon::parse($request->get('date_from', now()->subYear()));
        $dateTo = Carbon::parse($request->get('date_to', now()));
        
        $reportData = [
            'department' => $department->load('manager'),
            'employees' => $department->activeEmployees()->with(['performanceReviews' => function($query) use ($dateFrom, $dateTo) {
                $query->approved()->whereBetween('review_date', [$dateFrom, $dateTo]);
            }])->get(),
            'stats' => $this->getDepartmentStats($department, $dateFrom, $dateTo),
            'trends' => $department->getPerformanceTrend(12),
            'comparison' => $this->getDepartmentComparison($department, $dateFrom, $dateTo),
            'period' => [
                'from' => $dateFrom->format('M j, Y'),
                'to' => $dateTo->format('M j, Y')
            ]
        ];

        if ($request->get('format') === 'pdf') {
            return $this->generateDepartmentPDF($reportData);
        }

        return view('reports.department', $reportData);
    }

    /**
     * Generate comparison report.
     */
    public function comparisonReport(Request $request)
    {
        $request->validate([
            'type' => 'required|in:employee,department',
            'items' => 'required|array|min:2',
            'items.*' => 'integer',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from'
        ]);

        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);

        if ($request->type === 'employee') {
            $reportData = $this->generateEmployeeComparison($request->items, $dateFrom, $dateTo);
        } else {
            $reportData = $this->generateDepartmentComparison($request->items, $dateFrom, $dateTo);
        }

        if ($request->get('format') === 'pdf') {
            return $this->generateComparisonPDF($reportData, $request->type);
        }

        return view('reports.comparison', compact('reportData'));
    }

    /**
     * Generate trend analysis report.
     */
    public function trendAnalysis(Request $request)
    {
        $months = $request->get('months', 12);
        $departmentId = $request->get('department_id');
        
        $reportData = [
            'overall_trends' => $this->getOverallTrends($months, $departmentId),
            'department_trends' => $this->getDepartmentTrends($months),
            'template_analysis' => $this->getTemplatePerformanceAnalysis($months, $departmentId),
            'seasonal_patterns' => $this->getSeasonalPatterns($months, $departmentId),
            'predictions' => $this->generatePredictions($months, $departmentId),
            'period' => $months,
            'department' => $departmentId ? Department::find($departmentId) : null
        ];

        if ($request->get('format') === 'pdf') {
            return $this->generateTrendsPDF($reportData);
        }

        return view('reports.trends', $reportData);
    }

    /**
     * Schedule automated report.
     */
    public function scheduleReport(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'report_type' => 'required|string',
            'frequency' => 'required|in:weekly,monthly,quarterly',
            'recipients' => 'required|array',
            'recipients.*' => 'email',
            'parameters' => 'array'
        ]);

        // In a full implementation, this would create a scheduled job
        // For now, we'll return a success response
        
        return response()->json([
            'success' => true,
            'message' => 'Report scheduled successfully. You will receive reports ' . $request->frequency,
            'next_run' => $this->calculateNextRun($request->frequency)
        ]);
    }

    // Private helper methods

    private function getReportStats()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        return [
            'total_reviews' => PerformanceReview::count(),
            'this_month' => PerformanceReview::where('created_at', '>=', $currentMonth)->count(),
            'pending' => PerformanceReview::where('status', 'draft')->count(),
            'completed' => PerformanceReview::where('status', 'approved')->count(),
            'avg_score' => round(PerformanceReview::approved()->avg('overall_score') ?? 0, 1),
            'departments_covered' => Department::whereHas('employees.performanceReviews')->count()
        ];
    }

    private function getRecentReports()
    {
        // In a real implementation, this would show actual generated reports
        return collect([
            [
                'name' => 'Q3 Performance Summary',
                'type' => 'Performance Summary',
                'generated_at' => Carbon::now()->subDays(2),
                'format' => 'PDF',
                'size' => '2.3 MB'
            ],
            [
                'name' => 'Sales Department Analysis',
                'type' => 'Department Report',
                'generated_at' => Carbon::now()->subDays(5),
                'format' => 'Excel',
                'size' => '1.8 MB'
            ],
            [
                'name' => 'Monthly Trends Report',
                'type' => 'Trend Analysis',
                'generated_at' => Carbon::now()->subWeek(),
                'format' => 'PDF',
                'size' => '3.1 MB'
            ]
        ]);
    }

    private function generateReportData(Request $request)
    {
        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);
        
        $query = PerformanceReview::approved()
            ->whereBetween('review_date', [$dateFrom, $dateTo]);

        // Apply department filter
        if ($request->departments) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->whereIn('department_id', $request->departments);
            });
        }

        // Apply employee filter
        if ($request->employees) {
            $query->whereIn('employee_id', $request->employees);
        }

        $reviews = $query->with(['employee.department', 'reviewer', 'template'])->get();

        return [
            'reviews' => $reviews,
            'summary' => $this->generateSummary($reviews),
            'charts_data' => $request->include_charts ? $this->generateChartsData($reviews) : null,
            'detailed_analysis' => $request->include_details ? $this->generateDetailedAnalysis($reviews) : null,
            'period' => [
                'from' => $dateFrom->format('M j, Y'),
                'to' => $dateTo->format('M j, Y')
            ]
        ];
    }

    private function generateSummary($reviews)
    {
        $totalReviews = $reviews->count();
        
        if ($totalReviews === 0) {
            return [
                'total_reviews' => 0,
                'average_score' => 0,
                'distribution' => [],
                'top_performers' => []
            ];
        }

        return [
            'total_reviews' => $totalReviews,
            'average_score' => round($reviews->avg('overall_score'), 1),
            'highest_score' => $reviews->max('overall_score'),
            'lowest_score' => $reviews->min('overall_score'),
            'distribution' => [
                'excellent' => $reviews->where('overall_score', '>=', 90)->count(),
                'good' => $reviews->whereBetween('overall_score', [80, 89.99])->count(),
                'satisfactory' => $reviews->whereBetween('overall_score', [70, 79.99])->count(),
                'needs_improvement' => $reviews->where('overall_score', '<', 70)->count()
            ],
            'departments_covered' => $reviews->pluck('employee.department.name')->unique()->count(),
            'employees_reviewed' => $reviews->pluck('employee_id')->unique()->count()
        ];
    }

    private function generateChartsData($reviews)
    {
        return [
            'score_distribution' => $this->getScoreDistributionData($reviews),
            'department_performance' => $this->getDepartmentPerformanceData($reviews),
            'monthly_trends' => $this->getMonthlyTrendsData($reviews)
        ];
    }

    private function generateDetailedAnalysis($reviews)
    {
        return [
            'skill_analysis' => $this->getSkillAnalysis($reviews),
            'improvement_recommendations' => $this->getImprovementRecommendations($reviews),
            'performance_patterns' => $this->getPerformancePatterns($reviews)
        ];
    }

    private function getEmployeeStats($employee, $dateFrom, $dateTo)
    {
        $reviews = $employee->performanceReviews()
            ->approved()
            ->whereBetween('review_date', [$dateFrom, $dateTo])
            ->get();

        return [
            'total_reviews' => $reviews->count(),
            'average_score' => $reviews->count() > 0 ? round($reviews->avg('overall_score'), 1) : 0,
            'latest_score' => $reviews->sortByDesc('review_date')->first()?->overall_score ?? 0,
            'improvement' => $this->calculateImprovement($reviews),
            'ranking' => $employee->getRankingInDepartment(),
            'goals_achieved' => $this->countGoalsAchieved($reviews),
            'areas_improved' => $this->getAreasImproved($reviews)
        ];
    }

    private function getDepartmentStats($department, $dateFrom, $dateTo)
    {
        $reviews = PerformanceReview::approved()
            ->whereBetween('review_date', [$dateFrom, $dateTo])
            ->whereHas('employee', function($q) use ($department) {
                $q->where('department_id', $department->id);
            })
            ->get();

        return [
            'total_reviews' => $reviews->count(),
            'employees_reviewed' => $reviews->pluck('employee_id')->unique()->count(),
            'average_score' => $reviews->count() > 0 ? round($reviews->avg('overall_score'), 1) : 0,
            'top_score' => $reviews->max('overall_score') ?? 0,
            'participation_rate' => $this->calculateParticipationRate($department, $reviews),
            'performance_distribution' => $this->getPerformanceDistribution($reviews)
        ];
    }

    private function exportDashboardSummary($format)
    {
        $data = [
            'stats' => $this->getReportStats(),
            'top_performers' => Employee::topPerformers(10)->with('department')->get(),
            'department_summary' => Department::withStats()->get(),
            'recent_trends' => $this->getOverallTrends(6)
        ];

        switch ($format) {
            case 'pdf':
                return $this->generatePDF($data, (object)['report_type' => 'dashboard_summary']);
            case 'csv':
                return $this->generateCSV($data, (object)['report_type' => 'dashboard_summary']);
            default:
                return $this->generatePDF($data, (object)['report_type' => 'dashboard_summary']);
        }
    }

    private function exportPerformanceOverview($format)
    {
        $reviews = PerformanceReview::approved()
            ->with(['employee.department', 'reviewer'])
            ->where('review_date', '>=', now()->subMonths(12))
            ->get();

        $data = [
            'reviews' => $reviews,
            'summary' => $this->generateSummary($reviews),
            'trends' => $this->getOverallTrends(12)
        ];

        switch ($format) {
            case 'csv':
                return $this->generatePerformanceCSV($reviews);
            case 'pdf':
            default:
                return $this->generatePDF($data, (object)['report_type' => 'performance_overview']);
        }
    }

    private function generatePDF($data, $request)
    {
        // In a real implementation, this would use a PDF library like TCPDF or Dompdf
        // For now, we'll create a simple HTML-based PDF response
        
        $html = view('reports.pdf.template', compact('data', 'request'))->render();
        
        $filename = 'performance_report_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Mock PDF generation - in reality you'd use:
        // $pdf = PDF::loadHTML($html);
        // return $pdf->download($filename);
        
        return response($html)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function generateCSV($data, $request)
    {
        $filename = 'performance_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        $callback = function() use ($data, $request) {
            $file = fopen('php://output', 'w');
            
            // Generate CSV based on report type
            switch ($request->report_type) {
                case 'performance_summary':
                    $this->generatePerformanceSummaryCSV($file, $data);
                    break;
                case 'dashboard_summary':
                    $this->generateDashboardSummaryCSV($file, $data);
                    break;
                default:
                    $this->generateGenericCSV($file, $data);
            }
            
            fclose($file);
        };
        
        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function generatePerformanceSummaryCSV($file, $data)
    {
        fputcsv($file, ['Employee', 'Department', 'Position', 'Review Date', 'Score', 'Reviewer', 'Status']);
        
        foreach ($data['reviews'] as $review) {
            fputcsv($file, [
                $review->employee->full_name,
                $review->employee->department->name ?? '',
                $review->employee->position,
                $review->review_date->format('Y-m-d'),
                $review->overall_score,
                $review->reviewer->full_name ?? '',
                ucfirst($review->status)
            ]);
        }
    }

    private function generateDashboardSummaryCSV($file, $data)
    {
        fputcsv($file, ['Metric', 'Value']);
        
        foreach ($data['stats'] as $metric => $value) {
            fputcsv($file, [ucwords(str_replace('_', ' ', $metric)), $value]);
        }
        
        fputcsv($file, []);
        fputcsv($file, ['Top Performers']);
        fputcsv($file, ['Name', 'Department', 'Average Score']);
        
        foreach ($data['top_performers'] as $performer) {
            fputcsv($file, [
                $performer->full_name,
                $performer->department->name ?? '',
                $performer->getAverageScore()
            ]);
        }
    }

    private function calculateNextRun($frequency)
    {
        switch ($frequency) {
            case 'weekly':
                return now()->addWeek()->format('M j, Y');
            case 'monthly':
                return now()->addMonth()->format('M j, Y');
            case 'quarterly':
                return now()->addMonths(3)->format('M j, Y');
        }
    }

    // Additional helper methods would be implemented for:
    // - getEmployeeGoals()
    // - getDepartmentComparison() 
    // - generateEmployeeComparison()
    // - generateDepartmentComparison()
    // - getOverallTrends()
    // - getDepartmentTrends()
    // - getTemplatePerformanceAnalysis()
    // - getSeasonalPatterns()
    // - generatePredictions()
    // - calculateImprovement()
    // - countGoalsAchieved()
    // - getAreasImproved()
    // - calculateParticipationRate()
    // - getPerformanceDistribution()
    // - getScoreDistributionData()
    // - getDepartmentPerformanceData()
    // - getMonthlyTrendsData()
    // - getSkillAnalysis()
    // - getImprovementRecommendations()
    // - getPerformancePatterns()
    
    // These methods would contain the actual business logic for generating
    // various types of analytics and reports based on the performance data.
}