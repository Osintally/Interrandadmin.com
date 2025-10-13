<?php

namespace App\Exports;

use App\Models\PayrollRecord;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PayrollExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $payroll;

    public function __construct(PayrollRecord $payroll)
    {
        $this->payroll = $payroll;
    }

    public function array(): array
    {
        return [
            [
                'Employee Name' => $this->payroll->user->user_full_name ?? 'N/A',
                'Employee ID' => $this->payroll->user->employee_id ?? 'N/A',
                'Payroll Period' => $this->payroll->payroll_period,
                'Period Start' => $this->payroll->period_start ? Carbon::parse($this->payroll->period_start)->format('Y-m-d') : 'N/A',
                'Period End' => $this->payroll->period_end ? Carbon::parse($this->payroll->period_end)->format('Y-m-d') : 'N/A',
                'Working Days' => $this->payroll->total_working_days,
                'Days Present' => $this->payroll->days_present,
                'Days Absent' => $this->payroll->days_absent,
                'Days Late' => $this->payroll->days_late,
                'Days Very Late' => $this->payroll->days_very_late,
                'Days Early Leave' => $this->payroll->days_early_leave,
                'Total Hours Worked' => $this->payroll->total_hours_worked,
                'Overtime Hours' => $this->payroll->overtime_hours,
                'Basic Salary' => $this->payroll->basic_salary,
                'Allowances Total' => $this->payroll->allowances_total,
                'Overtime Amount' => $this->payroll->overtime_amount,
                'Gross Amount' => $this->payroll->gross_amount,
                'Attendance Deductions' => $this->payroll->attendance_deductions,
                'Tax Deductions' => $this->payroll->tax_deductions,
                'Other Deductions' => $this->payroll->other_deductions,
                'Total Deductions' => $this->payroll->total_deductions,
                'Net Amount' => $this->payroll->net_amount,
                'Status' => ucfirst($this->payroll->status),
                'Generated At' => $this->payroll->calculated_at ? $this->payroll->calculated_at->format('Y-m-d H:i:s') : 'N/A',
                'Approved At' => $this->payroll->approved_at ? $this->payroll->approved_at->format('Y-m-d H:i:s') : 'N/A',
                'Paid At' => $this->payroll->paid_at ? $this->payroll->paid_at->format('Y-m-d H:i:s') : 'N/A',
                'Notes' => $this->payroll->notes ?? ''
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Employee ID',
            'Payroll Period',
            'Period Start',
            'Period End',
            'Working Days',
            'Days Present',
            'Days Absent',
            'Days Late',
            'Days Very Late',
            'Days Early Leave',
            'Total Hours Worked',
            'Overtime Hours',
            'Basic Salary',
            'Allowances Total',
            'Overtime Amount',
            'Gross Amount',
            'Attendance Deductions',
            'Tax Deductions',
            'Other Deductions',
            'Total Deductions',
            'Net Amount',
            'Status',
            'Generated At',
            'Approved At',
            'Paid At',
            'Notes'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold
            1 => ['font' => ['bold' => true]],
            
            // Style the data row
            2 => [
                'font' => ['size' => 11],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Employee Name
            'B' => 15, // Employee ID
            'C' => 15, // Payroll Period
            'D' => 12, // Period Start
            'E' => 12, // Period End
            'F' => 12, // Working Days
            'G' => 12, // Days Present
            'H' => 12, // Days Absent
            'I' => 12, // Days Late
            'J' => 15, // Days Very Late
            'K' => 15, // Days Early Leave
            'L' => 15, // Total Hours Worked
            'M' => 15, // Overtime Hours
            'N' => 15, // Basic Salary
            'O' => 15, // Allowances Total
            'P' => 15, // Overtime Amount
            'Q' => 15, // Gross Amount
            'R' => 20, // Attendance Deductions
            'S' => 15, // Tax Deductions
            'T' => 15, // Other Deductions
            'U' => 15, // Total Deductions
            'V' => 15, // Net Amount
            'W' => 12, // Status
            'X' => 18, // Generated At
            'Y' => 18, // Approved At
            'Z' => 18, // Paid At
            'AA' => 30, // Notes
        ];
    }

    public function title(): string
    {
        return 'Payroll Record';
    }
}
