<?php

namespace App\Services\Performance;

use App\Models\Performance\PerformanceKPI;
use App\Models\Performance\KPIScore;
use App\Models\Performance\TemplateKPIMapping;
use App\Models\Performance\PerformanceGoal;
use App\Models\Performance\PerformanceAlert;
use Modules\Essentials\Entities\EssentialsPerformanceReview;
use Modules\Essentials\Entities\EssentialsPerformanceTemplate;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class KPICalculationService
{
    /**
     * Calculate KPI scores for a performance report
     */
    public function calculateKPIScores(EssentialsPerformanceReview $report): array
    {
        $template = $report->template;
        $formData = $report->form_data ?? [];
        $kpiScores = [];
        $totalWeightedScore = 0;
        $totalWeight = 0;

        // Get all KPI mappings for this template
        $kpiMappings = $template->kpiMappings()->with('kpi.threshold')->get();

        foreach ($kpiMappings as $mapping) {
            $fieldValue = $formData[$mapping->field_name] ?? null;
            
            if ($fieldValue !== null) {
                $score = $this->calculateFieldKPI($mapping, $fieldValue);
                
                $kpiScore = KPIScore::create([
                    'report_id' => $report->id,
                    'kpi_id' => $mapping->kpi_id,
                    'field_value' => $fieldValue,
                    'calculated_score' => $score,
                    'weight_applied' => $mapping->weight,
                ]);

                $kpiScores[] = $kpiScore;
                $totalWeightedScore += $score * $mapping->weight;
                $totalWeight += $mapping->weight;

                // Check for threshold breaches and create alerts
                $this->checkThresholdBreach($report->user_id, $mapping->kpi, $score);
            }
        }

        $overallScore = $totalWeight > 0 ? $totalWeightedScore / $totalWeight : 0;
        
        $report->update([
            'overall_score' => $overallScore,
            'calculated_kpis' => $this->formatKPIResults($kpiScores),
        ]);

        // Update performance goals
        $this->updatePerformanceGoals($report->user_id, $kpiScores);

        return $kpiScores;
    }

    /**
     * Calculate KPI score for a specific field
     */
    private function calculateFieldKPI(TemplateKPIMapping $mapping, $fieldValue): float
    {
        $kpi = $mapping->kpi;
        
        switch ($kpi->calculation_method) {
            case 'direct_score':
                return (float) $fieldValue;
                
            case 'percentage':
                if ($kpi->target_value && $kpi->target_value > 0) {
                    return ($fieldValue / $kpi->target_value) * 10;
                }
                return (float) $fieldValue;
                
            case 'formula':
                return $this->evaluateCustomFormula($mapping->scoring_formula ?? $kpi->scoring_formula, $fieldValue);
                
            case 'weighted_average':
                return $this->calculateWeightedAverage($fieldValue, $mapping);
                
            default:
                return (float) $fieldValue;
        }
    }

    /**
     * Evaluate custom formula safely
     */
    private function evaluateCustomFormula(string $formula, $value): float
    {
        if (empty($formula)) {
            return (float) $value;
        }

        // Safe formula evaluation
        $formula = str_replace('value', $value, $formula);
        $formula = preg_replace('/[^0-9+\-*\/().\s]/', '', $formula);
        
        try {
            $result = eval("return $formula;");
            return is_numeric($result) ? (float) $result : 0;
        } catch (\Exception $e) {
            Log::error("KPI Formula evaluation error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate weighted average (placeholder for complex calculations)
     */
    private function calculateWeightedAverage($fieldValue, TemplateKPIMapping $mapping): float
    {
        // This would need more complex logic based on multiple field values
        // For now, return the field value as is
        return (float) $fieldValue;
    }

    /**
     * Format KPI results for storage
     */
    private function formatKPIResults(array $kpiScores): array
    {
        return collect($kpiScores)->map(function ($score) {
            return [
                'kpi_id' => $score->kpi_id,
                'kpi_name' => $score->kpi->name,
                'field_value' => $score->field_value,
                'calculated_score' => $score->calculated_score,
                'weight_applied' => $score->weight_applied,
                'weighted_score' => $score->getWeightedScore(),
                'threshold' => $score->getThreshold(),
                'threshold_color' => $score->getThresholdColor(),
                'threshold_label' => $score->getThresholdLabel(),
            ];
        })->toArray();
    }

    /**
     * Get real-time KPI preview for form data
     */
    public function getRealTimeKPIPreview(array $formData, $templateId): array
    {
        $template = EssentialsPerformanceTemplate::with('kpiMappings.kpi.threshold')->find($templateId);
        
        if (!$template) {
            return [];
        }

        $kpiPreviews = [];

        foreach ($template->kpiMappings as $mapping) {
            $fieldValue = $formData[$mapping->field_name] ?? null;
            
            if ($fieldValue !== null) {
                $score = $this->calculateFieldKPI($mapping, $fieldValue);
                $threshold = $mapping->kpi->getScoreThreshold($score);
                
                $kpiPreviews[] = [
                    'kpi_id' => $mapping->kpi_id,
                    'kpi_name' => $mapping->kpi->name,
                    'field_name' => $mapping->field_name,
                    'field_value' => $fieldValue,
                    'score' => $score,
                    'weight' => $mapping->weight,
                    'weighted_score' => $score * $mapping->weight,
                    'threshold' => $threshold,
                    'threshold_color' => $mapping->kpi->getScoreColor($threshold),
                    'threshold_label' => $mapping->kpi->getScoreLabel($threshold),
                    'target_value' => $mapping->kpi->target_value,
                    'is_above_target' => $mapping->kpi->target_value ? $score >= $mapping->kpi->target_value : null,
                ];
            }
        }

        return $kpiPreviews;
    }

    /**
     * Calculate overall score from KPI previews
     */
    public function calculateOverallScore(array $kpiPreviews): float
    {
        if (empty($kpiPreviews)) {
            return 0;
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($kpiPreviews as $preview) {
            $totalWeightedScore += $preview['weighted_score'];
            $totalWeight += $preview['weight'];
        }

        return $totalWeight > 0 ? $totalWeightedScore / $totalWeight : 0;
    }

    /**
     * Check for threshold breaches and create alerts
     */
    private function checkThresholdBreach($employeeId, PerformanceKPI $kpi, float $score): void
    {
        $threshold = $kpi->getScoreThreshold($score);
        
        // Create alert for poor performance
        if ($threshold === 'needs_improvement') {
            $message = "Performance threshold breached for {$kpi->name}. Score: {$score}/10";
            
            PerformanceAlert::createThresholdBreachAlert(
                $employeeId,
                $kpi->id,
                $threshold,
                $message
            );
        }
    }

    /**
     * Update performance goals based on KPI scores
     */
    private function updatePerformanceGoals($employeeId, array $kpiScores): void
    {
        foreach ($kpiScores as $kpiScore) {
            $goals = PerformanceGoal::forEmployee($employeeId)
                ->forKPI($kpiScore->kpi_id)
                ->active()
                ->get();

            foreach ($goals as $goal) {
                $goal->updateCurrentScore($kpiScore->calculated_score);
                
                // Check if goal is missed and create alert
                if ($goal->isMissed()) {
                    $message = "Performance goal missed for {$goal->kpi->name}. Target: {$goal->target_score}, Current: {$goal->current_score}";
                    
                    PerformanceAlert::createGoalMissedAlert(
                        $employeeId,
                        $goal->kpi_id,
                        $message
                    );
                }
            }
        }
    }

    /**
     * Calculate performance trends for an employee
     */
    public function calculatePerformanceTrends($employeeId, $periods = 4): array
    {
        $reviews = EssentialsPerformanceReview::where('user_id', $employeeId)
            ->where('status', 'approved')
            ->orderBy('review_date', 'desc')
            ->limit($periods)
            ->get();

        $trends = [];
        $previousScore = null;

        foreach ($reviews as $review) {
            $currentScore = $review->overall_score;
            $trend = 'stable';
            
            if ($previousScore !== null) {
                if ($currentScore > $previousScore + 0.5) {
                    $trend = 'improving';
                } elseif ($currentScore < $previousScore - 0.5) {
                    $trend = 'declining';
                }
            }

            $trends[] = [
                'period' => $review->review_period,
                'date' => $review->review_date,
                'score' => $currentScore,
                'trend' => $trend,
                'change' => $previousScore ? $currentScore - $previousScore : 0,
            ];

            $previousScore = $currentScore;
        }

        return array_reverse($trends); // Return in chronological order
    }

    /**
     * Calculate department performance summary
     */
    public function calculateDepartmentPerformance($departmentId, $startDate = null, $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subMonths(3);
        $endDate = $endDate ?? Carbon::now();

        $reviews = EssentialsPerformanceReview::whereHas('user', function ($query) use ($departmentId) {
            $query->where('essentials_department_id', $departmentId);
        })
        ->where('status', 'approved')
        ->whereBetween('review_date', [$startDate, $endDate])
        ->get();

        if ($reviews->isEmpty()) {
            return [
                'average_score' => 0,
                'total_reviews' => 0,
                'performance_distribution' => [],
                'top_performers' => [],
                'improvement_areas' => [],
            ];
        }

        $scores = $reviews->pluck('overall_score');
        $averageScore = $scores->avg();

        // Performance distribution
        $distribution = [
            'excellent' => $scores->where('overall_score', '>=', 9.0)->count(),
            'good' => $scores->whereBetween('overall_score', [7.5, 8.9])->count(),
            'satisfactory' => $scores->whereBetween('overall_score', [6.0, 7.4])->count(),
            'needs_improvement' => $scores->where('overall_score', '<', 6.0)->count(),
        ];

        // Top performers
        $topPerformers = $reviews->sortByDesc('overall_score')->take(5)->map(function ($review) {
            return [
                'employee_id' => $review->user_id,
                'employee_name' => $review->user->first_name . ' ' . $review->user->last_name,
                'score' => $review->overall_score,
                'period' => $review->review_period,
            ];
        })->values();

        return [
            'average_score' => round($averageScore, 2),
            'total_reviews' => $reviews->count(),
            'performance_distribution' => $distribution,
            'top_performers' => $topPerformers,
            'improvement_areas' => $this->getDepartmentImprovementAreas($departmentId, $startDate, $endDate),
        ];
    }

    /**
     * Get department improvement areas
     */
    private function getDepartmentImprovementAreas($departmentId, $startDate, $endDate): array
    {
        $kpiScores = KPIScore::whereHas('report', function ($query) use ($departmentId, $startDate, $endDate) {
            $query->whereHas('user', function ($q) use ($departmentId) {
                $q->where('essentials_department_id', $departmentId);
            })
            ->whereBetween('review_date', [$startDate, $endDate]);
        })
        ->with('kpi')
        ->get();

        $kpiAverages = $kpiScores->groupBy('kpi_id')->map(function ($scores) {
            return [
                'kpi_name' => $scores->first()->kpi->name,
                'average_score' => $scores->avg('calculated_score'),
                'count' => $scores->count(),
            ];
        });

        return $kpiAverages->where('average_score', '<', 7.0)->sortBy('average_score')->take(3)->values()->toArray();
    }

    /**
     * Generate performance insights
     */
    public function generatePerformanceInsights($employeeId): array
    {
        $reviews = EssentialsPerformanceReview::where('user_id', $employeeId)
            ->where('status', 'approved')
            ->orderBy('review_date', 'desc')
            ->limit(3)
            ->get();

        if ($reviews->isEmpty()) {
            return [
                'insights' => [],
                'recommendations' => [],
                'trends' => [],
            ];
        }

        $latestReview = $reviews->first();
        $trends = $this->calculatePerformanceTrends($employeeId, 3);
        
        $insights = [];
        $recommendations = [];

        // Overall performance insight
        $overallScore = $latestReview->overall_score;
        if ($overallScore >= 9.0) {
            $insights[] = "Excellent overall performance with a score of {$overallScore}/10";
            $recommendations[] = "Consider mentoring other team members";
        } elseif ($overallScore >= 7.5) {
            $insights[] = "Good performance with room for improvement";
            $recommendations[] = "Focus on specific areas for enhancement";
        } else {
            $insights[] = "Performance needs improvement";
            $recommendations[] = "Develop a detailed improvement plan";
        }

        // Trend analysis
        if (count($trends) >= 2) {
            $latestTrend = $trends[count($trends) - 1]['trend'];
            if ($latestTrend === 'improving') {
                $insights[] = "Performance is showing positive trends";
            } elseif ($latestTrend === 'declining') {
                $insights[] = "Performance trend is declining";
                $recommendations[] = "Schedule regular check-ins with supervisor";
            }
        }

        return [
            'insights' => $insights,
            'recommendations' => $recommendations,
            'trends' => $trends,
        ];
    }
}
