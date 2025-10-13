<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\Performance\PerformanceKPI;
use App\Models\Performance\PerformanceGoal;
use App\Models\Performance\PerformanceAlert;
use App\Models\Performance\KPIThreshold;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class KPIController extends Controller
{
    /**
     * Display a listing of KPIs
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user->hasAnyPermission([
            'performance.kpi.view_all',
            'performance.kpi.view_department',
            'performance.kpi.view_own'
        ])) {
            abort(403, 'No permission to view KPIs');
        }

        $query = PerformanceKPI::with(['department', 'threshold']);

        // Apply filters based on permissions
        if ($user->hasPermissionTo('performance.kpi.view_all')) {
            // Can view all KPIs
        } elseif ($user->hasPermissionTo('performance.kpi.view_department')) {
            // Can view department KPIs
            $query->where('department_id', $user->essentials_department_id);
        }

        // Apply additional filters
        if ($request->has('department_id')) {
            $query->where('department_id', $request->get('department_id'));
        }

        if ($request->has('calculation_method')) {
            $query->where('calculation_method', $request->get('calculation_method'));
        }

        $kpis = $query->orderBy('name')->paginate(20);

        // Calculate target achievement rate
        $targetAchievementRate = 0;
        if ($kpis->count() > 0) {
            $achievedTargets = $kpis->filter(function($kpi) {
                return $kpi->current_value >= $kpi->target_value;
            })->count();
            $targetAchievementRate = ($achievedTargets / $kpis->count()) * 100;
        }

        return view('essentials::performance.kpi_index', [
            'kpis' => $kpis,
            'departments' => \App\Category::where('category_type', 'department')->get(),
            'targetAchievementRate' => $targetAchievementRate,
            'calculationMethods' => [
                'direct_score' => 'Direct Score',
                'weighted_average' => 'Weighted Average',
                'formula' => 'Custom Formula',
                'percentage' => 'Percentage',
            ],
            'can' => [
                'create' => $user->hasPermissionTo('performance.kpi.configure'),
                'edit' => $user->hasPermissionTo('performance.kpi.configure'),
                'delete' => $user->hasPermissionTo('performance.kpi.configure'),
                'set_targets' => $user->hasPermissionTo('performance.kpi.set_targets'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new KPI
     */
    public function create()
    {
        $this->authorize('performance.kpi.configure');

        return Inertia::render('Performance/KPI/Create', [
            'departments' => \App\Category::where('category_type', 'department')->get(),
            'calculationMethods' => [
                'direct_score' => 'Direct Score',
                'weighted_average' => 'Weighted Average',
                'formula' => 'Custom Formula',
                'percentage' => 'Percentage',
            ],
        ]);
    }

    /**
     * Store a newly created KPI
     */
    public function store(Request $request)
    {
        $this->authorize('performance.kpi.configure');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:categories,id',
            'weight' => 'required|numeric|min:0.1|max:10',
            'target_value' => 'nullable|numeric|min:0|max:100',
            'calculation_method' => 'required|in:direct_score,weighted_average,formula,percentage',
            'scoring_formula' => 'nullable|string',
            'thresholds' => 'required|array',
            'thresholds.excellent_min' => 'required|numeric|min:0|max:10',
            'thresholds.good_min' => 'required|numeric|min:0|max:10',
            'thresholds.needs_improvement_min' => 'required|numeric|min:0|max:10',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $kpi = PerformanceKPI::create([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'department_id' => $validated['department_id'],
                    'weight' => $validated['weight'],
                    'target_value' => $validated['target_value'],
                    'calculation_method' => $validated['calculation_method'],
                    'scoring_formula' => $validated['scoring_formula'],
                ]);

                // Create thresholds
                KPIThreshold::create([
                    'kpi_id' => $kpi->id,
                    'excellent_min' => $validated['thresholds']['excellent_min'],
                    'good_min' => $validated['thresholds']['good_min'],
                    'needs_improvement_min' => $validated['thresholds']['needs_improvement_min'],
                ]);
            });

            return redirect()->route('performance.kpis.index')
                ->with('success', 'KPI created successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create KPI: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified KPI
     */
    public function show(PerformanceKPI $kpi)
    {
        $user = auth()->user();
        
        // Check permissions
        if (!$user->hasPermissionTo('performance.kpi.view_all') &&
            !($user->hasPermissionTo('performance.kpi.view_department') && $kpi->department_id === $user->essentials_department_id)) {
            abort(403, 'No permission to view this KPI');
        }

        $kpi->load(['department', 'threshold', 'performanceGoals.employee']);

        // Get KPI statistics
        $statistics = $this->getKPIStatistics($kpi);

        return Inertia::render('Performance/KPI/Show', [
            'kpi' => $kpi,
            'statistics' => $statistics,
            'can' => [
                'edit' => $user->hasPermissionTo('performance.kpi.configure'),
                'delete' => $user->hasPermissionTo('performance.kpi.configure'),
                'set_targets' => $user->hasPermissionTo('performance.kpi.set_targets'),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified KPI
     */
    public function edit(PerformanceKPI $kpi)
    {
        $this->authorize('performance.kpi.configure');

        $kpi->load('threshold');

        return Inertia::render('Performance/KPI/Edit', [
            'kpi' => $kpi,
            'departments' => \App\Category::where('category_type', 'department')->get(),
            'calculationMethods' => [
                'direct_score' => 'Direct Score',
                'weighted_average' => 'Weighted Average',
                'formula' => 'Custom Formula',
                'percentage' => 'Percentage',
            ],
        ]);
    }

    /**
     * Update the specified KPI
     */
    public function update(Request $request, PerformanceKPI $kpi)
    {
        $this->authorize('performance.kpi.configure');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:categories,id',
            'weight' => 'required|numeric|min:0.1|max:10',
            'target_value' => 'nullable|numeric|min:0|max:100',
            'calculation_method' => 'required|in:direct_score,weighted_average,formula,percentage',
            'scoring_formula' => 'nullable|string',
            'thresholds' => 'required|array',
            'thresholds.excellent_min' => 'required|numeric|min:0|max:10',
            'thresholds.good_min' => 'required|numeric|min:0|max:10',
            'thresholds.needs_improvement_min' => 'required|numeric|min:0|max:10',
        ]);

        try {
            DB::transaction(function () use ($kpi, $validated) {
                $kpi->update([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'department_id' => $validated['department_id'],
                    'weight' => $validated['weight'],
                    'target_value' => $validated['target_value'],
                    'calculation_method' => $validated['calculation_method'],
                    'scoring_formula' => $validated['scoring_formula'],
                ]);

                // Update thresholds
                $kpi->threshold()->updateOrCreate(
                    ['kpi_id' => $kpi->id],
                    [
                        'excellent_min' => $validated['thresholds']['excellent_min'],
                        'good_min' => $validated['thresholds']['good_min'],
                        'needs_improvement_min' => $validated['thresholds']['needs_improvement_min'],
                    ]
                );
            });

            return redirect()->route('performance.kpis.index')
                ->with('success', 'KPI updated successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update KPI: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified KPI
     */
    public function destroy(PerformanceKPI $kpi)
    {
        $this->authorize('performance.kpi.configure');

        if (!$kpi->canBeDeleted()) {
            return back()->withErrors(['error' => 'Cannot delete KPI that is being used in templates or reports']);
        }

        try {
            $kpi->delete();
            return redirect()->route('performance.kpis.index')
                ->with('success', 'KPI deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete KPI: ' . $e->getMessage()]);
        }
    }

    /**
     * Get KPI analytics
     */
    public function analytics(PerformanceKPI $kpi)
    {
        $user = auth()->user();
        
        // Check permissions
        if (!$user->hasPermissionTo('performance.kpi.view_all') &&
            !($user->hasPermissionTo('performance.kpi.view_department') && $kpi->department_id === $user->essentials_department_id)) {
            abort(403, 'No permission to view KPI analytics');
        }

        $analytics = $this->getKPIAnalytics($kpi);

        return Inertia::render('Performance/KPI/Analytics', [
            'kpi' => $kpi->load(['department', 'threshold']),
            'analytics' => $analytics,
        ]);
    }

    /**
     * Set performance goals for KPI
     */
    public function setGoals(Request $request, PerformanceKPI $kpi)
    {
        $this->authorize('performance.kpi.set_targets');

        $validated = $request->validate([
            'goals' => 'required|array',
            'goals.*.employee_id' => 'required|exists:users,id',
            'goals.*.target_score' => 'required|numeric|min:0|max:10',
            'goals.*.period_start' => 'required|date',
            'goals.*.period_end' => 'required|date|after:goals.*.period_start',
        ]);

        try {
            DB::transaction(function () use ($kpi, $validated) {
                foreach ($validated['goals'] as $goalData) {
                    PerformanceGoal::updateOrCreate(
                        [
                            'employee_id' => $goalData['employee_id'],
                            'kpi_id' => $kpi->id,
                            'period_start' => $goalData['period_start'],
                            'period_end' => $goalData['period_end'],
                        ],
                        [
                            'target_score' => $goalData['target_score'],
                            'status' => 'active',
                        ]
                    );
                }
            });

            return response()->json(['success' => true, 'message' => 'Goals set successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to set goals: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get KPI performance data
     */
    public function getPerformanceData(PerformanceKPI $kpi, Request $request)
    {
        $user = $request->user();
        
        // Check permissions
        if (!$user->hasPermissionTo('performance.kpi.view_all') &&
            !($user->hasPermissionTo('performance.kpi.view_department') && $kpi->department_id === $user->essentials_department_id)) {
            abort(403, 'No permission to view KPI performance data');
        }

        $period = $request->get('period', '3months');
        $data = $this->getKPIPerformanceData($kpi, $period);

        return response()->json($data);
    }

    /**
     * Get KPI statistics
     */
    private function getKPIStatistics(PerformanceKPI $kpi): array
    {
        $kpiScores = $kpi->kpiScores()->with('report.user')->get();
        
        if ($kpiScores->isEmpty()) {
            return [
                'total_scores' => 0,
                'average_score' => 0,
                'max_score' => 0,
                'min_score' => 0,
                'score_distribution' => [],
                'recent_trend' => 'stable',
            ];
        }

        $scores = $kpiScores->pluck('calculated_score');
        
        return [
            'total_scores' => $kpiScores->count(),
            'average_score' => round($scores->avg(), 2),
            'max_score' => $scores->max(),
            'min_score' => $scores->min(),
            'score_distribution' => [
                'excellent' => $scores->where('calculated_score', '>=', $kpi->threshold->excellent_min ?? 9.0)->count(),
                'good' => $scores->whereBetween('calculated_score', [$kpi->threshold->good_min ?? 7.5, $kpi->threshold->excellent_min ?? 9.0])->count(),
                'needs_improvement' => $scores->where('calculated_score', '<', $kpi->threshold->good_min ?? 7.5)->count(),
            ],
            'recent_trend' => $this->calculateKPITrend($kpi),
        ];
    }

    /**
     * Get KPI analytics data
     */
    private function getKPIAnalytics(PerformanceKPI $kpi): array
    {
        $kpiScores = $kpi->kpiScores()
            ->with(['report.user', 'report.template'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $analytics = [
            'performance_over_time' => $this->getPerformanceOverTime($kpiScores),
            'department_breakdown' => $this->getDepartmentBreakdown($kpiScores),
            'top_performers' => $this->getTopPerformers($kpiScores),
            'improvement_areas' => $this->getImprovementAreas($kpiScores),
        ];

        return $analytics;
    }

    /**
     * Get KPI performance data for charts
     */
    private function getKPIPerformanceData(PerformanceKPI $kpi, string $period): array
    {
        $startDate = $this->getStartDateForPeriod($period);
        
        $kpiScores = $kpi->kpiScores()
            ->whereHas('report', function ($query) use ($startDate) {
                $query->where('review_date', '>=', $startDate);
            })
            ->with('report')
            ->orderBy('created_at')
            ->get();

        $performanceData = [];
        $monthlyData = [];

        foreach ($kpiScores as $score) {
            $month = $score->report->review_date->format('Y-m');
            
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = [];
            }
            
            $monthlyData[$month][] = $score->calculated_score;
        }

        foreach ($monthlyData as $month => $scores) {
            $performanceData[] = [
                'month' => $month,
                'average_score' => round(array_sum($scores) / count($scores), 2),
                'count' => count($scores),
            ];
        }

        return $performanceData;
    }

    /**
     * Calculate KPI trend
     */
    private function calculateKPITrend(PerformanceKPI $kpi): string
    {
        $recentScores = $kpi->kpiScores()
            ->with('report')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->pluck('calculated_score');

        if ($recentScores->count() < 2) {
            return 'stable';
        }

        $firstHalf = $recentScores->take(5)->avg();
        $secondHalf = $recentScores->skip(5)->avg();

        if ($secondHalf > $firstHalf + 0.5) {
            return 'improving';
        } elseif ($secondHalf < $firstHalf - 0.5) {
            return 'declining';
        } else {
            return 'stable';
        }
    }

    /**
     * Get performance over time
     */
    private function getPerformanceOverTime($kpiScores): array
    {
        $monthlyData = [];
        
        foreach ($kpiScores as $score) {
            $month = $score->report->review_date->format('Y-m');
            
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = [];
            }
            
            $monthlyData[$month][] = $score->calculated_score;
        }

        $performanceOverTime = [];
        foreach ($monthlyData as $month => $scores) {
            $performanceOverTime[] = [
                'month' => $month,
                'average_score' => round(array_sum($scores) / count($scores), 2),
                'count' => count($scores),
            ];
        }

        return $performanceOverTime;
    }

    /**
     * Get department breakdown
     */
    private function getDepartmentBreakdown($kpiScores): array
    {
        $departmentData = [];
        
        foreach ($kpiScores as $score) {
            $departmentId = $score->report->user->essentials_department_id;
            $departmentName = $score->report->user->department->name ?? 'No Department';
            
            if (!isset($departmentData[$departmentName])) {
                $departmentData[$departmentName] = [];
            }
            
            $departmentData[$departmentName][] = $score->calculated_score;
        }

        $breakdown = [];
        foreach ($departmentData as $department => $scores) {
            $breakdown[] = [
                'department' => $department,
                'average_score' => round(array_sum($scores) / count($scores), 2),
                'count' => count($scores),
            ];
        }

        return $breakdown;
    }

    /**
     * Get top performers for KPI
     */
    private function getTopPerformers($kpiScores): array
    {
        $topPerformers = $kpiScores
            ->sortByDesc('calculated_score')
            ->take(5)
            ->map(function ($score) {
                return [
                    'employee_name' => $score->report->user->first_name . ' ' . $score->report->user->last_name,
                    'score' => $score->calculated_score,
                    'date' => $score->report->review_date,
                ];
            })
            ->values();

        return $topPerformers->toArray();
    }

    /**
     * Get improvement areas
     */
    private function getImprovementAreas($kpiScores): array
    {
        $lowScores = $kpiScores
            ->where('calculated_score', '<', 7.0)
            ->sortBy('calculated_score')
            ->take(5)
            ->map(function ($score) {
                return [
                    'employee_name' => $score->report->user->first_name . ' ' . $score->report->user->last_name,
                    'score' => $score->calculated_score,
                    'date' => $score->report->review_date,
                ];
            })
            ->values();

        return $lowScores->toArray();
    }

    /**
     * Get start date for period
     */
    private function getStartDateForPeriod(string $period): \Carbon\Carbon
    {
        return match($period) {
            '1month' => \Carbon\Carbon::now()->subMonth(),
            '3months' => \Carbon\Carbon::now()->subMonths(3),
            '6months' => \Carbon\Carbon::now()->subMonths(6),
            '1year' => \Carbon\Carbon::now()->subYear(),
            default => \Carbon\Carbon::now()->subMonths(3),
        };
    }

    /**
     * Export KPIs to CSV
     */
    public function export(Request $request)
    {
        $user = $request->user();
        
        if (!$user->hasAnyPermission([
            'performance.kpi.view_all',
            'performance.kpi.view_department',
            'performance.kpi.view_own'
        ])) {
            abort(403, 'No permission to export KPIs');
        }

        $query = PerformanceKPI::with(['department', 'threshold']);

        // Apply filters based on permissions
        if ($user->hasPermissionTo('performance.kpi.view_all')) {
            // Can view all KPIs
        } elseif ($user->hasPermissionTo('performance.kpi.view_department')) {
            // Can view department KPIs
            $query->where('department_id', $user->essentials_department_id);
        }

        $kpis = $query->orderBy('name')->get();

        $filename = 'kpis_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($kpis) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Description',
                'Department',
                'Weight',
                'Target Value',
                'Calculation Method',
                'Created At',
                'Updated At'
            ]);

            // CSV data
            foreach ($kpis as $kpi) {
                fputcsv($file, [
                    $kpi->id,
                    $kpi->name,
                    $kpi->description,
                    $kpi->department->name ?? 'N/A',
                    $kpi->weight,
                    $kpi->target_value,
                    $kpi->calculation_method,
                    $kpi->created_at->format('Y-m-d H:i:s'),
                    $kpi->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
