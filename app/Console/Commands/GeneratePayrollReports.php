<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\EmployeeSalary;
use App\Models\PayrollRecord;
use App\Business;
use App\User;
use Carbon\Carbon;
use Exception;

class GeneratePayrollReports extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:generate-payroll 
                            {--business_id= : Specific business ID to process}
                            {--period= : Payroll period (YYYY-MM)}
                            {--send-reports : Email reports to administrators}
                            {--dry-run : Preview what would be generated}';

    /**
     * The console command description.
     */
    protected $description = 'Generate automated payroll reports based on attendance data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('💰 Starting automated payroll generation...');

        try {
            $businessId = $this->option('business_id');
            $period = $this->option('period') ?: Carbon::now()->subMonth()->format('Y-m');
            $sendReports = $this->option('send-reports');
            $dryRun = $this->option('dry-run');

            if ($dryRun) {
                $this->warn('📋 DRY RUN MODE: No payroll records will be created');
            }

            $this->info("📅 Processing payroll for period: {$period}");

            // Parse period
            $periodDate = Carbon::createFromFormat('Y-m', $period);
            $periodStart = $periodDate->copy()->startOfMonth();
            $periodEnd = $periodDate->copy()->endOfMonth();

            // Get businesses to process
            $businesses = $businessId ? 
                Business::where('id', $businessId)->get() : 
                Business::all();

            if ($businesses->isEmpty()) {
                $this->error('❌ No businesses found for processing');
                return 1;
            }

            $this->info("🏢 Processing {$businesses->count()} business(es)...");

            $totalGenerated = 0;
            $totalErrors = 0;

            foreach ($businesses as $business) {
                $result = $this->generateBusinessPayroll($business, $periodStart, $periodEnd, $dryRun);
                $totalGenerated += $result['generated'];
                $totalErrors += $result['errors'];

                if ($sendReports && !$dryRun && $result['generated'] > 0) {
                    $this->sendPayrollReport($business, $period, $result);
                }
            }

            $this->info("✅ Payroll generation completed:");
            $this->info("   💰 Generated: {$totalGenerated} payroll records");
            $this->info("   ❌ Errors: {$totalErrors}");

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error during payroll generation: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Generate payroll for a specific business.
     */
    private function generateBusinessPayroll(Business $business, Carbon $periodStart, Carbon $periodEnd, bool $dryRun): array
    {
        $this->info("🏢 Processing business: {$business->name}");

        $generated = 0;
        $errors = 0;
        $payrollRecords = [];

        try {
            // Get business settings
            $settings = AttendanceSetting::getOrCreateForBusiness($business->id);
            
            // Get all employees with salary information
            $employees = EmployeeSalary::where('business_id', $business->id)
                ->where('active', true)
                ->where('salary_effective_date', '<=', $periodEnd)
                ->where(function($query) use ($periodStart) {
                    $query->whereNull('salary_end_date')
                          ->orWhere('salary_end_date', '>=', $periodStart);
                })
                ->with('user')
                ->get();

            if ($employees->isEmpty()) {
                $this->warn("⚠️  No active employees found for {$business->name}");
                return ['generated' => 0, 'errors' => 0, 'records' => []];
            }

            $this->info("👥 Processing {$employees->count()} employees");

            foreach ($employees as $employee) {
                try {
                    // Check if payroll already exists for this period
                    $existingPayroll = PayrollRecord::where('business_id', $business->id)
                        ->where('user_id', $employee->user_id)
                        ->where('payroll_date', $periodEnd->format('Y-m-d'))
                        ->first();

                    if ($existingPayroll) {
                        $this->line("⏭️  Payroll already exists for {$employee->user->user_full_name}");
                        continue;
                    }

                    // Generate attendance summary for the period
                    $attendanceSummary = $this->getAttendanceSummary($business->id, $employee->user_id, $periodStart, $periodEnd);
                    
                    // Calculate payroll
                    $payrollData = $this->calculatePayroll($employee, $attendanceSummary, $settings, $periodStart, $periodEnd);
                    
                    if (!$dryRun) {
                        // Create payroll record
                        $payrollRecord = PayrollRecord::create($payrollData);
                        $payrollRecords[] = $payrollRecord;
                    } else {
                        $payrollRecords[] = $payrollData;
                    }

                    $generated++;
                    $this->line("✅ Generated payroll for {$employee->user->user_full_name}: ₦{$payrollData['net_amount']}");

                } catch (Exception $e) {
                    $errors++;
                    $this->error("❌ Error processing {$employee->user->user_full_name}: {$e->getMessage()}");
                }
            }

        } catch (Exception $e) {
            $this->error("❌ Error processing business {$business->name}: {$e->getMessage()}");
            $errors++;
        }

        return [
            'generated' => $generated,
            'errors' => $errors,
            'records' => $payrollRecords
        ];
    }

    /**
     * Get attendance summary for an employee in a period.
     */
    private function getAttendanceSummary(int $businessId, int $userId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $summary = AttendanceLog::getUserSummary($businessId, $userId, $periodStart, $periodEnd);

        return [
            'total_working_days' => $this->calculateWorkingDays($periodStart, $periodEnd),
            'days_present' => $summary->present_days ?? 0,
            'days_absent' => $summary->absent_days ?? 0,
            'days_late' => $summary->late_days ?? 0,
            'days_low_late' => $summary->low_late_days ?? 0,
            'days_very_late' => $summary->very_late_days ?? 0,
            'days_early_leave' => $summary->early_leave_days ?? 0,
            'total_hours_worked' => $summary->total_hours_worked ?? 0,
            'overtime_hours' => max(0, ($summary->total_hours_worked ?? 0) - ($summary->present_days ?? 0) * 8)
        ];
    }

    /**
     * Calculate working days in a period.
     */
    private function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $workingDays = 0;
        $current = $start->copy();

        while ($current <= $end) {
            // Count Monday to Friday as working days (1-5)
            if ($current->dayOfWeek >= 1 && $current->dayOfWeek <= 5) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Calculate payroll for an employee.
     */
    private function calculatePayroll(EmployeeSalary $employee, array $attendanceSummary, AttendanceSetting $settings, Carbon $periodStart, Carbon $periodEnd): array
    {
        // Basic calculations
        $basicSalary = $employee->basic_salary;
        $allowancesTotal = $employee->house_allowance + $employee->transport_allowance + 
                          $employee->meal_allowance + $employee->medical_allowance + 
                          $employee->other_allowances;
        
        // Calculate overtime (if enabled)
        $overtimeAmount = 0;
        if ($settings->overtime_enabled && $attendanceSummary['overtime_hours'] > 0) {
            $hourlyRate = $basicSalary / ($settings->working_days_per_month * $settings->working_hours_per_day);
            $overtimeAmount = $hourlyRate * $attendanceSummary['overtime_hours'] * $settings->overtime_multiplier;
        }

        $grossAmount = $basicSalary + $allowancesTotal + $overtimeAmount;

        // Calculate attendance-based deductions
        $attendanceDeductions = 0;
        
        // Absent days deduction
        $absentDeduction = $settings->calculateDeduction($basicSalary, 'absent', $attendanceSummary['days_absent']);
        $attendanceDeductions += $absentDeduction;
        
        // Late deductions
        $lateDeduction = $settings->calculateDeduction($basicSalary, 'late', $attendanceSummary['days_late']);
        $lowLateDeduction = $settings->calculateDeduction($basicSalary, 'low_late', $attendanceSummary['days_low_late']);
        $veryLateDeduction = $settings->calculateDeduction($basicSalary, 'very_late', $attendanceSummary['days_very_late']);
        $attendanceDeductions += $lateDeduction + $lowLateDeduction + $veryLateDeduction;
        
        // Early leave deduction
        $earlyLeaveDeduction = $settings->calculateDeduction($basicSalary, 'early_leave', $attendanceSummary['days_early_leave']);
        $attendanceDeductions += $earlyLeaveDeduction;

        // Standard deductions
        $taxDeductions = $employee->tax_deduction;
        $otherDeductions = $employee->pension_deduction + $employee->insurance_deduction + $employee->other_deductions;
        $totalDeductions = $attendanceDeductions + $taxDeductions + $otherDeductions;

        // Net amount
        $netAmount = $grossAmount - $totalDeductions;

        return [
            'business_id' => $employee->business_id,
            'user_id' => $employee->user_id,
            'salary_id' => $employee->id,
            'payroll_date' => $periodEnd->format('Y-m-d'),
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'total_working_days' => $attendanceSummary['total_working_days'],
            'days_present' => $attendanceSummary['days_present'],
            'days_absent' => $attendanceSummary['days_absent'],
            'days_late' => $attendanceSummary['days_late'],
            'days_low_late' => $attendanceSummary['days_low_late'],
            'days_very_late' => $attendanceSummary['days_very_late'],
            'days_early_leave' => $attendanceSummary['days_early_leave'],
            'total_hours_worked' => $attendanceSummary['total_hours_worked'],
            'overtime_hours' => $attendanceSummary['overtime_hours'],
            'basic_salary' => $basicSalary,
            'allowances_total' => $allowancesTotal,
            'overtime_amount' => $overtimeAmount,
            'gross_amount' => $grossAmount,
            'attendance_deductions' => $attendanceDeductions,
            'tax_deductions' => $taxDeductions,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_amount' => $netAmount,
            'status' => 'calculated',
            'calculated_by' => 1, // System
            'calculated_at' => now(),
            'notes' => 'Auto-generated by system'
        ];
    }

    /**
     * Send payroll report via email.
     */
    private function sendPayrollReport(Business $business, string $period, array $result): void
    {
        try {
            // Get business admin email
            $adminUser = User::where('business_id', $business->id)
                ->whereHas('roles', function($query) {
                    $query->where('name', 'Admin');
                })
                ->first();

            if (!$adminUser || !$adminUser->email) {
                $this->warn("📧 No admin email found for {$business->name}");
                return;
            }

            $totalPayroll = collect($result['records'])->sum('net_amount');
            $avgSalary = $result['generated'] > 0 ? $totalPayroll / $result['generated'] : 0;

            $subject = "Payroll Report - {$business->name} ({$period})";
            $message = "Automated payroll generation completed for {$period}:\n\n";
            $message .= "📊 Summary:\n";
            $message .= "   👥 Employees processed: {$result['generated']}\n";
            $message .= "   💰 Total payroll: ₦" . number_format($totalPayroll, 2) . "\n";
            $message .= "   📈 Average salary: ₦" . number_format($avgSalary, 2) . "\n";
            
            if ($result['errors'] > 0) {
                $message .= "   ❌ Processing errors: {$result['errors']}\n";
            }
            
            $message .= "\n";
            $message .= "Individual payroll records:\n";
            
            foreach ($result['records'] as $record) {
                $employee = User::find($record['user_id']);
                $message .= "- {$employee->user_full_name}: ₦" . number_format($record['net_amount'], 2) . "\n";
            }
            
            $message .= "\nPlease review and approve the payroll records in the system.\n";

            // Send email
            Mail::raw($message, function ($mail) use ($adminUser, $subject) {
                $mail->to($adminUser->email)
                     ->subject($subject);
            });

            $this->info("📧 Payroll report sent to {$adminUser->email}");

        } catch (Exception $e) {
            $this->warn("📧 Failed to send payroll report: {$e->getMessage()}");
        }
    }
}