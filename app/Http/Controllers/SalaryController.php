<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\EmployeeSalary;
use App\User;
use Carbon\Carbon;

class SalaryController extends Controller
{
    /**
     * Constructor - Apply middleware and permissions.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:salary.view_all|salary.view_own')->only(['index', 'getSettings']);
        $this->middleware('permission:salary.create')->only(['store']);
        $this->middleware('permission:salary.view_all|salary.view_own')->only(['show']);
        $this->middleware('permission:salary.edit')->only(['update']);
        $this->middleware('permission:salary.delete')->only(['destroy']);
        $this->middleware('permission:salary.import')->only(['import', 'bulkImport']);
        $this->middleware('permission:salary.export')->only(['export']);
        // Temporarily disabled permission check for template download
        // $this->middleware('permission:salary.view_all|salary.view_own|salary.import')->only(['downloadTemplate']);
    }

    /**
     * Display salary management dashboard.
     */
    public function index()
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        // Get employees with current salary settings
        $employeesWithSalary = EmployeeSalary::where('business_id', $businessId)
            ->where('active', true)
            ->with('user')
            ->get();
        
        // Get employees without salary settings
        $employeesWithoutSalary = User::where('business_id', $businessId)
            ->whereNotIn('id', $employeesWithSalary->pluck('user_id'))
            ->get();

