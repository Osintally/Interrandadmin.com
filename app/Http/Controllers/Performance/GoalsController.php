<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GoalsController extends Controller
{
    /**
     * Display a listing of goals
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user = $request->user();
        
        // Get goals from database
        $goals = \App\Models\Performance\PerformanceGoal::where('employee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'title' => $goal->title,
                    'description' => $goal->description,
                    'target_value' => $goal->target_value,
                    'current_value' => $goal->current_value,
                    'unit' => $goal->unit,
                    'status' => $goal->status,
                    'due_date' => $goal->due_date,
                    'progress' => $goal->progress_percentage
                ];
            })->toArray();
        
        return view('performance.goals.index', compact('goals'));
    }
    
    /**
     * Show the form for creating a new goal
     */
    public function create(Request $request)
    {
        return view('performance.goals.create');
    }
    
    /**
     * Store a newly created goal
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_value' => 'required|numeric',
            'unit' => 'required|string|max:20',
            'due_date' => 'required|date',
        ]);
        
        // Create goal logic here
        
        return redirect()->route('performance.goals.index')
            ->with('success', 'Goal created successfully');
    }
    
    /**
     * Display the specified goal
     */
    public function show(Request $request, $id)
    {
        // Load goal details
        $goal = [
            'id' => $id,
            'title' => 'Increase Sales Performance',
            'description' => 'Achieve 15% increase in quarterly sales',
            'target_value' => 15.0,
            'current_value' => 12.3,
            'unit' => '%',
            'status' => 'in_progress',
            'due_date' => '2024-06-30',
            'progress' => 82
        ];
        
        return view('performance.goals.show', compact('goal'));
    }
    
    /**
     * Update goal progress
     */
    public function updateScore(Request $request, $id)
    {
        $request->validate([
            'progress' => 'required|numeric|min:0|max:100',
        ]);
        
        // Update goal progress logic here
        
        return redirect()->route('performance.goals.index')
            ->with('success', 'Goal progress updated successfully');
    }
    
    /**
     * Remove the specified goal
     */
    public function destroy(Request $request, $id)
    {
        // Delete goal logic here
        
        return redirect()->route('performance.goals.index')
            ->with('success', 'Goal deleted successfully');
    }
}