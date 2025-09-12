<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Http\Requests\EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'manager']);
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }
        
        // Department filter
        if ($request->has('department') && $request->department) {
            $query->where('department_id', $request->department);
        }
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['first_name', 'last_name', 'email', 'hire_date', 'position', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        $employees = $query->paginate(15)->appends($request->query());
        $departments = Department::active()->orderBy('name')->get();
        
        return view('employees.index', compact('employees', 'departments'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $departments = Department::active()->orderBy('name')->get();
        $managers = Employee::active()
            ->whereIn('position', ['Manager', 'Senior Manager', 'HR Manager', 'Department Head'])
            ->orWhere('position', 'like', '%Manager%')
            ->orWhere('position', 'like', '%Lead%')
            ->orderBy('first_name')
            ->get();
            
        return view('employees.create', compact('departments', 'managers'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(EmployeeRequest $request)
    {
        $data = $request->validated();
        
        // Generate employee ID if not provided
        if (!isset($data['employee_id']) || empty($data['employee_id'])) {
            $data['employee_id'] = $this->generateEmployeeId();
        }
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        
        // Handle skills array
        if (isset($data['skills']) && is_array($data['skills'])) {
            $data['skills'] = array_filter($data['skills']); // Remove empty values
        }
        
        $employee = Employee::create($data);
        
        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee)
    {
        $employee->load([
            'department',
            'manager',
            'directReports',
            'performanceReviews' => function ($query) {
                $query->with('reviewer')
                      ->latest('review_date')
                      ->limit(5);
            }
        ]);
        
        // Get performance statistics
        $performanceStats = [
            'average_score' => $employee->getAverageScore(),
            'latest_score' => $employee->latest_performance_score,
            'total_reviews' => $employee->performanceReviews()->count(),
            'ranking' => $employee->getRankingInDepartment()
        ];
        
        // Get performance trend for last 12 months
        $performanceTrend = $employee->getPerformanceTrend(12);
        
        return view('employees.show', compact('employee', 'performanceStats', 'performanceTrend'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee)
    {
        $departments = Department::active()->orderBy('name')->get();
        $managers = Employee::active()
            ->where('id', '!=', $employee->id) // Exclude self
            ->whereIn('position', ['Manager', 'Senior Manager', 'HR Manager', 'Department Head'])
            ->orWhere('position', 'like', '%Manager%')
            ->orWhere('position', 'like', '%Lead%')
            ->orderBy('first_name')
            ->get();
            
        return view('employees.edit', compact('employee', 'departments', 'managers'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(EmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($employee->avatar) {
                Storage::disk('public')->delete($employee->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        
        // Handle skills array
        if (isset($data['skills']) && is_array($data['skills'])) {
            $data['skills'] = array_filter($data['skills']); // Remove empty values
        }
        
        $employee->update($data);
        
        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        // Check if employee has performance reviews
        if ($employee->performanceReviews()->count() > 0) {
            return redirect()
                ->route('employees.index')
                ->with('error', 'Cannot delete employee with existing performance reviews. Consider deactivating instead.');
        }
        
        // Check if employee is a manager
        if ($employee->directReports()->count() > 0) {
            return redirect()
                ->route('employees.index')
                ->with('error', 'Cannot delete employee who is managing other employees. Please reassign direct reports first.');
        }
        
        // Delete avatar file
        if ($employee->avatar) {
            Storage::disk('public')->delete($employee->avatar);
        }
        
        $employee->delete();
        
        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Generate unique employee ID.
     */
    private function generateEmployeeId()
    {
        do {
            $id = 'EMP' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Employee::where('employee_id', $id)->exists());
        
        return $id;
    }

    /**
     * Toggle employee status.
     */
    public function toggleStatus(Employee $employee)
    {
        $newStatus = $employee->status === 'active' ? 'inactive' : 'active';
        $employee->update(['status' => $newStatus]);
        
        $message = $newStatus === 'active' ? 'Employee activated successfully.' : 'Employee deactivated successfully.';
        
        return redirect()
            ->back()
            ->with('success', $message);
    }

    /**
     * Export employees data.
     */
    public function export(Request $request)
    {
        $query = Employee::with(['department', 'manager']);
        
        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('department') && $request->department) {
            $query->where('department_id', $request->department);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $employees = $query->get();
        
        $csvData = [];
        $csvData[] = [
            'Employee ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Position',
            'Department',
            'Manager',
            'Hire Date',
            'Status',
            'Salary'
        ];
        
        foreach ($employees as $employee) {
            $csvData[] = [
                $employee->employee_id,
                $employee->first_name,
                $employee->last_name,
                $employee->email,
                $employee->phone,
                $employee->position,
                $employee->department->name ?? '',
                $employee->manager->full_name ?? '',
                $employee->hire_date->format('Y-m-d'),
                $employee->status,
                $employee->salary
            ];
        }
        
        $filename = 'employees_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get employee data for API calls.
     */
    public function apiIndex(Request $request)
    {
        $query = Employee::with(['department', 'manager']);
        
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }
        
        $employees = $query->limit(50)->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $employee->full_name,
                'email' => $employee->email,
                'position' => $employee->position,
                'department' => $employee->department->name ?? '',
                'avatar' => $employee->avatar ? asset('storage/' . $employee->avatar) : null,
                'status' => $employee->status,
                'latest_score' => $employee->latest_performance_score
            ];
        });
        
        return response()->json($employees);
    }

    /**
     * Bulk update employees.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'action' => 'required|in:activate,deactivate,delete,update_department',
            'department_id' => 'required_if:action,update_department|exists:departments,id'
        ]);
        
        $employeeIds = $request->employee_ids;
        $action = $request->action;
        
        switch ($action) {
            case 'activate':
                Employee::whereIn('id', $employeeIds)->update(['status' => 'active']);
                $message = count($employeeIds) . ' employees activated successfully.';
                break;
                
            case 'deactivate':
                Employee::whereIn('id', $employeeIds)->update(['status' => 'inactive']);
                $message = count($employeeIds) . ' employees deactivated successfully.';
                break;
                
            case 'update_department':
                Employee::whereIn('id', $employeeIds)->update(['department_id' => $request->department_id]);
                $department = Department::find($request->department_id);
                $message = count($employeeIds) . ' employees moved to ' . $department->name . ' successfully.';
                break;
                
            case 'delete':
                // Check for constraints
                $employeesWithReviews = Employee::whereIn('id', $employeeIds)
                    ->whereHas('performanceReviews')
                    ->count();
                    
                if ($employeesWithReviews > 0) {
                    return redirect()->back()->with('error', 'Cannot delete employees with existing performance reviews.');
                }
                
                Employee::whereIn('id', $employeeIds)->delete();
                $message = count($employeeIds) . ' employees deleted successfully.';
                break;
        }
        
        return redirect()
            ->route('employees.index')
            ->with('success', $message);
    }
}