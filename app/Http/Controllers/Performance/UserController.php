<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display user management for performance system
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Get users with their performance data
        $users = \App\User::where('business_id', $business_id)
            ->with(['roles'])
            ->paginate(20);
        
        return view('performance.users.index', compact('users'));
    }
}