        return view('salary.index', compact('employeesWithSalary', 'employeesWithoutSalary'));
    }

    /**
     * Get all salary settings.
     */
    public function getSettings(Request $request)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $settings = EmployeeSalary::with('user')
            ->where('business_id', $businessId)
            ->where('active', true)
            ->get()
            ->map(function ($setting) {
                return [
                    'id' => $setting->id,
                    'user_id' => $setting->user_id,
                    'employee_name' => trim($setting->user->surname . ' ' . $setting->user->first_name . ' ' . $setting->user->last_name) ?: 'Unknown User',
                    'employee_id' => $setting->employee_id,
                    'department' => $setting->department,
                    'position' => $setting->position,
                    'basic_salary' => $setting->basic_salary,
                    'house_allowance' => $setting->house_allowance,
                    'transport_allowance' => $setting->transport_allowance,
                    'meal_allowance' => $setting->meal_allowance,
                    'medical_allowance' => $setting->medical_allowance,
                    'other_allowances' => $setting->other_allowances,
                    'tax_deduction' => $setting->tax_deduction,
                    'pension_deduction' => $setting->pension_deduction,
                    'insurance_deduction' => $setting->insurance_deduction,
                    'other_deductions' => $setting->other_deductions,
                    'gross_salary' => $setting->gross_salary,
                    'total_deductions' => $setting->total_deductions,
                    'net_salary' => $setting->net_salary,
                    'salary_effective_date' => $setting->salary_effective_date->format('Y-m-d'),
                    'salary_end_date' => $setting->salary_end_date ? $setting->salary_end_date->format('Y-m-d') : null,
                    'active' => $setting->active,
                    'created_at' => $setting->created_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Get employees without salary settings.
     */
    public function getEmployeesWithoutSalary()
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        // Get users with active salary settings
        $usersWithSalary = EmployeeSalary::where('business_id', $businessId)
            ->where('active', true)
            ->pluck('user_id');

        $employees = User::where('business_id', $businessId)
            ->whereNotIn('id', $usersWithSalary)
            ->select('id', 'surname', 'first_name', 'last_name', 'username', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => trim($user->surname . ' ' . $user->first_name . ' ' . $user->last_name),
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'username' => $user->username,
                    'email' => $user->email
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $employees
        ]);
    }

    /**
     * Create or update salary setting.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'employee_id' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'basic_salary' => 'required|numeric|min:0',
            'house_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'other_allowances' => 'nullable|numeric|min:0',
            'tax_deduction' => 'nullable|numeric|min:0',
            'pension_deduction' => 'nullable|numeric|min:0',
            'insurance_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|in:monthly,weekly,daily,hourly',
            'hourly_rate' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'employment_start_date' => 'nullable|date',
            'salary_effective_date' => 'nullable|date',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:100'
        ]);

        $businessId = session('user.business_id') ?? auth()->user()->business_id;

        // Validate that user belongs to the business
        $user = User::where('id', $request->user_id)
            ->where('business_id', $businessId)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or does not belong to your business'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Deactivate existing salary records for this user
            EmployeeSalary::where('business_id', $businessId)
                ->where('user_id', $request->user_id)
                ->where('active', true)
                ->update(['active' => false, 'salary_end_date' => Carbon::today()->subDay()]);

            // Create new salary record
            $data = $request->all();
            $data['business_id'] = $businessId;
            $data['salary_effective_date'] = $data['salary_effective_date'] ?? Carbon::today();
            $data['active'] = true;

            // Set default values for all numeric fields
            $data['house_allowance'] = $data['house_allowance'] ?? 0;
            $data['transport_allowance'] = $data['transport_allowance'] ?? 0;
            $data['meal_allowance'] = $data['meal_allowance'] ?? 0;
            $data['medical_allowance'] = $data['medical_allowance'] ?? 0;
            $data['other_allowances'] = $data['other_allowances'] ?? 0;
            $data['tax_deduction'] = $data['tax_deduction'] ?? 0;
            $data['pension_deduction'] = $data['pension_deduction'] ?? 0;
            $data['insurance_deduction'] = $data['insurance_deduction'] ?? 0;
            $data['other_deductions'] = $data['other_deductions'] ?? 0;
            $data['salary_type'] = $data['salary_type'] ?? 'monthly';
            $data['hourly_rate'] = $data['hourly_rate'] ?? null;
            $data['daily_rate'] = $data['daily_rate'] ?? null;

            $salarySetting = EmployeeSalary::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Salary setting created/updated successfully',
                'data' => $salarySetting->load('user')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error saving salary setting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get salary setting details.
     */
    public function show($id)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        try {
            $setting = EmployeeSalary::with('user')
                ->where('business_id', $businessId)
                ->where('id', $id)
                ->first();

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salary setting not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $setting->id,
                    'user_id' => $setting->user_id,
                    'employee_name' => trim($setting->user->surname . ' ' . $setting->user->first_name . ' ' . $setting->user->last_name) ?: 'Unknown User',
                    'employee_id' => $setting->employee_id,
                    'department' => $setting->department,
                    'position' => $setting->position,
                    'basic_salary' => $setting->basic_salary,
                    'house_allowance' => $setting->house_allowance,
                    'transport_allowance' => $setting->transport_allowance,
                    'meal_allowance' => $setting->meal_allowance,
                    'medical_allowance' => $setting->medical_allowance,
                    'other_allowances' => $setting->other_allowances,
                    'gross_salary' => $setting->gross_salary,
                    'tax_deduction' => $setting->tax_deduction,
                    'pension_deduction' => $setting->pension_deduction,
                    'insurance_deduction' => $setting->insurance_deduction,
                    'other_deductions' => $setting->other_deductions,
                    'total_deductions' => $setting->total_deductions,
                    'net_salary' => $setting->net_salary,
                    'salary_type' => $setting->salary_type ?? 'monthly',
                    'hourly_rate' => $setting->hourly_rate,
                    'daily_rate' => $setting->daily_rate,
                    'employment_start_date' => $setting->employment_start_date ? $setting->employment_start_date->format('Y-m-d') : null,
                    'salary_effective_date' => $setting->salary_effective_date->format('Y-m-d'),
                    'salary_end_date' => $setting->salary_end_date ? $setting->salary_end_date->format('Y-m-d') : null,
                    'bank_name' => $setting->bank_name,
                    'account_number' => $setting->account_number,
                    'account_name' => $setting->account_name,
                    'active' => $setting->active,
                    'notes' => $setting->notes,
                    'created_at' => $setting->created_at->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading salary details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading salary details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update salary setting.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'position' => 'nullable|string|max:255',
            'gross_salary' => 'required|numeric|min:0',
            'basic_salary' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'currency' => 'required|in:NGN,USD,EUR,GBP',
            'salary_type' => 'required|in:monthly,daily,hourly',
            'hourly_rate' => 'nullable|numeric|min:0',
            'effective_from' => 'nullable|date'
        ]);

        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $setting = EmployeeSalary::where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Salary setting not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            $data = $request->all();
            
            // Set default values
            $data['basic_salary'] = $data['basic_salary'] ?? $data['gross_salary'];
            $data['allowances'] = $data['allowances'] ?? 0;
            $data['deductions'] = $data['deductions'] ?? 0;

            // Validate hourly rate for hourly salary type
            if ($data['salary_type'] === 'hourly' && empty($data['hourly_rate'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hourly rate is required for hourly salary type'
                ], 400);
            }

            // If effective date is changed or salary is changed significantly, create new record
            $effectiveFrom = $data['effective_from'] ?? $setting->effective_from;
            $salaryChanged = abs($setting->gross_salary - $data['gross_salary']) > 0.01;
            
            if ($salaryChanged || $effectiveFrom != $setting->effective_from->format('Y-m-d')) {
                // Create new salary setting
                $newSetting = EmployeeSalary::createOrUpdate($businessId, $setting->user_id, $data);
                $result = $newSetting->load(['user', 'creator']);
            } else {
                // Update existing setting
                $setting->update($data);
                $result = $setting->fresh(['user', 'creator']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Salary setting updated successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating salary setting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete salary setting.
     */
    public function destroy($id)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $setting = EmployeeSalary::where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Salary setting not found'
            ], 404);
        }

        try {
            // Check if there are any payroll records using this salary setting
            $payrollCount = $setting->user->payrollRecords()
                ->where('business_id', $businessId)
                ->count();

            if ($payrollCount > 0) {
                // Don't delete, just deactivate
                $setting->update([
                    'is_active' => false,
                    'effective_to' => Carbon::yesterday()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Salary setting deactivated successfully (payroll records exist)'
                ]);
            } else {
                // Safe to delete
                $setting->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Salary setting deleted successfully'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting salary setting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get salary history for a user.
     */
    public function getHistory($userId)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        // Validate user belongs to business
        $user = User::where('id', $userId)
            ->where('business_id', $businessId)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $history = EmployeeSalary::getSalaryHistory($businessId, $userId)
            ->map(function ($setting) {
                return [
                    'id' => $setting->id,
                    'position' => $setting->position,
                    'gross_salary' => $setting->gross_salary,
                    'currency' => $setting->currency,
                    'salary_type' => $setting->salary_type,
                    'effective_from' => $setting->effective_from->format('Y-m-d'),
                    'effective_to' => $setting->effective_to ? $setting->effective_to->format('Y-m-d') : null,
                    'is_active' => $setting->is_active,
                    'created_by' => $setting->creator ? trim($setting->creator->surname . ' ' . $setting->creator->first_name . ' ' . $setting->creator->last_name) : 'System',
                    'created_at' => $setting->created_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $user->id,
                    'name' => trim($user->surname . ' ' . $user->first_name . ' ' . $user->last_name),
                    'username' => $user->username
                ],
                'history' => $history
            ]
        ]);
    }

    /**
     * Bulk import salary settings.
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        $businessId = session('user.business_id') ?? auth()->user()->business_id;

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            // Read file content based on extension
            if (in_array($extension, ['xlsx', 'xls'])) {
                $data = $this->readExcelFile($file);
            } else {
                $data = $this->readCsvFile($file);
            }

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid data found in the uploaded file'
                ], 400);
            }

            DB::beginTransaction();

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($data as $index => $row) {
                try {
                    $result = $this->processSalaryRow($row, $businessId, $index + 2); // +2 for header row
                    $results[] = $result;
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    $results[] = [
                        'row' => $index + 2,
                        'success' => false,
                        'message' => 'Error processing row: ' . $e->getMessage()
                    ];
                    $errorCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Import completed. Success: $successCount, Errors: $errorCount",
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'results' => $results
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error importing salary data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export salary settings.
     */
    public function export()
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        $settings = EmployeeSalary::with('user')
            ->forBusiness($businessId)
            ->active()
            ->get();

        $csvHeader = [
            'Employee Name', 'Employee ID', 'Department', 'Position', 'Basic Salary', 'House Allowance', 'Transport Allowance', 'Meal Allowance', 'Medical Allowance', 'Other Allowances', 'Tax Deduction', 'Pension Deduction', 'Insurance Deduction', 'Other Deductions', 'Gross Salary', 'Net Salary', 'Effective From', 'Effective To'
        ];
        $filename = 'salary_settings_' . Carbon::now()->format('Y-m-d') . '.csv';

        $callback = function() use ($settings, $csvHeader) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $csvHeader);
            foreach ($settings as $setting) {
                fputcsv($file, [
                    trim($setting->user->surname . ' ' . $setting->user->first_name . ' ' . $setting->user->last_name) ?? 'Unknown User',
                    $setting->employee_id,
                    $setting->department,
                    $setting->position,
                    $setting->basic_salary,
                    $setting->house_allowance,
                    $setting->transport_allowance,
                    $setting->meal_allowance,
                    $setting->medical_allowance,
                    $setting->other_allowances,
                    $setting->tax_deduction,
                    $setting->pension_deduction,
                    $setting->insurance_deduction,
                    $setting->other_deductions,
                    $setting->gross_salary,
                    $setting->net_salary,
                    $setting->salary_effective_date ? $setting->salary_effective_date->format('Y-m-d') : '',
                    $setting->salary_end_date ? $setting->salary_end_date->format('Y-m-d') : 'Active'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename"
        ]);
    }

    /**
     * Import salary data from Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'salary_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        $businessId = session('user.business_id') ?? auth()->user()->business_id;

        try {
            $file = $request->file('salary_file');
            $extension = $file->getClientOriginalExtension();
            
            // Read file content based on extension
            if (in_array($extension, ['xlsx', 'xls'])) {
                $data = $this->readExcelFile($file);
            } else {
                $data = $this->readCsvFile($file);
            }

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid data found in the uploaded file'
                ], 400);
            }

            DB::beginTransaction();

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($data as $index => $row) {
                try {
                    $result = $this->processSalaryRow($row, $businessId, $index + 2); // +2 for header row
                    $results[] = $result;
                    
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    $results[] = [
                        'row' => $index + 2,
                        'success' => false,
                        'message' => 'Error processing row: ' . $e->getMessage()
                    ];
                    $errorCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Import completed. Success: $successCount, Errors: $errorCount",
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'results' => $results
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error importing salary data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Read Excel file data.
     */
    private function readExcelFile($file)
    {
        try {
            // Simple Excel reader for basic functionality
            $data = [];
            $path = $file->getRealPath();
            
            // For basic Excel support, convert to CSV first or use a simple reader
            // This is a basic implementation - for production use PhpSpreadsheet
            return $this->readCsvFile($file);
            
        } catch (\Exception $e) {
            Log::error('Error reading Excel file: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Read CSV file data.
     */
    private function readCsvFile($file)
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');
        
        if ($handle !== false) {
            $header = fgetcsv($handle); // Read header row
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) >= count($header)) {
                    $data[] = array_combine($header, $row);
                }
            }
            
            fclose($handle);
        }
        
        return $data;
    }

    /**
     * Process individual salary row from import.
     */
    private function processSalaryRow($row, $businessId, $rowNumber)
    {
        // Map CSV columns to database fields
        $mapping = [
            'employee_name' => ['Employee Name', 'employee_name', 'name'],
            'employee_id' => ['Employee ID', 'employee_id', 'emp_id'],
            'department' => ['Department', 'department'],
            'position' => ['Position', 'position', 'job_title'],
            'basic_salary' => ['Basic Salary', 'basic_salary', 'basic'],
            'house_allowance' => ['House Allowance', 'house_allowance', 'housing'],
            'transport_allowance' => ['Transport Allowance', 'transport_allowance', 'transport'],
            'meal_allowance' => ['Meal Allowance', 'meal_allowance', 'meal'],
            'medical_allowance' => ['Medical Allowance', 'medical_allowance', 'medical'],
            'other_allowances' => ['Other Allowances', 'other_allowances', 'other'],
            'tax_deduction' => ['Tax Deduction', 'tax_deduction', 'tax'],
            'pension_deduction' => ['Pension Deduction', 'pension_deduction', 'pension'],
            'insurance_deduction' => ['Insurance Deduction', 'insurance_deduction', 'insurance'],
            'other_deductions' => ['Other Deductions', 'other_deductions'],
        ];

        $salaryData = [];
        
        // Map row data to salary data
        foreach ($mapping as $field => $possibleColumns) {
            foreach ($possibleColumns as $column) {
                if (isset($row[$column])) {
                    $salaryData[$field] = $row[$column];
                    break;
                }
            }
        }

        // Find user by name or employee ID
        $user = null;
        if (!empty($salaryData['employee_name'])) {
            $nameParts = explode(' ', trim($salaryData['employee_name']));
            $user = User::where('business_id', $businessId)
                ->where(function ($q) use ($nameParts) {
                    foreach ($nameParts as $part) {
                        $q->orWhere('first_name', 'like', "%$part%")
                          ->orWhere('last_name', 'like', "%$part%")
                          ->orWhere('surname', 'like', "%$part%");
                    }
                })
                ->first();
        }

        if (!$user && !empty($salaryData['employee_id'])) {
            // Try to find by employee_id in existing salary records
            $existingSalary = EmployeeSalary::where('business_id', $businessId)
                ->where('employee_id', $salaryData['employee_id'])
                ->with('user')
                ->first();
            
            if ($existingSalary) {
                $user = $existingSalary->user;
            }
        }

        if (!$user) {
            return [
                'row' => $rowNumber,
                'success' => false,
                'message' => 'Employee not found: ' . ($salaryData['employee_name'] ?? $salaryData['employee_id'] ?? 'Unknown')
            ];
        }

        // Validate and clean salary data
        $cleanData = [
            'user_id' => $user->id,
            'employee_id' => $salaryData['employee_id'] ?? null,
            'department' => $salaryData['department'] ?? null,
            'position' => $salaryData['position'] ?? null,
            'basic_salary' => (float) ($salaryData['basic_salary'] ?? 0),
            'house_allowance' => (float) ($salaryData['house_allowance'] ?? 0),
            'transport_allowance' => (float) ($salaryData['transport_allowance'] ?? 0),
            'meal_allowance' => (float) ($salaryData['meal_allowance'] ?? 0),
            'medical_allowance' => (float) ($salaryData['medical_allowance'] ?? 0),
            'other_allowances' => (float) ($salaryData['other_allowances'] ?? 0),
            'tax_deduction' => (float) ($salaryData['tax_deduction'] ?? 0),
            'pension_deduction' => (float) ($salaryData['pension_deduction'] ?? 0),
            'insurance_deduction' => (float) ($salaryData['insurance_deduction'] ?? 0),
            'other_deductions' => (float) ($salaryData['other_deductions'] ?? 0),
            'salary_effective_date' => Carbon::today(),
            'active' => true
        ];

        if ($cleanData['basic_salary'] <= 0) {
            return [
                'row' => $rowNumber,
                'success' => false,
                'message' => 'Basic salary must be greater than 0'
            ];
        }

        // Create or update salary record
        $salary = EmployeeSalary::createOrUpdate($businessId, $user->id, $cleanData);

        return [
            'row' => $rowNumber,
            'success' => true,
            'employee' => trim($user->surname . ' ' . $user->first_name . ' ' . $user->last_name),
            'salary_id' => $salary->id
        ];
    }

    /**
     * Download CSV template for salary import.
     */
    public function downloadTemplate()
    {
        try {
            Log::info('Salary template download requested by user: ' . auth()->id());
        $headers = [
            'Employee Name',
            'Employee ID', 
            'Department',
            'Position',
            'Basic Salary',
            'House Allowance',
            'Transport Allowance',
            'Meal Allowance',
            'Medical Allowance',
            'Other Allowances',
            'Tax Deduction',
            'Pension Deduction', 
            'Insurance Deduction',
            'Other Deductions'
        ];

        $sampleData = [
            [
                'John Doe',
                'EMP001',
                'IT Department',
                'Software Developer',
                '150000',
                '30000',
                '20000',
                '15000',
                '10000',
                '5000',
                '15000',
                '7500',
                '5000',
                '2500'
            ],
            [
                'Jane Smith',
                'EMP002', 
                'HR Department',
                'HR Manager',
                '200000',
                '40000',
                '25000',
                '20000',
                '15000',
                '10000',
                '20000',
                '10000',
                '7500',
                '2500'
            ]
        ];

        $filename = 'salary_import_template.csv';
        
        $response = response()->streamDownload(function () use ($headers, $sampleData) {
            $handle = fopen('php://output', 'w');
            
            // Add BOM for proper Excel encoding
            fwrite($handle, "\xEF\xBB\xBF");
            
            // Write headers
            fputcsv($handle, $headers);
            
            // Write sample data
            foreach ($sampleData as $row) {
                fputcsv($handle, $row);
            }
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);

            Log::info('Salary template download completed successfully');
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Error downloading salary template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating template: ' . $e->getMessage()
            ], 500);
        }
    }
}
