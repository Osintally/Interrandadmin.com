<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\EmployeeSalary;
use App\Models\PayrollRecord;
use App\Business;
use App\User;
use Exception;

class VerifyAttendanceSetup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:verify-setup 
                            {--fix : Attempt to fix detected issues}
                            {--create-sample : Create sample data for testing}';

    /**
     * The console command description.
     */
    protected $description = 'Verify attendance automation setup and configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verifying Attendance & Payroll Automation Setup...');
        $this->newLine();

        $issues = [];
        $warnings = [];

        // Check database tables
        $this->info('📊 Checking Database Tables...');
        $tableIssues = $this->checkDatabaseTables();
        $issues = array_merge($issues, $tableIssues);

        // Check Laravel commands
        $this->info('⚙️ Checking Laravel Commands...');
        $commandIssues = $this->checkCommands();
        $issues = array_merge($issues, $commandIssues);

        // Check file permissions and directories
        $this->info('📁 Checking File System...');
        $fileIssues = $this->checkFileSystem();
        $issues = array_merge($issues, $fileIssues);

        // Check configuration
        $this->info('🔧 Checking Configuration...');
        $configIssues = $this->checkConfiguration();
        $warnings = array_merge($warnings, $configIssues);

        // Check business and employee data
        $this->info('🏢 Checking Business Data...');
        $dataIssues = $this->checkBusinessData();
        $warnings = array_merge($warnings, $dataIssues);

        // Check scheduler setup
        $this->info('⏰ Checking Scheduler...');
        $scheduleIssues = $this->checkScheduler();
        $warnings = array_merge($warnings, $scheduleIssues);

        // Summary
        $this->newLine();
        $this->info('📋 Setup Verification Summary:');
        
        if (empty($issues) && empty($warnings)) {
            $this->info('✅ All checks passed! Your attendance automation is ready.');
        } else {
            if (!empty($issues)) {
                $this->error("❌ Found {" . count($issues) . "} critical issues:");
                foreach ($issues as $issue) {
                    $this->line("   • $issue");
                }
            }

            if (!empty($warnings)) {
                $this->warn("⚠️  Found {" . count($warnings) . "} warnings:");
                foreach ($warnings as $warning) {
                    $this->line("   • $warning");
                }
            }
        }

        // Offer to fix issues
        if (!empty($issues) && $this->option('fix')) {
            $this->newLine();
            $this->info('🔧 Attempting to fix detected issues...');
            $this->fixIssues($issues);
        }

        // Create sample data if requested
        if ($this->option('create-sample')) {
            $this->newLine();
            $this->info('📝 Creating sample data for testing...');
            $this->createSampleData();
        }

        $this->newLine();
        $this->info('🎯 Next Steps:');
        $this->line('   1. Fix any critical issues shown above');
        $this->line('   2. Set up your cron job: * * * * * php artisan schedule:run');
        $this->line('   3. Test commands manually before going live');
        $this->line('   4. Monitor logs in storage/logs/laravel.log');

        return empty($issues) ? 0 : 1;
    }

    /**
     * Check if required database tables exist and have correct structure.
     */
    private function checkDatabaseTables(): array
    {
        $issues = [];
        $requiredTables = [
            'attendance_logs',
            'attendance_settings', 
            'employee_salaries',
            'payroll_records'
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $issues[] = "Missing database table: $table";
            }
        }

        // Check specific columns
        if (Schema::hasTable('attendance_logs')) {
            $requiredColumns = ['id', 'business_id', 'user_id', 'attendance_date', 'clock_in', 'clock_out'];
            foreach ($requiredColumns as $column) {
                if (!Schema::hasColumn('attendance_logs', $column)) {
                    $issues[] = "Missing column in attendance_logs: $column";
                }
            }
        }

        return $issues;
    }

    /**
     * Check if attendance commands are available.
     */
    private function checkCommands(): array
    {
        $issues = [];
        
        try {
            // Check if commands are registered
            $commands = Artisan::all();
            
            if (!isset($commands['attendance:process'])) {
                $issues[] = "Command not found: attendance:process";
            }
            
            if (!isset($commands['attendance:generate-payroll'])) {
                $issues[] = "Command not found: attendance:generate-payroll";
            }

        } catch (Exception $e) {
            $issues[] = "Error checking commands: " . $e->getMessage();
        }

        return $issues;
    }

    /**
     * Check file system permissions and directory structure.
     */
    private function checkFileSystem(): array
    {
        $issues = [];
        
        // Check Laravel directories
        $laravelDirs = [
            'storage/app',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views'
        ];

        foreach ($laravelDirs as $dir) {
            $path = base_path($dir);
            if (!is_dir($path)) {
                $issues[] = "Missing directory: $dir";
            } elseif (!is_writable($path)) {
                $issues[] = "Directory not writable: $dir";
            }
        }

        // Check if we can create test file
        try {
            $testFile = storage_path('app/attendance_test.tmp');
            file_put_contents($testFile, 'test');
            if (file_exists($testFile)) {
                unlink($testFile);
            } else {
                $issues[] = "Cannot write to storage directory";
            }
        } catch (Exception $e) {
            $issues[] = "File system error: " . $e->getMessage();
        }

        return $issues;
    }

    /**
     * Check Laravel configuration.
     */
    private function checkConfiguration(): array
    {
        $warnings = [];

        // Check mail configuration
        if (!config('mail.default')) {
            $warnings[] = "Mail driver not configured - email notifications won't work";
        }

        // Check database connection
        try {
            DB::connection()->getPdo();
        } catch (Exception $e) {
            $warnings[] = "Database connection issue: " . $e->getMessage();
        }

        // Check timezone
        if (config('app.timezone') !== 'Africa/Lagos') {
            $warnings[] = "App timezone is " . config('app.timezone') . " - consider using Africa/Lagos for Nigerian businesses";
        }

        return $warnings;
    }

    /**
     * Check business and employee data setup.
     */
    private function checkBusinessData(): array
    {
        $warnings = [];

        try {
            $businessCount = Business::count();
            if ($businessCount == 0) {
                $warnings[] = "No businesses found - create businesses first";
            } else {
                $this->line("   ✓ Found $businessCount business(es)");
            }

            $employeeCount = EmployeeSalary::where('active', true)->count();
            if ($employeeCount == 0) {
                $warnings[] = "No active employee salaries found - payroll generation won't work";
            } else {
                $this->line("   ✓ Found $employeeCount active employee salary record(s)");
            }

            $attendanceCount = AttendanceLog::count();
            if ($attendanceCount == 0) {
                $warnings[] = "No attendance records found - this is normal for new setup";
            } else {
                $this->line("   ✓ Found $attendanceCount attendance record(s)");
            }

        } catch (Exception $e) {
            $warnings[] = "Error checking business data: " . $e->getMessage();
        }

        return $warnings;
    }

    /**
     * Check Laravel scheduler setup.
     */
    private function checkScheduler(): array
    {
        $warnings = [];

        try {
            // Check if schedule commands are defined
            $schedule = app()->make('Illuminate\Console\Scheduling\Schedule');
            $events = $schedule->events();
            
            if (empty($events)) {
                $warnings[] = "No scheduled events found";
            } else {
                $attendanceEvents = 0;
                foreach ($events as $event) {
                    if (strpos($event->command, 'attendance:') !== false) {
                        $attendanceEvents++;
                    }
                }
                
                if ($attendanceEvents == 0) {
                    $warnings[] = "No attendance events in scheduler";
                } else {
                    $this->line("   ✓ Found $attendanceEvents scheduled attendance event(s)");
                }
            }

        } catch (Exception $e) {
            $warnings[] = "Error checking scheduler: " . $e->getMessage();
        }

        // Check for common cron issues
        if (PHP_SAPI !== 'cli') {
            $warnings[] = "Not running in CLI mode - scheduler needs to run via command line";
        }

        return $warnings;
    }

    /**
     * Attempt to fix common issues.
     */
    private function fixIssues(array $issues): void
    {
        foreach ($issues as $issue) {
            if (strpos($issue, 'Missing directory') !== false) {
                // Extract directory name and create it
                preg_match('/Missing directory: (.+)/', $issue, $matches);
                if (isset($matches[1])) {
                    $dir = base_path($matches[1]);
                    if (!is_dir($dir) && mkdir($dir, 0755, true)) {
                        $this->info("✅ Created directory: {$matches[1]}");
                    }
                }
            }

            if (strpos($issue, 'not writable') !== false) {
                // Extract directory name and fix permissions
                preg_match('/Directory not writable: (.+)/', $issue, $matches);
                if (isset($matches[1])) {
                    $dir = base_path($matches[1]);
                    if (chmod($dir, 0755)) {
                        $this->info("✅ Fixed permissions for: {$matches[1]}");
                    }
                }
            }
        }
    }

    /**
     * Create sample data for testing.
     */
    private function createSampleData(): void
    {
        try {
            // Create sample business if none exists
            $business = Business::first();
            if (!$business) {
                $this->warn("No businesses found to create sample data");
                return;
            }

            // Create sample attendance setting
            $setting = AttendanceSetting::getOrCreateForBusiness($business->id);
            $this->line("✅ Created/verified attendance settings for {$business->name}");

            // Create sample employee salary if none exists
            $users = User::where('business_id', $business->id)->limit(2)->get();
            foreach ($users as $user) {
                $existingSalary = EmployeeSalary::where('business_id', $business->id)
                    ->where('user_id', $user->id)
                    ->where('active', true)
                    ->first();

                if (!$existingSalary) {
                    EmployeeSalary::create([
                        'business_id' => $business->id,
                        'user_id' => $user->id,
                        'basic_salary' => 150000,
                        'house_allowance' => 50000,
                        'transport_allowance' => 30000,
                        'meal_allowance' => 20000,
                        'medical_allowance' => 25000,
                        'other_allowances' => 0,
                        'tax_deduction' => 15000,
                        'pension_deduction' => 7500,
                        'insurance_deduction' => 5000,
                        'other_deductions' => 0,
                        'salary_effective_date' => now()->subMonth(),
                        'active' => true,
                    ]);
                    $this->line("✅ Created sample salary for {$user->user_full_name}");
                }
            }

            // Create sample attendance log
            if (AttendanceLog::count() == 0 && $users->isNotEmpty()) {
                AttendanceLog::create([
                    'business_id' => $business->id,
                    'user_id' => $users->first()->id,
                    'date' => now()->format('Y-m-d'),
                    'check_in' => now()->setHour(8)->setMinute(0),
                    'check_out' => now()->setHour(17)->setMinute(0),
                    'location_check_in' => 'Office',
                    'location_check_out' => 'Office',
                    'notes' => 'Sample attendance record for testing'
                ]);
                $this->line("✅ Created sample attendance record");
            }

            $this->info("📝 Sample data creation completed!");

        } catch (Exception $e) {
            $this->error("❌ Error creating sample data: " . $e->getMessage());
        }
    }
}