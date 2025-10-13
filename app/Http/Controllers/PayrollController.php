<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\PayrollRecord;
use App\Models\SalarySetting;
use App\Models\AttendanceSetting;
use App\Models\AttendanceLog;
use App\User;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\PayrollExport;

class PayrollController extends Controller
{
    /**
     * Display the payroll management dashboard.
     */
    public function index()
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        // Get recent payroll records
        $recentPayroll = PayrollRecord::with('user')
            ->forBusiness($businessId)
            ->latest()
            ->limit(10)
            ->get();

        // Get payroll statistics
        $stats = $this->getPayrollStats($businessId);

        return view('payroll.index', compact('recentPayroll', 'stats'));
    }

    /**
     * Get payroll statistics.
     */
    public function getPayrollStats($businessId)
    {
        $currentMonth = Carbon::now()->format('Y-m');
        
        $currentMonthStats = PayrollRecord::getBusinessSummary($businessId, $currentMonth);
        
        return [
            'total_employees' => $currentMonthStats->total_employees ?? 0,
            'total_gross_pay' => $currentMonthStats->total_gross_pay ?? 0,
            'total_net_pay' => $currentMonthStats->total_net_pay ?? 0,
            'total_deductions' => $currentMonthStats->total_deductions ?? 0,
            'avg_attendance_rate' => $currentMonthStats->avg_attendance_rate ?? 0
        ];
    }

    /**
     * Get payroll records.
     */
    public function getRecords(Request $request)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        $period = $request->get('period', Carbon::now()->format('Y-m'));

        $records = PayrollRecord::with(['user', 'generator', 'approver'])
            ->forBusiness($businessId)
            ->when($period, function ($query) use ($period) {
                $query->forPeriod($period);
            })
            ->latest()
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'user_id' => $record->user_id,
                    'employee_name' => $record->user->user_full_name ?? 'Unknown User',
                    'payroll_period' => $record->payroll_period,
                    'gross_pay' => $record->gross_amount,
                    'net_pay' => $record->net_amount,
                    'total_deductions' => $record->attendance_deductions + $record->tax_deductions + $record->other_deductions,
                    'attendance_rate' => $record->attendance_rate,
                    'punctuality_rate' => $record->punctuality_rate,
                    'status' => $record->status,
                    'generated_by' => $record->generator ? $record->generator->user_full_name : 'System',
                    'approved_by' => $record->approver ? $record->approver->user_full_name : null,
                    'approved_at' => $record->approved_at ? $record->approved_at->format('Y-m-d H:i:s') : null,
                    'created_at' => $record->created_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $records
        ]);
    }

    /**
     * Generate payroll for a specific period.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'user_ids' => 'array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        $periodStart = $request->period_start;
        $periodEnd = $request->period_end;

        try {
            DB::beginTransaction();

            // Get user IDs to process
            $userIds = $request->user_ids;
            if (empty($userIds)) {
                // Get all users with salary settings
                $userIds = SalarySetting::forBusiness($businessId)
                    ->active()
                    ->current($periodStart)
                    ->pluck('user_id')
                    ->toArray();
            }

            if (empty($userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No employees found with salary settings'
                ], 400);
            }

            // Generate payroll for selected users
            $results = PayrollRecord::generateBulkPayroll($businessId, $userIds, $periodStart, $periodEnd);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($results as $userId => $result) {
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $user = User::find($userId);
                    $errors[] = [
                        'user' => $user ? $user->user_full_name : "User ID: $userId",
                        'error' => $result['message']
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Payroll generation completed. Success: $successCount, Errors: $errorCount",
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error generating payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load payroll data from Excel file.
     */
    public function loadFromExcel(Request $request)
    {
        $request->validate([
            'payroll_file' => 'required|file|mimes:xlsx,xls'
        ]);

        $businessId = session('user.business_id') ?? auth()->user()->business_id;

        try {
            $file = $request->file('payroll_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            
            $data = [];
            $headerRow = null;
            
            foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                
                if ($rowIndex === 1 || $headerRow === null) {
                    // Try to find header row
                    if ($this->isHeaderRow($rowData)) {
                        $headerRow = $rowData;
                        continue;
                    }
                }
                
                if ($headerRow && !empty($rowData[0]) && is_numeric($rowData[0])) {
                    $data[] = $this->mapExcelRowToPayroll($headerRow, $rowData);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payroll file loaded successfully',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading payroll file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if row contains headers.
     */
    private function isHeaderRow($rowData)
    {
        $headerKeywords = ['name', 'employee', 'salary', 'gross', 'net', 'deduction'];
        $rowText = strtolower(implode(' ', $rowData));
        
        foreach ($headerKeywords as $keyword) {
            if (strpos($rowText, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Map Excel row data to payroll structure.
     */
    private function mapExcelRowToPayroll($headers, $rowData)
    {
        $mapped = [
            'sn' => $rowData[0] ?? '',
            'name' => $rowData[1] ?? '',
            'position' => $rowData[2] ?? '',
            'gross_pay' => $rowData[3] ?? 0,
            'deduction' => $rowData[4] ?? 0,
            'additional_allowance' => $rowData[5] ?? 0,
            'uniform_allowance' => $rowData[6] ?? 0,
            'net_pay' => $rowData[7] ?? 0,
            'remarks' => $rowData[8] ?? ''
        ];

        // Clean numeric values
        foreach (['gross_pay', 'deduction', 'additional_allowance', 'uniform_allowance', 'net_pay'] as $field) {
            $mapped[$field] = is_numeric($mapped[$field]) ? floatval($mapped[$field]) : 0;
        }

        return $mapped;
    }

    /**
     * Get detailed payroll record.
     */
    public function getDetail($id)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $payroll = PayrollRecord::with(['user', 'generator', 'approver'])
            ->where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found'
            ], 404);
        }

        // Get attendance details for the period
        $attendanceDetails = AttendanceLog::where('business_id', $businessId)
            ->where('user_id', $payroll->user_id)
            ->whereBetween('attendance_date', [$payroll->period_start, $payroll->period_end])
            ->orderBy('attendance_date')
            ->get()
            ->map(function ($log) {
                return [
                    'date' => $log->attendance_date->format('Y-m-d'),
                    'status' => $log->status,
                    'clock_in' => $log->clock_in ? $log->clock_in->format('H:i:s') : null,
                    'clock_out' => $log->clock_out ? $log->clock_out->format('H:i:s') : null,
                    'total_hours' => $log->total_hours,
                    'late_status' => $log->late_status,
                    'early_leave' => $log->early_leave
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'payroll' => $payroll,
                'attendance_details' => $attendanceDetails
            ]
        ]);
    }

    /**
     * Approve payroll record.
     */
    public function approve(Request $request, $id)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $payroll = PayrollRecord::where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found'
            ], 404);
        }

        if ($payroll->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft payroll records can be approved'
            ], 400);
        }

        $payroll->approve();

        return response()->json([
            'success' => true,
            'message' => 'Payroll record approved successfully',
            'data' => $payroll
        ]);
    }

    /**
     * Delete payroll record.
     */
    public function destroy($id)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $payroll = PayrollRecord::where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found'
            ], 404);
        }

        if ($payroll->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete approved payroll record'
            ], 400);
        }

        $payroll->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payroll record deleted successfully'
        ]);
    }

    /**
     * Export payroll record to Excel.
     */
    public function export($id)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $payroll = PayrollRecord::with(['user', 'salarySetting'])
            ->where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found'
            ], 404);
        }

        try {
            // Create Excel file
            $filename = 'payroll_' . $payroll->user->user_full_name . '_' . $payroll->payroll_period . '.xlsx';
            
            // Use Laravel Excel to create the file
            return Excel::download(new PayrollExport($payroll), $filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export payroll record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark payroll as paid.
     */
    public function markAsPaid(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:500'
        ]);

        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $payroll = PayrollRecord::where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found'
            ], 404);
        }

        if ($payroll->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved payroll records can be marked as paid'
            ], 400);
        }

        $payroll->markAsPaid($request->remarks);

        return response()->json([
            'success' => true,
            'message' => 'Payroll record marked as paid successfully',
            'data' => $payroll
        ]);
    }

    /**
     * Delete payroll record.
     */
    public function delete($id)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $payroll = PayrollRecord::where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found'
            ], 404);
        }

        if ($payroll->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete paid payroll records'
            ], 400);
        }

        $payroll->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payroll record deleted successfully'
        ]);
    }

    /**
     * Export all payroll records.
     */
    public function exportAll(Request $request)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        $period = $request->get('period', Carbon::now()->format('Y-m'));

        $records = PayrollRecord::with('user')
            ->forBusiness($businessId)
            ->forPeriod($period)
            ->get();

        $data = $records->map(function ($record) {
            return [
                'Employee Name' => $record->user->user_full_name ?? 'Unknown User',
                'Period' => $record->payroll_period,
                'Gross Pay' => $record->gross_amount,
                'Allowances' => $record->allowances_total,
                'Attendance Deduction' => $record->attendance_deductions,
                'Tax Deduction' => $record->tax_deductions,
                'Other Deductions' => $record->other_deductions,
                'Net Pay' => $record->net_amount,
                'Working Days' => $record->total_working_days,
                'Present Days' => $record->total_present_days,
                'Absent Days' => $record->total_absent_days,
                'Late Days' => $record->total_late_days,
                'Status' => ucfirst($record->status)
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => $data,
            'filename' => "payroll_report_{$period}.csv"
        ]);
    }

    /**
     * Get payroll summary report.
     */
    public function getSummaryReport(Request $request)
    {
        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        $period = $request->get('period', Carbon::now()->format('Y-m'));

        $summary = PayrollRecord::getBusinessSummary($businessId, $period);
        
        $statusBreakdown = PayrollRecord::forBusiness($businessId)
            ->forPeriod($period)
            ->selectRaw('status, COUNT(*) as count, SUM(net_pay) as total_net_pay')
            ->groupBy('status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'summary' => $summary,
                'status_breakdown' => $statusBreakdown
            ]
        ]);
    }

    /**
     * Submit payroll for review.
     */
    public function submitForReview(Request $request, $id)
    {
        if (!auth()->user()->can('payroll.submit_for_review')) {
            abort(403, 'Unauthorized action.');
        }

        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $payroll = PayrollRecord::where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found'
            ], 404);
        }

        if (!$payroll->canBeSubmitted()) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll cannot be submitted in current status'
            ], 400);
        }

        try {
            $payroll->submitForReview(auth()->id(), $request->input('comments'));

            // Send notification to reviewers
            $this->sendPayrollNotification($payroll, 'submitted_for_review', [
                'subject' => 'Payroll Submitted for Review',
                'message' => "Payroll for {$payroll->user->user_full_name} ({$payroll->payroll_period}) has been submitted for review."
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payroll submitted for review successfully',
                'data' => $payroll->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Review payroll record.
     */
    public function review(Request $request, $id)
    {
        if (!auth()->user()->can('payroll.review')) {
            abort(403, 'Unauthorized action.');
        }

        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $payroll = PayrollRecord::where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found'
            ], 404);
        }

        if (!$payroll->canBeReviewed()) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll cannot be reviewed in current status'
            ], 400);
        }

        try {
            $payroll->review(auth()->id(), $request->input('comments'));

            // Send notification to approvers
            $this->sendPayrollNotification($payroll, 'reviewed', [
                'subject' => 'Payroll Reviewed - Ready for Approval',
                'message' => "Payroll for {$payroll->user->user_full_name} ({$payroll->payroll_period}) has been reviewed and is ready for approval."
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payroll reviewed successfully',
                'data' => $payroll->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error reviewing payroll: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Reject payroll record.
     */
    public function reject(Request $request, $id)
    {
        if (!auth()->user()->can('payroll.reject')) {
            abort(403, 'Unauthorized action.');
        }

        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $payroll = PayrollRecord::where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found'
            ], 404);
        }

        if (!$payroll->canBeRejected()) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll cannot be rejected in current status'
            ], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $payroll->reject(auth()->id(), $request->input('reason'), $request->input('comments'));

            // Send notification to submitter
            $this->sendPayrollNotification($payroll, 'rejected', [
                'subject' => 'Payroll Rejected',
                'message' => "Payroll for {$payroll->user->user_full_name} ({$payroll->payroll_period}) has been rejected. Reason: {$request->input('reason')}"
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payroll rejected successfully',
                'data' => $payroll->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get approval history for a payroll record.
     */
    public function getApprovalHistory($id)
    {
        if (!auth()->user()->can('payroll.view_approval_status')) {
            abort(403, 'Unauthorized action.');
        }

        $businessId = session('user.business_id') ?? auth()->user()->business_id;
        
        $payroll = PayrollRecord::where('business_id', $businessId)
            ->where('id', $id)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found'
            ], 404);
        }

        $history = $payroll->approvalHistory()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Send payroll notification using Memos system.
     */
    private function sendPayrollNotification($payroll, $action, $notificationData)
    {
        try {
            // Get users who should receive notifications based on action
            $recipients = $this->getNotificationRecipients($payroll, $action);
            
            if (empty($recipients)) {
                return;
            }

            // Create memo using the existing Memos system
            $memo = \Modules\Essentials\Entities\Memo::create([
                'business_id' => $payroll->business_id,
                'sender_id' => auth()->id(),
                'subject' => $notificationData['subject'],
                'body' => $notificationData['message'],
                'status' => 'sent'
            ]);

            // Add recipients
            foreach ($recipients as $recipientId) {
                \Modules\Essentials\Entities\MemoRecipient::create([
                    'memo_id' => $memo->id,
                    'user_id' => $recipientId,
                    'recipient_type' => 'to'
                ]);
            }

            // Send notifications
            $this->sendMemoNotifications($memo);

        } catch (\Exception $e) {
            \Log::error('Failed to send payroll notification: ' . $e->getMessage());
        }
    }

    /**
     * Get notification recipients based on action.
     */
    private function getNotificationRecipients($payroll, $action)
    {
        $businessId = $payroll->business_id;
        $recipients = [];

        switch ($action) {
            case 'submitted_for_review':
                // Notify users with review permission
                $recipients = \App\User::where('business_id', $businessId)
                    ->whereHas('roles.permissions', function($query) {
                        $query->where('name', 'payroll.review');
                    })
                    ->pluck('id')
                    ->toArray();
                break;

            case 'reviewed':
                // Notify users with approve permission
                $recipients = \App\User::where('business_id', $businessId)
                    ->whereHas('roles.permissions', function($query) {
                        $query->where('name', 'payroll.approve');
                    })
                    ->pluck('id')
                    ->toArray();
                break;

            case 'approved':
                // Notify submitter and HR team
                $recipients = array_merge(
                    [$payroll->submitted_by],
                    \App\User::where('business_id', $businessId)
                        ->whereHas('roles.permissions', function($query) {
                            $query->where('name', 'payroll.view_all');
                        })
                        ->pluck('id')
                        ->toArray()
                );
                break;

            case 'rejected':
                // Notify submitter
                $recipients = [$payroll->submitted_by];
                break;
        }

        return array_filter(array_unique($recipients));
    }

    /**
     * Send memo notifications.
     */
    private function sendMemoNotifications($memo)
    {
        try {
            $recipients = $memo->recipients()->with('user')->get();
            
            foreach ($recipients as $recipient) {
                if ($recipient->user->email) {
                    // Use the existing notification system
                    $notificationUtil = app(\App\Utils\NotificationUtil::class);
                    $notificationUtil->autoSendNotification(
                        $memo->business_id,
                        'new_memo',
                        $memo,
                        $recipient->user
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send memo notifications: ' . $e->getMessage());
        }
    }
}