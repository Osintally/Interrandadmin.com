<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\User;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    /**
     * Display the attendance management dashboard with enhanced modern UI.
     */
    public function index()
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        // Get attendance settings
        $settings = AttendanceSetting::getOrCreateForBusiness($businessId);
        
        // Get recent attendance data
        $recentAttendance = AttendanceLog::with('user')
            ->forBusiness($businessId)
            ->orderBy('attendance_date', 'desc')
            ->limit(10)
            ->get();

        // Get dashboard statistics
        $stats = $this->getDashboardStats($businessId);

        // Return the enhanced view with modern UI
        return view('hrm.attendance', compact('settings', 'recentAttendance', 'stats'));
    }

    /**
     * Get dashboard statistics.
     */
    public function getDashboardStats($businessId)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now();

        $stats = AttendanceLog::getBusinessStats($businessId, $startDate, $endDate);
        
        // Get most punctual employee
        $mostPunctual = AttendanceLog::getMostPunctualEmployee($businessId, $startDate, $endDate);
        
        // Get inactive users (no attendance records this month)
        $activeUsers = AttendanceLog::where('business_id', $businessId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->distinct('user_id')
            ->pluck('user_id');
            
        $totalUsers = User::where('business_id', $businessId)->count();
        $inactiveUsers = $totalUsers - $activeUsers->count();

        return [
            'total_employees' => $stats->total_employees ?? 0,
            'total_present' => $stats->total_present ?? 0,
            'total_absent' => $stats->total_absent ?? 0,
            'total_late' => $stats->total_late ?? 0,
            'avg_hours' => $stats->avg_hours_worked ?? 0,
            'most_punctual' => $mostPunctual ? $mostPunctual->user->user_full_name : '-',
            'inactive_users' => $inactiveUsers,
            'attendance_rate' => $totalUsers > 0 ? round(($stats->total_present ?? 0) / ($totalUsers * $startDate->diffInDaysFiltered(function (Carbon $date) {
                return $date->isWeekday();
            }, $endDate)) * 100, 1) : 0
        ];
    }

    /**
     * Process uploaded attendance files using proven frontend parsing.
     */
    public function processFiles(Request $request)
    {
        // Debug: Log the incoming request data
        \Log::info('Processing files request', [
            'files' => [
                'user_file' => $request->hasFile('user_file') ? 'present' : 'missing',
                'attendance_files' => $request->hasFile('attendance_files') ? count($request->file('attendance_files')) : 0
            ],
            'form_data' => $request->only(['start_time', 'end_time', 'grace_minutes', 'working_days'])
        ]);

        $request->validate([
            'user_file' => 'required|file|max:10240',
            'attendance_files' => 'required|array', 
            'attendance_files.*' => 'file|max:10240',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i', 
            'grace_minutes' => 'required|integer|min:0|max:60',
            'working_days' => 'nullable|integer|min:1|max:31'
        ], [
            'user_file.required' => 'User file is required',
            'user_file.file' => 'User file must be a valid file',
            'attendance_files.required' => 'At least one attendance file is required',
            'attendance_files.array' => 'Attendance files must be an array',
            'attendance_files.*.file' => 'Each attendance file must be a valid file',
            'start_time.required' => 'Start time is required',
            'start_time.date_format' => 'Start time must be in HH:MM format',
            'end_time.required' => 'End time is required',
            'end_time.date_format' => 'End time must be in HH:MM format',
            'grace_minutes.required' => 'Grace minutes is required',
            'grace_minutes.integer' => 'Grace minutes must be a number',
            'grace_minutes.min' => 'Grace minutes must be at least 0',
            'grace_minutes.max' => 'Grace minutes cannot exceed 60',
            'working_days.integer' => 'Working days must be a number',
            'working_days.min' => 'Working days must be at least 1',
            'working_days.max' => 'Working days cannot exceed 31'
        ]);

        // Set default working_days if not provided
        $workingDays = $request->input('working_days', 21);

        // Enhanced file validation with proper error handling
        foreach (array_merge([$request->file('user_file')], $request->file('attendance_files')) as $file) {
            if (!$this->isValidAttendanceFile($file, ['dat', 'txt'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'All files must be readable .dat or .txt files.',
                    'errors' => ['files' => ['Invalid file format detected.']]
                ], 422);
            }
        }

        try {
            // Read and validate file contents to ensure they're not HTML
            $userFileContent = file_get_contents($request->file('user_file')->getRealPath());
            $attendanceFilesContent = [];
            
            foreach ($request->file('attendance_files') as $file) {
                $content = file_get_contents($file->getRealPath());
                
                // Check if content looks like HTML (common error case)
                if (strpos($content, '<!DOCTYPE') !== false || strpos($content, '<html') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'File contains HTML content instead of attendance data. Please check your file format.',
                        'errors' => ['files' => ['File appears to be HTML instead of .dat format.']]
                    ], 422);
                }
                
                $attendanceFilesContent[] = $content;
            }
            
            // Validate user file content
            if (strpos($userFileContent, '<!DOCTYPE') !== false || strpos($userFileContent, '<html') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'User file contains HTML content instead of user data. Please check your file format.',
                    'errors' => ['files' => ['User file appears to be HTML instead of .dat format.']]
                ], 422);
            }
            
            // Return success with file validation confirmation
            return response()->json([
                'success' => true,
                'message' => 'Files validated successfully. Ready for processing.',
                'action' => 'process_frontend',
                'file_info' => [
                    'user_file_size' => strlen($userFileContent),
                    'attendance_files_count' => count($attendanceFilesContent),
                    'total_attendance_size' => array_sum(array_map('strlen', $attendanceFilesContent))
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error processing attendance files', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse user mapping file.
     */
    private function parseUserFile($content)
    {
        // Clean content and extract user ID - name pairs
        $cleaned = preg_replace('/[\x00-\x1F]/', ' ', $content);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $tokens = explode(' ', trim($cleaned));

        $names = [];
        $ids = [];
        
        foreach ($tokens as $token) {
            if (preg_match('/^[A-Za-z]+$/', $token)) {
                $names[] = $token;
            } elseif (preg_match('/^[1-9]\d*$/', $token)) { // Only accept positive integers (no 0)
                $ids[] = intval($token);
            }
        }

        $users = [];
        $count = min(count($names), count($ids));
        
        for ($i = 0; $i < $count; $i++) {
            // Additional validation: ensure ID is positive and valid
            $userId = $ids[$i];
            $userName = $names[$i];
            
            if ($userId > 0 && !empty($userName)) {
                $users[$userId] = $userName;
            }
        }

        return $users;
    }

    /**
     * Parse attendance log file.
     */
    private function parseAttendanceFile($content)
    {
        $lines = explode("\n", $content);
        $records = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Handle concatenated timestamps
            $entries = $this->splitConcatenatedEntries($line);
            
            foreach ($entries as $entry) {
                $record = $this->parseAttendanceEntry($entry);
                if ($record) {
                    $records[] = $record;
                }
            }
        }

        return $records;
    }

    /**
     * Split concatenated timestamp entries.
     */
    private function splitConcatenatedEntries($line)
    {
        // Look for multiple date patterns
        if (preg_match_all('/(\d+)\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/', $line, $matches, PREG_SET_ORDER)) {
            return array_map(function($match) {
                return $match[1] . ' ' . $match[2];
            }, $matches);
        }

        return [$line];
    }

    /**
     * Parse individual attendance entry.
     */
    private function parseAttendanceEntry($entry)
    {
        $parts = explode(' ', $entry, 3);
        
        if (count($parts) < 2) {
            return null;
        }

        $userId = trim($parts[0]);
        // Validate user ID: must be a positive integer (no 0, no empty)
        if (!preg_match('/^[1-9]\d*$/', $userId)) {
            return null;
        }
        
        $userId = intval($userId);
        if ($userId <= 0) {
            return null;
        }

        // Extract timestamp
        if (preg_match('/(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})/', $entry, $matches)) {
            $date = $matches[1];
            $time = $matches[2];
            
            return [
                'user_id' => $userId,
                'timestamp' => Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time)
            ];
        }

        return null;
    }

    /**
     * Process attendance data and create records.
     */
    private function processAttendanceData($businessId, $users, $attendanceData, $startTime, $endTime, $graceMinutes, $workingDays)
    {
        // Group attendance by user and date
        $grouped = [];
        foreach ($attendanceData as $record) {
            $userId = intval($record['user_id']);
            
            // Skip invalid user IDs
            if ($userId <= 0) {
                continue;
            }
            
            $date = $record['timestamp']->format('Y-m-d');
            $key = $userId . '|' . $date;
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'user_id' => $userId,
                    'date' => $date,
                    'clock_in' => $record['timestamp'],
                    'clock_out' => $record['timestamp']
                ];
            } else {
                if ($record['timestamp']->lt($grouped[$key]['clock_in'])) {
                    $grouped[$key]['clock_in'] = $record['timestamp'];
                }
                if ($record['timestamp']->gt($grouped[$key]['clock_out'])) {
                    $grouped[$key]['clock_out'] = $record['timestamp'];
                }
            }
        }

        // Generate business calendar
        $minDate = min(array_map(function($record) {
            return $record['timestamp'];
        }, $attendanceData));
        
        $calendar = $this->generateBusinessCalendar($minDate, $workingDays);

        $detail = [];
        $summary = [];

        foreach ($users as $userId => $username) {
            // Ensure user ID is valid
            $userId = intval($userId);
            if ($userId <= 0) {
                continue;
            }
            
            $userSummary = [
                'user_id' => $userId,
                'username' => $username,
                'total_present' => 0,
                'total_absent' => 0,
                'total_late' => 0,
                'total_low_late' => 0,
                'total_early_leave' => 0
            ];

            foreach ($calendar as $date) {
                $dateStr = $date->format('Y-m-d');
                $key = $userId . '|' . $dateStr;
                
                if (isset($grouped[$key])) {
                    $record = $grouped[$key];
                    
                    // Calculate attendance details
                    $attendanceLog = new AttendanceLog([
                        'business_id' => $businessId,
                        'user_id' => $userId,
                        'attendance_date' => $date,
                        'clock_in' => $record['clock_in'],
                        'clock_out' => $record['clock_out'],
                        'status' => 'present'
                    ]);

                    // Calculate late status
                    $lateInfo = $attendanceLog->determineLateStatus($startTime, $graceMinutes);
                    $attendanceLog->late_status = $lateInfo['late_status'];
                    $attendanceLog->late_category = $lateInfo['late_category'];
                    $attendanceLog->late_minutes = $attendanceLog->calculateLateMinutes($startTime, $graceMinutes);
                    
                    // Calculate total hours and early leave
                    $attendanceLog->total_hours = $attendanceLog->calculateTotalHours();
                    $attendanceLog->early_leave = $attendanceLog->checkEarlyLeave($endTime);

                    $attendanceLog->save();

                    $detail[] = [
                        'user_id' => $userId,
                        'username' => $username,
                        'date' => $dateStr,
                        'clock_in' => $record['clock_in']->format('Y-m-d H:i:s'),
                        'clock_out' => $record['clock_out']->format('Y-m-d H:i:s'),
                        'late_status' => $lateInfo['late_status'],
                        'late_category' => $lateInfo['late_category'],
                        'early_leave' => $attendanceLog->early_leave,
                        'total_hours' => $attendanceLog->total_hours,
                        'status' => 'present'
                    ];

                    $userSummary['total_present']++;
                    if ($lateInfo['late_status'] === 'late') $userSummary['total_late']++;
                    if ($lateInfo['late_status'] === 'low_late') $userSummary['total_low_late']++;
                    if ($attendanceLog->early_leave) $userSummary['total_early_leave']++;
                    
                } else {
                    // Absent record
                    AttendanceLog::create([
                        'business_id' => $businessId,
                        'user_id' => $userId,
                        'attendance_date' => $date,
                        'status' => 'absent'
                    ]);

                    $detail[] = [
                        'user_id' => $userId,
                        'username' => $username,
                        'date' => $dateStr,
                        'clock_in' => '',
                        'clock_out' => '',
                        'late_status' => '',
                        'late_category' => '',
                        'early_leave' => false,
                        'total_hours' => 0,
                        'status' => 'absent'
                    ];

                    $userSummary['total_absent']++;
                }
            }

            $summary[] = $userSummary;
        }

        return compact('detail', 'summary');
    }

    /**
     * Generate business calendar.
     */
    private function generateBusinessCalendar($startDate, $workingDaysCount)
    {
        $calendar = [];
        $date = Carbon::parse($startDate)->startOfDay();
        $workingDays = [1, 2, 3, 4, 5]; // Mon-Fri by default

        while (count($calendar) < $workingDaysCount) {
            if (in_array($date->dayOfWeek, $workingDays)) {
                $calendar[] = $date->copy();
            }
            $date->addDay();
        }

        return $calendar;
    }

    /**
     * Validate user existence and create missing users.
     */
    private function validateAndCreateUsers($businessId, $users)
    {
        $existingUsers = [];
        $createdUsers = [];
        
        // Filter out invalid user IDs (0 or negative)
        $validUsers = array_filter($users, function($name, $userId) {
            return is_numeric($userId) && intval($userId) > 0 && !empty(trim($name));
        }, ARRAY_FILTER_USE_BOTH);
        
        if (empty($validUsers)) {
            return [
                'existing' => [],
                'created' => [],
                'invalid_users_filtered' => count($users) - count($validUsers)
            ];
        }
        
        $userIds = array_keys($validUsers);
        $userIds = array_map('intval', $userIds); // Ensure all IDs are integers
        
        // Get existing users
        $existingUserRecords = User::where('business_id', $businessId)
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->toArray();
        
        // Identify missing users
        $missingUserIds = array_diff($userIds, $existingUserRecords);
        
        // Create missing users
        foreach ($missingUserIds as $userId) {
            $username = $validUsers[$userId] ?? 'User' . $userId;
            
            try {
                // Create user with specific ID by disabling auto-increment temporarily
                $user = new User([
                    'user_type' => 'user',
                    'surname' => '',
                    'first_name' => $username,
                    'last_name' => '',
                    'username' => strtolower(str_replace(' ', '_', $username) . '_' . $userId),
                    'email' => strtolower(str_replace(' ', '_', $username) . '_' . $userId . '@attendance.local'),
                    'password' => bcrypt('attendance123'), // Default password
                    'business_id' => $businessId,
                    'language' => 'en',
                    'allow_login' => 0, // Don't allow login for attendance-only users
                    'status' => 'active'
                ]);
                $user->id = $userId;
                $user->save();
                
                $createdUsers[] = [
                    'id' => $userId,
                    'name' => $username,
                    'username' => $user->username,
                    'email' => $user->email
                ];
                
            } catch (\Exception $e) {
                // If creation fails, we'll still try to process attendance
                // but record the error
                \Log::warning('Failed to create user for attendance', [
                    'user_id' => $userId,
                    'username' => $username,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Track existing users
        foreach ($existingUserRecords as $userId) {
            $existingUsers[] = [
                'id' => $userId,
                'name' => $validUsers[$userId] ?? 'Unknown'
            ];
        }
        
        return [
            'existing' => $existingUsers,
            'created' => $createdUsers
        ];
    }

    /**
     * Validate if file is a valid attendance file.
     */
    private function isValidAttendanceFile($file, $allowedExtensions = ['dat', 'txt'])
    {
        if (!$file || !$file->isValid()) {
            return false;
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }

        // Try to read the file as text to ensure it's readable
        try {
            $content = file_get_contents($file->getRealPath());
            // Check if file has some content and is not purely binary
            if (empty($content) || strlen($content) > 50 * 1024 * 1024) { // Max 50MB
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Save processed attendance data from frontend (simple approach).
     */
    public function saveProcessedDataSimple(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'summary' => 'required|array', 
            'detail' => 'required|array',
            'settings' => 'required|array'
        ]);

        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        try {
            DB::beginTransaction();
            
            // Create a mapping of original user IDs to actual Laravel user IDs
            $userMapping = [];
            $createdUsers = [];
            
            // First, create all needed users
            foreach ($request->users as $userData) {
                $originalUserId = $userData['UserId'];
                $username = $userData['Username'];
                
                // Check if user already exists by looking for attendance records or username pattern
                $existingUser = User::where('business_id', $businessId)
                    ->where(function($query) use ($originalUserId, $username) {
                        $query->where('username', 'like', '%' . strtolower(str_replace(' ', '', $username)) . '%')
                              ->orWhere('first_name', $username);
                    })
                    ->first();
                
                if ($existingUser) {
                    $userMapping[$originalUserId] = $existingUser->id;
                } else {
                    // Create unique username
                    $baseUsername = strtolower(str_replace(' ', '', $username)) . '_att';
                    $username_final = $baseUsername;
                    $counter = 1;
                    
                    while (User::where('username', $username_final)->exists()) {
                        $username_final = $baseUsername . '_' . $counter;
                        $counter++;
                    }
                    
                    // Create unique email
                    $email = $username_final . '@attendance.local';
                    $emailCounter = 1;
                    while (User::where('email', $email)->exists()) {
                        $email = $baseUsername . '_' . $emailCounter . '@attendance.local';
                        $emailCounter++;
                    }
                    
                    $newUser = User::create([
                        'business_id' => $businessId,
                        'user_type' => 'user',
                        'first_name' => $username,
                        'last_name' => '',
                        'surname' => '',
                        'username' => $username_final,
                        'email' => $email,
                        'password' => bcrypt('attendance123'),
                        'allow_login' => 0,
                        'status' => 'active'
                    ]);
                    
                    $userMapping[$originalUserId] = $newUser->id;
                    $createdUsers[] = [
                        'original_id' => $originalUserId,
                        'actual_id' => $newUser->id,
                        'username' => $username_final,
                        'name' => $username
                    ];
                }
            }
            
            // Now create attendance records using the mapped user IDs
            $saved = 0;
            foreach ($request->detail as $record) {
                $originalUserId = $record['UserId'];
                
                if (!isset($userMapping[$originalUserId])) {
                    continue; // Skip if no user mapping found
                }
                
                $actualUserId = $userMapping[$originalUserId];
                
                AttendanceLog::create([
                    'business_id' => $businessId,
                    'user_id' => $actualUserId,
                    'attendance_date' => $record['Date'],
                    'clock_in' => !empty($record['Clockin']) ? $record['Clockin'] : null,
                    'clock_out' => !empty($record['Clockout']) ? $record['Clockout'] : null,
                    'total_hours' => floatval($record['TotalHours'] ?? 0),
                    'status' => strtolower($record['Status']) === 'present' ? 'present' : 'absent',
                    'late_status' => $this->mapLateStatus($record['Late'] ?? ''),
                    'late_minutes' => $this->calculateLateMinutes($record['Clockin'] ?? '', $request->settings['startTime'] ?? '08:00'),
                    'early_leave' => ($record['EarlyLeave'] ?? '') === 'Yes'
                ]);
                
                $saved++;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance data saved successfully',
                'records_saved' => $saved,
                'users_created' => count($createdUsers),
                'created_users_details' => $createdUsers,
                'user_mapping' => $userMapping
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error saving attendance data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error saving data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Save processed attendance data from frontend.
     */
    public function saveProcessedData(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'summary' => 'required|array', 
            'detail' => 'required|array',
            'settings' => 'required|array'
        ]);

        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        try {
            DB::beginTransaction();
            
            // Clear existing attendance data for this business (optional)
            // AttendanceLog::where('business_id', $businessId)->delete();
            
            // Process and save each detail record
            $saved = 0;
            $created_users = [];
            
            \Log::info('Starting to save attendance records', [
                'total_records' => count($request->detail),
                'business_id' => $businessId
            ]);
            
            foreach ($request->detail as $record) {
                if (empty($record['UserId']) || $record['UserId'] <= 0) continue;
                
                $userId = intval($record['UserId']);
                
                // Check if user exists, create if not
                $user = User::where('id', $userId)
                    ->where('business_id', $businessId)
                    ->first();
                
                if (!$user) {
                    // Create new user with unique username handling
                    $baseUsername = strtolower(str_replace(' ', '', ($record['Username'] ?? 'user')) . '_' . $userId);
                    $username = $baseUsername;
                    $counter = 1;
                    
                    // Ensure username is unique
                    while (User::where('username', $username)->exists()) {
                        $username = $baseUsername . '_' . $counter;
                        $counter++;
                    }
                    
                    $baseEmail = strtolower(str_replace(' ', '', ($record['Username'] ?? 'user')) . '_' . $userId);
                    $email = $baseEmail . '@attendance.local';
                    $emailCounter = 1;
                    
                    // Ensure email is unique
                    while (User::where('email', $email)->exists()) {
                        $email = $baseEmail . '_' . $emailCounter . '@attendance.local';
                        $emailCounter++;
                    }
                    
                    try {
                        $user = User::create([
                            'id' => $userId,
                            'business_id' => $businessId,
                            'user_type' => 'user',
                            'first_name' => $record['Username'] ?? 'User' . $userId,
                            'last_name' => '',
                            'surname' => '',
                            'username' => $username,
                            'email' => $email,
                            'password' => bcrypt('attendance123'),
                            'allow_login' => 0,
                            'status' => 'active'
                        ]);
                    } catch (\Exception $e) {
                        // If ID-specific creation fails, create without specific ID
                        $user = User::create([
                            'business_id' => $businessId,
                            'user_type' => 'user',
                            'first_name' => $record['Username'] ?? 'User' . $userId,
                            'last_name' => '',
                            'surname' => '',
                            'username' => $username,
                            'email' => $email,
                            'password' => bcrypt('attendance123'),
                            'allow_login' => 0,
                            'status' => 'active'
                        ]);
                        
                        // Update the record to use the auto-generated ID
                        $userId = $user->id;
                    }
                    
                    $created_users[] = [
                        'original_id' => $record['UserId'],
                        'actual_id' => $user->id,
                        'username' => $user->username,
                        'name' => $record['Username']
                    ];
                    
                    \Log::info('Created new user for attendance', [
                        'original_user_id' => $record['UserId'],
                        'actual_user_id' => $user->id,
                        'username' => $user->username
                    ]);
                }
                
                // Create attendance log (use the actual user ID from the user object)
                AttendanceLog::create([
                    'business_id' => $businessId,
                    'user_id' => $user->id,
                    'attendance_date' => $record['Date'],
                    'clock_in' => !empty($record['Clockin']) ? $record['Clockin'] : null,
                    'clock_out' => !empty($record['Clockout']) ? $record['Clockout'] : null,
                    'total_hours' => floatval($record['TotalHours'] ?? 0),
                    'status' => strtolower($record['Status']) === 'present' ? 'present' : 'absent',
                    'late_status' => $this->mapLateStatus($record['Late'] ?? ''),
                    'late_minutes' => $this->calculateLateMinutes($record['Clockin'] ?? '', $request->settings['startTime'] ?? '08:00'),
                    'early_leave' => ($record['EarlyLeave'] ?? '') === 'Yes'
                ]);
                
                $saved++;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance data saved successfully',
                'records_saved' => $saved,
                'users_created' => count($created_users),
                'created_users_details' => $created_users
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error saving data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function mapLateStatus($lateValue)
    {
        switch (strtolower($lateValue)) {
            case 'late': return 'late';
            case 'low_late': return 'low_late';
            default: return 'no';
        }
    }
    
    private function calculateLateMinutes($clockin, $startTime)
    {
        if (empty($clockin) || empty($startTime)) return 0;
        
        try {
            $clockinTime = new \DateTime($clockin);
            $startDateTime = new \DateTime(date('Y-m-d') . ' ' . $startTime);
            
            if ($clockinTime > $startDateTime) {
                return ($clockinTime->getTimestamp() - $startDateTime->getTimestamp()) / 60;
            }
        } catch (\Exception $e) {
            // Ignore parsing errors
        }
        
        return 0;
    }

    /**
     * Get attendance summary data.
     */
    public function getSummary(Request $request)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $summary = AttendanceLog::with('user')
            ->select([
                'user_id',
                DB::raw('COUNT(*) as total_days'),
                DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_days'),
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_days'),
                DB::raw('SUM(CASE WHEN late_status = "late" THEN 1 ELSE 0 END) as late_days'),
                DB::raw('SUM(CASE WHEN late_status = "low_late" THEN 1 ELSE 0 END) as low_late_days'),
                DB::raw('SUM(CASE WHEN early_leave = 1 THEN 1 ELSE 0 END) as early_leave_days')
            ])
            ->where('business_id', $businessId)
            ->groupBy('user_id')
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->user_id,
                    'username' => $item->user->user_full_name ?? 'Unknown User',
                    'total_present' => $item->present_days,
                    'total_absent' => $item->absent_days,
                    'total_late' => $item->late_days,
                    'total_low_late' => $item->low_late_days,
                    'total_early_leave' => $item->early_leave_days
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Get detailed attendance for a specific user.
     */
    public function getUserDetails(Request $request, $userId)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $details = AttendanceLog::with('user')
            ->where('business_id', $businessId)
            ->where('user_id', $userId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'date' => $log->attendance_date->format('Y-m-d'),
                    'clock_in' => $log->clock_in ? $log->clock_in->format('Y-m-d H:i:s') : '',
                    'clock_out' => $log->clock_out ? $log->clock_out->format('Y-m-d H:i:s') : '',
                    'total_hours' => $log->total_hours ?? 0,
                    'status' => $log->status,
                    'late_status' => $log->late_status,
                    'late_category' => $log->late_category,
                    'early_leave' => $log->early_leave
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $details
        ]);
    }

    /**
     * Get attendance settings.
     */
    public function getSettings()
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        $settings = AttendanceSetting::getOrCreateForBusiness($businessId);

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Update attendance settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_minutes' => 'required|integer|min:0|max:60',
            'late_threshold_minutes' => 'required|integer|min:0|max:60',
            'working_days' => 'required|array',
            'working_days.*' => 'integer|min:0|max:6',
            'working_days_per_month' => 'required|integer|min:1|max:31',
            'absent_deduction_percent' => 'required|numeric|min:0|max:100',
            'late_deduction_percent' => 'required|numeric|min:0|max:100',
            'low_late_deduction_percent' => 'required|numeric|min:0|max:100'
        ]);

        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $settings = AttendanceSetting::updateSettings($businessId, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $settings
        ]);
    }

    /**
     * Clear all attendance data.
     */
    public function clearData()
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        try {
            AttendanceLog::where('business_id', $businessId)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'All attendance data cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update attendance records from the modal
     */
    public function updateAttendance(Request $request)
    {
        try {
            $userId = $request->input('userId');
            $attendanceData = $request->input('attendanceData');
            
            if (!$userId || !$attendanceData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data provided'
                ], 400);
            }

            $businessId = session('user.business_id') ?? auth()->user()->business_id;
            
            DB::beginTransaction();
            
            foreach ($attendanceData as $record) {
                // Handle different date formats
                try {
                    $date = Carbon::createFromFormat('d/m/Y', $record['date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $date = Carbon::parse($record['date'])->format('Y-m-d');
                    } catch (\Exception $e2) {
                        \Log::error('Invalid date format in attendance update', [
                            'date' => $record['date'],
                            'error' => $e2->getMessage()
                        ]);
                        continue; // Skip this record
                    }
                }
                
                // Handle time format properly
                $clockIn = null;
                $clockOut = null;
                
                if (!empty($record['clockIn']) && $record['clockIn'] !== 'N/A') {
                    try {
                        $clockIn = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $record['clockIn']);
                    } catch (\Exception $e) {
                        \Log::error('Invalid clock-in time format', [
                            'time' => $record['clockIn'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                if (!empty($record['clockOut']) && $record['clockOut'] !== 'N/A') {
                    try {
                        $clockOut = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $record['clockOut']);
                    } catch (\Exception $e) {
                        \Log::error('Invalid clock-out time format', [
                            'time' => $record['clockOut'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Update or create attendance record
                AttendanceLog::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'attendance_date' => $date,
                        'business_id' => $businessId
                    ],
                    [
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'total_hours' => floatval($record['totalHours']),
                        'status' => strtolower($record['status']),
                        'late_status' => $record['late'],
                        'updated_at' => now()
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating attendance: ' . $e->getMessage()
            ], 500);
        }
    }
}