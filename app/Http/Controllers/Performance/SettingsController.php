<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display performance system settings
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Sample settings - replace with actual settings from database
        $settings = [
            'performance' => [
                'review_frequency' => 'quarterly',
                'auto_notifications' => true,
                'require_manager_approval' => true,
                'allow_self_review' => true,
            ],
            'scoring' => [
                'scale_type' => '1-10',
                'weighted_scoring' => true,
                'auto_calculate' => true,
            ],
            'notifications' => [
                'email_reminders' => true,
                'deadline_alerts' => true,
                'performance_updates' => true,
            ]
        ];
        
        return view('performance.settings.index', compact('settings'));
    }
    
    /**
     * Update settings
     */
    public function update(Request $request)
    {
        // Validate and update settings
        $request->validate([
            'review_frequency' => 'required|in:monthly,quarterly,annual',
            'auto_notifications' => 'boolean',
            'require_manager_approval' => 'boolean',
        ]);
        
        // Update settings logic here
        
        return redirect()->route('performance.settings.index')
            ->with('success', 'Settings updated successfully');
    }
}
