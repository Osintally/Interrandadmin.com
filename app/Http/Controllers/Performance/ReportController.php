<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of reports
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user = $request->user();
        
        // Get reports from database
        $reports = \Modules\Essentials\Entities\EssentialsPerformanceReview::with(['template', 'reviewer'])
            ->where('business_id', $business_id)
            ->where('user_id', $user->id)
            ->orderBy('review_date', 'desc')
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'title' => $review->review_period . ' Performance Review',
                    'template' => $review->template ? $review->template->name : 'Unknown Template',
                    'status' => $review->status,
                    'score' => $review->overall_score,
                    'due_date' => $review->review_date,
                    'submitted_at' => $review->submitted_at,
                ];
            })->toArray();
        
        return view('performance.reports.index', compact('reports'));
    }
    
    /**
     * Show the form for creating a new report
     */
    public function create(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Get available templates
        $templates = [
            ['id' => 1, 'name' => 'Q1 Performance Review'],
            ['id' => 2, 'name' => 'Annual Review'],
            ['id' => 3, 'name' => 'Goal Setting'],
        ];
        
        return view('performance.reports.create', compact('templates'));
    }
    
    /**
     * Store a newly created report
     */
    public function store(Request $request)
    {
        $request->validate([
            'template_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
        ]);
        
        // Create report logic here
        
        return redirect()->route('performance.reports.index')
            ->with('success', 'Report created successfully');
    }
    
    /**
     * Display the specified report
     */
    public function show(Request $request, $id)
    {
        // Load report details
        $report = [
            'id' => $id,
            'title' => 'Q1 2024 Performance Review',
            'template' => 'Quarterly Review',
            'status' => 'completed',
            'score' => 87.5,
            'due_date' => '2024-03-31',
            'submitted_at' => '2024-03-28',
        ];
        
        return view('performance.reports.show', compact('report'));
    }
    
    /**
     * Show the form for editing the specified report
     */
    public function edit(Request $request, $id)
    {
        // Load report for editing
        $report = [
            'id' => $id,
            'title' => 'Q1 2024 Performance Review',
            'template_id' => 1,
            'due_date' => '2024-03-31',
        ];
        
        return view('performance.reports.edit', compact('report'));
    }
    
    /**
     * Update the specified report
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
        ]);
        
        // Update report logic here
        
        return redirect()->route('performance.reports.index')
            ->with('success', 'Report updated successfully');
    }
    
    /**
     * Remove the specified report
     */
    public function destroy(Request $request, $id)
    {
        // Delete report logic here
        
        return redirect()->route('performance.reports.index')
            ->with('success', 'Report deleted successfully');
    }
}