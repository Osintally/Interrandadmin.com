<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Models\Employee;
use App\Models\Template;
use App\Models\Department;
use App\Http\Requests\PerformanceReviewRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    /**
     * Display a listing of performance reviews.
     */
    public function index(Request $request)
    {
        $query = PerformanceReview::with(['employee', 'reviewer', 'template']);
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }
        
        // Department filter
        if ($request->has('department') && $request->department) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Period filter
        if ($request->has('period') && $request->period) {
            $query->where('review_period', $request->period);
        }
        
        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->where('review_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->where('review_date', '<=', $request->date_to);
        }
        
        // Score filter
        if ($request->has('min_score') && $request->min_score) {
            $query->where('overall_score', '>=', $request->min_score);
        }
        
        if ($request->has('max_score') && $request->max_score) {
            $query->where('overall_score', '<=', $request->max_score);
        }
        
        // Sorting
        $sortBy = $request->get('sort', 'review_date');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['review_date', 'overall_score', 'status', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        $reviews = $query->paginate(20)->appends($request->query());
        
        // Get filter options
        $departments = Department::active()->orderBy('name')->get();
        $periods = PerformanceReview::distinct()->pluck('review_period')->sort()->values();
        $templates = Template::active()->orderBy('name')->get();
        
        // Statistics
        $stats = [
            'total' => PerformanceReview::count(),
            'pending' => PerformanceReview::where('status', 'draft')->count(),
            'completed' => PerformanceReview::where('status', 'completed')->count(),
            'approved' => PerformanceReview::where('status', 'approved')->count(),
            'average_score' => PerformanceReview::approved()->avg('overall_score') ?? 0,
        ];
        
        return view('performance.index', compact(
            'reviews', 
            'departments', 
            'periods', 
            'templates', 
            'stats'
        ));
    }

    /**
     * Show the form for creating a new performance review.
     */
    public function create(Request $request)
    {
        $employees = Employee::active()->with('department')->orderBy('first_name')->get();
        $templates = Template::active()->orderBy('name')->get();
        $reviewers = Employee::active()
            ->whereIn('position', ['Manager', 'Senior Manager', 'HR Manager', 'Department Head'])
            ->orWhere('position', 'like', '%Manager%')
            ->orWhere('position', 'like', '%Lead%')
            ->orderBy('first_name')
            ->get();
        
        // Pre-select employee if provided
        $selectedEmployee = null;
        if ($request->has('employee_id')) {
            $selectedEmployee = Employee::find($request->employee_id);
        }
        
        return view('performance.create', compact(
            'employees', 
            'templates', 
            'reviewers', 
            'selectedEmployee'
        ));
    }

    /**
     * Store a newly created performance review.
     */
    public function store(PerformanceReviewRequest $request)
    {
        $data = $request->validated();
        
        // Set review date to current date if not provided
        if (!isset($data['review_date'])) {
            $data['review_date'] = now();
        }
        
        // Generate review period if not provided
        if (!isset($data['review_period'])) {
            $data['review_period'] = now()->format('Y-Q') . 'Q' . now()->quarter;
        }
        
        // Initialize scores and responses if not provided
        if (!isset($data['scores'])) {
            $data['scores'] = [];
        }
        
        if (!isset($data['responses'])) {
            $data['responses'] = [];
        }
        
        // Set initial status
        $data['status'] = 'draft';
        
        $review = PerformanceReview::create($data);
        
        return redirect()
            ->route('performance.edit', $review)
            ->with('success', 'Performance review created successfully. Please complete the evaluation.');
    }

    /**
     * Display the specified performance review.
     */
    public function show(PerformanceReview $performance)
    {
        $performance->load([
            'employee.department',
            'reviewer',
            'template',
            'approver'
        ]);
        
        return view('performance.show', compact('performance'));
    }

    /**
     * Show the form for editing the specified performance review.
     */
    public function edit(PerformanceReview $performance)
    {
        // Check if user can edit this review
        $currentUserId = 1; // This should be auth()->id() when authentication is implemented
        
        if (!$performance->canBeEditedBy($currentUserId)) {
            return redirect()
                ->route('performance.show', $performance)
                ->with('error', 'You do not have permission to edit this review.');
        }
        
        $performance->load([
            'employee.department',
            'reviewer',
            'template'
        ]);
        
        $employees = Employee::active()->with('department')->orderBy('first_name')->get();
        $templates = Template::active()->orderBy('name')->get();
        $reviewers = Employee::active()
            ->whereIn('position', ['Manager', 'Senior Manager', 'HR Manager', 'Department Head'])
            ->orWhere('position', 'like', '%Manager%')
            ->orWhere('position', 'like', '%Lead%')
            ->orderBy('first_name')
            ->get();
        
        return view('performance.edit', compact(
            'performance', 
            'employees', 
            'templates', 
            'reviewers'
        ));
    }

    /**
     * Update the specified performance review.
     */
    public function update(PerformanceReviewRequest $request, PerformanceReview $performance)
    {
        // Check if user can edit this review
        $currentUserId = 1; // This should be auth()->id() when authentication is implemented
        
        if (!$performance->canBeEditedBy($currentUserId)) {
            return redirect()
                ->route('performance.show', $performance)
                ->with('error', 'You do not have permission to edit this review.');
        }
        
        $data = $request->validated();
        
        // Calculate overall score if scores are provided
        if (isset($data['scores']) && !empty($data['scores'])) {
            $performance->scores = $data['scores'];
            $data['overall_score'] = $performance->calculateOverallScore();
        }
        
        $performance->update($data);
        
        return redirect()
            ->route('performance.show', $performance)
            ->with('success', 'Performance review updated successfully.');
    }

    /**
     * Submit performance review for approval.
     */
    public function submit(PerformanceReview $performance)
    {
        if ($performance->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Only draft reviews can be submitted.');
        }
        
        // Validate that required fields are completed
        if (empty($performance->scores) || empty($performance->overall_score)) {
            return redirect()
                ->route('performance.edit', $performance)
                ->with('error', 'Please complete all required fields before submitting.');
        }
        
        $performance->submit();
        
        return redirect()
            ->route('performance.show', $performance)
            ->with('success', 'Performance review submitted successfully and is now awaiting approval.');
    }

    /**
     * Approve performance review.
     */
    public function approve(PerformanceReview $performance)
    {
        $currentUserId = 1; // This should be auth()->id() when authentication is implemented
        
        if (!$performance->canBeApprovedBy($currentUserId)) {
            return redirect()
                ->back()
                ->with('error', 'You do not have permission to approve this review.');
        }
        
        if ($performance->status !== 'completed') {
            return redirect()
                ->back()
                ->with('error', 'Only completed reviews can be approved.');
        }
        
        $performance->approve($currentUserId);
        
        return redirect()
            ->route('performance.show', $performance)
            ->with('success', 'Performance review approved successfully.');
    }

    /**
     * Reject performance review.
     */
    public function reject(PerformanceReview $performance, Request $request)
    {
        $currentUserId = 1; // This should be auth()->id() when authentication is implemented
        
        if (!$performance->canBeApprovedBy($currentUserId)) {
            return redirect()
                ->back()
                ->with('error', 'You do not have permission to reject this review.');
        }
        
        if ($performance->status !== 'completed') {
            return redirect()
                ->back()
                ->with('error', 'Only completed reviews can be rejected.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);
        
        $performance->reject();
        
        // Add rejection reason to comments
        $comments = $performance->comments ?? '';
        $comments .= "\n\nRejection Reason: " . $request->rejection_reason;
        $performance->update(['comments' => $comments]);
        
        return redirect()
            ->route('performance.show', $performance)
            ->with('success', 'Performance review rejected. Employee and reviewer will be notified.');
    }

    /**
     * Remove the specified performance review.
     */
    public function destroy(PerformanceReview $performance)
    {
        // Only allow deletion of draft reviews
        if ($performance->status !== 'draft') {
            return redirect()
                ->route('performance.index')
                ->with('error', 'Only draft reviews can be deleted.');
        }
        
        $performance->delete();
        
        return redirect()
            ->route('performance.index')
            ->with('success', 'Performance review deleted successfully.');
    }

    /**
     * Get performance reviews by employee.
     */
    public function byEmployee(Employee $employee)
    {
        $reviews = $employee->performanceReviews()
            ->with(['reviewer', 'template', 'approver'])
            ->orderBy('review_date', 'desc')
            ->paginate(10);
            
        return view('performance.by-employee', compact('employee', 'reviews'));
    }

    /**
     * Get performance reviews by department.
     */
    public function byDepartment(Department $department)
    {
        $reviews = PerformanceReview::with(['employee', 'reviewer', 'template'])
            ->whereHas('employee', function ($query) use ($department) {
                $query->where('department_id', $department->id);
            })
            ->orderBy('review_date', 'desc')
            ->paginate(20);
            
        return view('performance.by-department', compact('department', 'reviews'));
    }

    /**
     * Clone a performance review.
     */
    public function clone(PerformanceReview $performance)
    {
        $newReview = $performance->replicate();
        $newReview->status = 'draft';
        $newReview->overall_score = null;
        $newReview->submitted_at = null;
        $newReview->approved_at = null;
        $newReview->approved_by = null;
        $newReview->review_date = now();
        $newReview->review_period = now()->format('Y-Q') . 'Q' . now()->quarter;
        $newReview->save();
        
        return redirect()
            ->route('performance.edit', $newReview)
            ->with('success', 'Performance review cloned successfully. Please update the details as needed.');
    }

    /**
     * Export performance reviews.
     */
    public function export(Request $request)
    {
        $query = PerformanceReview::with(['employee.department', 'reviewer', 'template']);
        
        // Apply filters similar to index method
        if ($request->has('department') && $request->department) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('period') && $request->period) {
            $query->where('review_period', $request->period);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->where('review_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->where('review_date', '<=', $request->date_to);
        }
        
        $reviews = $query->get();
        
        $csvData = [];
        $csvData[] = [
            'Employee ID',
            'Employee Name',
            'Department',
            'Reviewer',
            'Review Period',
            'Review Date',
            'Template',
            'Overall Score',
            'Performance Level',
            'Status',
            'Strengths',
            'Areas for Improvement',
            'Goals',
            'Comments'
        ];
        
        foreach ($reviews as $review) {
            $csvData[] = [
                $review->employee->employee_id,
                $review->employee->full_name,
                $review->employee->department->name ?? '',
                $review->reviewer->full_name ?? '',
                $review->review_period,
                $review->review_date->format('Y-m-d'),
                $review->template->name ?? '',
                $review->overall_score,
                $review->getPerformanceLevel(),
                ucfirst($review->status),
                $review->strengths,
                $review->areas_for_improvement,
                $review->goals,
                $review->comments
            ];
        }
        
        $filename = 'performance_reviews_export_' . date('Y-m-d_H-i-s') . '.csv';
        
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
     * Get performance statistics.
     */
    public function getStats()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth();
        
        $stats = [
            'total_reviews' => PerformanceReview::count(),
            'this_month' => PerformanceReview::where('created_at', '>=', $currentMonth)->count(),
            'pending_approval' => PerformanceReview::where('status', 'completed')->count(),
            'average_score' => round(PerformanceReview::approved()->avg('overall_score') ?? 0, 1),
            'high_performers' => PerformanceReview::approved()->where('overall_score', '>=', 90)->count(),
            'needs_improvement' => PerformanceReview::approved()->where('overall_score', '<', 70)->count(),
        ];
        
        return response()->json($stats);
    }

    /**
     * Bulk operations on performance reviews.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:performance_reviews,id',
            'action' => 'required|in:approve,reject,delete'
        ]);
        
        $reviewIds = $request->review_ids;
        $action = $request->action;
        $currentUserId = 1; // This should be auth()->id() when authentication is implemented
        
        $processed = 0;
        $errors = [];
        
        foreach ($reviewIds as $reviewId) {
            $review = PerformanceReview::find($reviewId);
            
            try {
                switch ($action) {
                    case 'approve':
                        if ($review->canBeApprovedBy($currentUserId) && $review->status === 'completed') {
                            $review->approve($currentUserId);
                            $processed++;
                        } else {
                            $errors[] = "Cannot approve review for {$review->employee->full_name}";
                        }
                        break;
                        
                    case 'reject':
                        if ($review->canBeApprovedBy($currentUserId) && $review->status === 'completed') {
                            $review->reject();
                            $processed++;
                        } else {
                            $errors[] = "Cannot reject review for {$review->employee->full_name}";
                        }
                        break;
                        
                    case 'delete':
                        if ($review->status === 'draft') {
                            $review->delete();
                            $processed++;
                        } else {
                            $errors[] = "Cannot delete non-draft review for {$review->employee->full_name}";
                        }
                        break;
                }
            } catch (\Exception $e) {
                $errors[] = "Error processing review for {$review->employee->full_name}: " . $e->getMessage();
            }
        }
        
        $message = "$processed reviews processed successfully.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }
        
        return redirect()
            ->route('performance.index')
            ->with($processed > 0 ? 'success' : 'error', $message);
    }
}