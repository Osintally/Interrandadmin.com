<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index()
    {
        $departments = Department::withCount(['activeEmployees as employee_count'])
            ->withAvg([
                'performanceReviews as avg_score' => function ($query) {
                    $query->where('status', 'approved');
                }
            ], 'overall_score')
            ->orderBy('name')
            ->get();

        $stats = [
            'total_departments' => Department::count(),
            'active_departments' => Department::active()->count(),
            'total_employees' => Employee::active()->count(),
            'avg_employees_per_dept' => round(Employee::active()->count() / max(Department::active()->count(), 1), 1)
        ];

        return view('departments.index', compact('departments', 'stats'));
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:10|unique:departments,code',
            'description' => 'nullable|string|max:1000',
            'manager_id' => 'nullable|exists:employees,id'
        ]);

        $department = Department::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'department' => $department->load('manager')
        ]);
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        $department->load([
            'manager',
            'employees' => function($query) {
                $query->active()->with('performanceReviews');
            }
        ]);

        $stats = [
            'total_employees' => $department->activeEmployees()->count(),
            'avg_performance' => $department->average_performance,
            'latest_reviews' => $department->performanceReviews()
                ->approved()
                ->where('review_date', '>=', now()->subMonths(3))
                ->count(),
            'top_performer' => $department->getTopPerformers(1)->first()
        ];

        return view('departments.show', compact('department', 'stats'));
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->ignore($department->id)
            ],
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('departments', 'code')->ignore($department->id)
            ],
            'description' => 'nullable|string|max:1000',
            'manager_id' => 'nullable|exists:employees,id',
            'is_active' => 'boolean'
        ]);

        $department->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
            'department' => $department->load('manager')
        ]);
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department with existing employees. Please reassign employees first.'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully'
        ]);
    }
}