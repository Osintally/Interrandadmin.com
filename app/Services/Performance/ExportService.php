<?php

namespace App\Services\Performance;

use App\Models\Performance\PerformanceReport;
use App\Models\Performance\PerformanceKPI;
use App\Models\Performance\PerformanceAggregation;
use App\Models\Performance\PerformanceGoal;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportService
{
    /**
     * Export performance reports to Excel
     */
    public function exportReportsToExcel(array $filters = []): string
    {
        $reports = $this->getFilteredReports($filters);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = [
            'A1' => 'Employee Name',
            'B1' => 'Department',
            'C1' => 'Template Name',
            'D1' => 'Overall Score',
            'E1' => 'Status',
            'F1' => 'Submitted Date',
            'G1' => 'Reviewer',
            'H1' => 'Approved Date'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Style headers
        $headerRange = 'A1:H1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '366092']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ]);
        
        // Add data
        $row = 2;
        foreach ($reports as $report) {
            $sheet->setCellValue("A{$row}", $report->employee->first_name . ' ' . $report->employee->surname);
            $sheet->setCellValue("B{$row}", $report->employee->department->name ?? 'N/A');
            $sheet->setCellValue("C{$row}", $report->template->name);
            $sheet->setCellValue("D{$row}", $report->overall_score);
            $sheet->setCellValue("E{$row}", ucfirst($report->status));
            $sheet->setCellValue("F{$row}", $report->submitted_at ? $report->submitted_at->format('Y-m-d H:i:s') : 'N/A');
            $sheet->setCellValue("G{$row}", $report->reviewer ? $report->reviewer->first_name . ' ' . $report->reviewer->surname : 'N/A');
            $sheet->setCellValue("H{$row}", $report->approved_at ? $report->approved_at->format('Y-m-d H:i:s') : 'N/A');
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Save file
        $filename = 'performance_reports_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = storage_path('app/exports/' . $filename);
        
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filepath;
    }
    
    /**
     * Export KPI analytics to Excel
     */
    public function exportKPIToExcel(array $filters = []): string
    {
        $kpis = $this->getFilteredKPIs($filters);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = [
            'A1' => 'KPI Name',
            'B1' => 'Department',
            'C1' => 'Weight (%)',
            'D1' => 'Target Value',
            'E1' => 'Average Score',
            'F1' => 'Excellent Threshold',
            'G1' => 'Good Threshold',
            'H1' => 'Needs Improvement Threshold',
            'I1' => 'Calculation Method'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Style headers
        $headerRange = 'A1:I1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '366092']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ]);
        
        // Add data
        $row = 2;
        foreach ($kpis as $kpi) {
            $sheet->setCellValue("A{$row}", $kpi->name);
            $sheet->setCellValue("B{$row}", $kpi->department->name ?? 'All Departments');
            $sheet->setCellValue("C{$row}", $kpi->weight);
            $sheet->setCellValue("D{$row}", $kpi->target_value ?? 'N/A');
            $sheet->setCellValue("E{$row}", $this->getKPIAverageScore($kpi));
            $sheet->setCellValue("F{$row}", $kpi->thresholds->where('type', 'excellent')->first()->threshold_value ?? 'N/A');
            $sheet->setCellValue("G{$row}", $kpi->thresholds->where('type', 'good')->first()->threshold_value ?? 'N/A');
            $sheet->setCellValue("H{$row}", $kpi->thresholds->where('type', 'needs_improvement')->first()->threshold_value ?? 'N/A');
            $sheet->setCellValue("I{$row}", ucfirst(str_replace('_', ' ', $kpi->calculation_method)));
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Save file
        $filename = 'kpi_analytics_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = storage_path('app/exports/' . $filename);
        
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filepath;
    }
    
    /**
     * Export performance goals to Excel
     */
    public function exportGoalsToExcel(array $filters = []): string
    {
        $goals = $this->getFilteredGoals($filters);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = [
            'A1' => 'Employee Name',
            'B1' => 'KPI Name',
            'C1' => 'Target Score',
            'D1' => 'Current Score',
            'E1' => 'Achievement %',
            'F1' => 'Status',
            'G1' => 'Period Start',
            'H1' => 'Period End',
            'I1' => 'Created Date'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Style headers
        $headerRange = 'A1:I1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '366092']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ]);
        
        // Add data
        $row = 2;
        foreach ($goals as $goal) {
            $sheet->setCellValue("A{$row}", $goal->employee->first_name . ' ' . $goal->employee->surname);
            $sheet->setCellValue("B{$row}", $goal->kpi->name);
            $sheet->setCellValue("C{$row}", $goal->target_score);
            $sheet->setCellValue("D{$row}", $goal->current_score);
            $sheet->setCellValue("E{$row}", $goal->achievement_percentage);
            $sheet->setCellValue("F{$row}", ucfirst($goal->status));
            $sheet->setCellValue("G{$row}", $goal->period_start->format('Y-m-d'));
            $sheet->setCellValue("H{$row}", $goal->period_end->format('Y-m-d'));
            $sheet->setCellValue("I{$row}", $goal->created_at->format('Y-m-d H:i:s'));
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Save file
        $filename = 'performance_goals_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = storage_path('app/exports/' . $filename);
        
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filepath;
    }
    
    /**
     * Generate performance report PDF
     */
    public function generateReportPDF(int $reportId): string
    {
        $report = PerformanceReport::with(['employee', 'template', 'reviewer', 'kpiScores.kpi'])
            ->findOrFail($reportId);
        
        $data = [
            'report' => $report,
            'kpiScores' => $report->kpiScores,
            'generated_at' => now()
        ];
        
        $pdf = Pdf::loadView('performance.reports.pdf', $data);
        
        $filename = 'performance_report_' . $reportId . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $filepath = storage_path('app/exports/' . $filename);
        
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $pdf->save($filepath);
        
        return $filepath;
    }
    
    /**
     * Generate dashboard summary PDF
     */
    public function generateDashboardPDF(array $filters = []): string
    {
        $data = [
            'summary' => $this->getDashboardSummary($filters),
            'kpis' => $this->getFilteredKPIs($filters),
            'goals' => $this->getFilteredGoals($filters),
            'generated_at' => now()
        ];
        
        $pdf = Pdf::loadView('performance.dashboard.pdf', $data);
        
        $filename = 'performance_dashboard_' . date('Y-m-d_H-i-s') . '.pdf';
        $filepath = storage_path('app/exports/' . $filename);
        
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $pdf->save($filepath);
        
        return $filepath;
    }
    
    /**
     * Get filtered performance reports
     */
    private function getFilteredReports(array $filters): Collection
    {
        $query = PerformanceReport::with(['employee', 'template', 'reviewer']);
        
        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        
        if (isset($filters['department_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        return $query->get();
    }
    
    /**
     * Get filtered KPIs
     */
    private function getFilteredKPIs(array $filters): Collection
    {
        $query = PerformanceKPI::with(['department', 'thresholds']);
        
        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        
        return $query->get();
    }
    
    /**
     * Get filtered goals
     */
    private function getFilteredGoals(array $filters): Collection
    {
        $query = PerformanceGoal::with(['employee', 'kpi']);
        
        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('period_start', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('period_end', '<=', $filters['date_to']);
        }
        
        return $query->get();
    }
    
    /**
     * Get dashboard summary data
     */
    private function getDashboardSummary(array $filters): array
    {
        $reports = $this->getFilteredReports($filters);
        
        return [
            'total_employees' => $reports->unique('employee_id')->count(),
            'completed_reviews' => $reports->where('status', 'approved')->count(),
            'pending_reviews' => $reports->whereIn('status', ['submitted', 'draft'])->count(),
            'average_score' => $reports->where('overall_score', '>', 0)->avg('overall_score') ?? 0
        ];
    }
    
    /**
     * Get KPI average score
     */
    private function getKPIAverageScore(PerformanceKPI $kpi): float
    {
        $scores = $kpi->scores()->avg('score');
        return round($scores ?? 0, 2);
    }
}
