<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\Performance\PerformanceAlert;
use App\Models\Performance\PerformanceGoal;
use App\Models\Performance\PerformanceKPI;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlertsController extends Controller
{
    /**
     * Display a listing of performance alerts
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user->hasAnyPermission([
            'performance.kpi.view_all',
            'performance.kpi.view_department',
            'performance.kpi.view_own'
        ])) {
            abort(403, 'No permission to view performance alerts');
        }

        $query = PerformanceAlert::with(['employee', 'kpi', 'goal']);

        // Apply filters based on permissions
        if ($user->hasPermissionTo('performance.kpi.view_all')) {
            // Can view all alerts
        } elseif ($user->hasPermissionTo('performance.kpi.view_department')) {
            // Can view department alerts
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('essentials_department_id', $user->essentials_department_id);
            });
        } else {
            // Can only view own alerts
            $query->where('employee_id', $user->id);
        }

        // Apply additional filters
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        if ($request->has('status')) {
            if ($request->get('status') === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->get('status') === 'read') {
                $query->where('is_read', true);
            } elseif ($request->get('status') === 'resolved') {
                $query->where('status', 'resolved');
            }
        }

        $alerts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('essentials::performance.alerts_index', [
            'alerts' => $alerts,
        ]);
    }

    /**
     * Display the specified alert
     */
    public function show(PerformanceAlert $alert)
    {
        $user = auth()->user();
        
        // Check permissions
        if (!$user->hasPermissionTo('performance.kpi.view_all') &&
            !($user->hasPermissionTo('performance.kpi.view_department') && $alert->employee->essentials_department_id === $user->essentials_department_id) &&
            $alert->employee_id !== $user->id) {
            abort(403, 'No permission to view this alert');
        }

        $alert->load(['employee', 'kpi', 'goal']);

        $html = view('essentials::performance.alert_detail', compact('alert'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'alert_id' => $alert->id
        ]);
    }

    /**
     * Mark alert as read
     */
    public function markAsRead(PerformanceAlert $alert)
    {
        $user = auth()->user();
        
        // Check permissions
        if (!$user->hasPermissionTo('performance.kpi.view_all') &&
            !($user->hasPermissionTo('performance.kpi.view_department') && $alert->employee->essentials_department_id === $user->essentials_department_id) &&
            $alert->employee_id !== $user->id) {
            abort(403, 'No permission to modify this alert');
        }

        try {
            $alert->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Alert marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark alert as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all alerts as read
     */
    public function markAllAsRead(Request $request)
    {
        $this->authorize('performance.kpi.view_all');

        try {
            $user = $request->user();
            
            $query = PerformanceAlert::where('is_read', false);

            // Apply permissions
            if (!$user->hasPermissionTo('performance.kpi.view_all')) {
                if ($user->hasPermissionTo('performance.kpi.view_department')) {
                    $query->whereHas('employee', function($q) use ($user) {
                        $q->where('essentials_department_id', $user->essentials_department_id);
                    });
                } else {
                    $query->where('employee_id', $user->id);
                }
            }

            $query->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'All alerts marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all alerts as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve alert
     */
    public function resolve(PerformanceAlert $alert)
    {
        $user = auth()->user();
        
        // Check permissions
        if (!$user->hasPermissionTo('performance.kpi.view_all') &&
            !($user->hasPermissionTo('performance.kpi.view_department') && $alert->employee->essentials_department_id === $user->essentials_department_id)) {
            abort(403, 'No permission to resolve this alert');
        }

        try {
            $alert->update([
                'status' => 'resolved',
                'resolved_at' => Carbon::now(),
                'resolved_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alert resolved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve alert: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified alert
     */
    public function destroy(PerformanceAlert $alert)
    {
        $user = auth()->user();
        
        // Check permissions
        if (!$user->hasPermissionTo('performance.kpi.view_all') &&
            !($user->hasPermissionTo('performance.kpi.view_department') && $alert->employee->essentials_department_id === $user->essentials_department_id) &&
            $alert->employee_id !== $user->id) {
            abort(403, 'No permission to delete this alert');
        }

        try {
            $alert->delete();

            return response()->json([
                'success' => true,
                'message' => 'Alert deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete alert: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export performance alerts
     */
    public function export(Request $request)
    {
        $this->authorize('performance.kpi.view_all');

        $query = PerformanceAlert::with(['employee', 'kpi', 'goal']);

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        if ($request->has('status')) {
            if ($request->get('status') === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->get('status') === 'read') {
                $query->where('is_read', true);
            } elseif ($request->get('status') === 'resolved') {
                $query->where('status', 'resolved');
            }
        }

        $alerts = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'performance_alerts_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($alerts) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Type',
                'Title',
                'Employee',
                'Priority',
                'Status',
                'Is Read',
                'Created At',
                'Resolved At'
            ]);

            // CSV data
            foreach ($alerts as $alert) {
                fputcsv($file, [
                    $alert->type,
                    $alert->title,
                    $alert->employee->first_name . ' ' . $alert->employee->last_name,
                    $alert->priority,
                    $alert->status,
                    $alert->is_read ? 'Yes' : 'No',
                    $alert->created_at->format('Y-m-d H:i:s'),
                    $alert->resolved_at ? $alert->resolved_at->format('Y-m-d H:i:s') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get alerts data for DataTables
     */
    public function getAlertsData(Request $request)
    {
        $user = $request->user();
        
        $query = PerformanceAlert::with(['employee', 'kpi', 'goal']);

        // Apply filters based on permissions
        if ($user->hasPermissionTo('performance.kpi.view_all')) {
            // Can view all alerts
        } elseif ($user->hasPermissionTo('performance.kpi.view_department')) {
            // Can view department alerts
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('essentials_department_id', $user->essentials_department_id);
            });
        } else {
            // Can only view own alerts
            $query->where('employee_id', $user->id);
        }

        // Apply additional filters
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        if ($request->has('status')) {
            if ($request->get('status') === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->get('status') === 'read') {
                $query->where('is_read', true);
            } elseif ($request->get('status') === 'resolved') {
                $query->where('status', 'resolved');
            }
        }

        return datatables($query)
            ->addColumn('type_badge', function ($alert) {
                $colors = [
                    'goal_missed' => 'danger',
                    'submission_overdue' => 'warning',
                    'performance_decline' => 'warning',
                    'kpi_threshold_breach' => 'danger',
                    'goal_achieved' => 'success',
                    'deadline_approaching' => 'info'
                ];
                
                $color = $colors[$alert->type] ?? 'secondary';
                $label = ucwords(str_replace('_', ' ', $alert->type));
                
                return '<span class="badge badge-' . $color . '">' . $label . '</span>';
            })
            ->addColumn('employee_name', function ($alert) {
                return $alert->employee->first_name . ' ' . $alert->employee->last_name;
            })
            ->addColumn('priority_badge', function ($alert) {
                $colors = [
                    'high' => 'danger',
                    'medium' => 'warning',
                    'low' => 'info'
                ];
                
                $color = $colors[$alert->priority] ?? 'secondary';
                
                return '<span class="badge badge-' . $color . '">' . ucfirst($alert->priority) . '</span>';
            })
            ->addColumn('status_badge', function ($alert) {
                if ($alert->status === 'resolved') {
                    return '<span class="badge badge-success">Resolved</span>';
                } elseif ($alert->is_read) {
                    return '<span class="badge badge-info">Read</span>';
                } else {
                    return '<span class="badge badge-warning">Unread</span>';
                }
            })
            ->addColumn('action', function ($alert) {
                $actions = '';
                
                $actions .= '<button class="btn btn-sm btn-primary view-alert" data-id="' . $alert->id . '">
                    <i class="fa fa-eye"></i> View
                </button> ';
                
                if (!$alert->is_read) {
                    $actions .= '<button class="btn btn-sm btn-info mark-read" data-id="' . $alert->id . '">
                        <i class="fa fa-check"></i> Mark Read
                    </button> ';
                }
                
                if ($alert->status !== 'resolved' && auth()->user()->can('performance.kpi.view_all')) {
                    $actions .= '<button class="btn btn-sm btn-success resolve-alert" data-id="' . $alert->id . '">
                        <i class="fa fa-check-circle"></i> Resolve
                    </button> ';
                }
                
                $actions .= '<button class="btn btn-sm btn-danger delete-alert" data-id="' . $alert->id . '">
                    <i class="fa fa-trash"></i> Delete
                </button>';
                
                return $actions;
            })
            ->rawColumns(['type_badge', 'priority_badge', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Generate performance alerts based on current data
     */
    public static function generateAlerts()
    {
        // Check for missed goals
        $missedGoals = PerformanceGoal::where('status', 'active')
            ->where('period_end', '<', Carbon::now())
            ->get();

        foreach ($missedGoals as $goal) {
            PerformanceAlert::updateOrCreate(
                [
                    'type' => 'goal_missed',
                    'employee_id' => $goal->employee_id,
                    'kpi_id' => $goal->kpi_id,
                    'goal_id' => $goal->id,
                ],
                [
                    'title' => 'Goal Missed: ' . $goal->kpi->name,
                    'message' => 'The performance goal for ' . $goal->kpi->name . ' was not achieved by the target date.',
                    'priority' => 'high',
                    'is_read' => false,
                    'status' => 'active',
                ]
            );
        }

        // Check for goals approaching deadline
        $approachingGoals = PerformanceGoal::where('status', 'active')
            ->where('period_end', '<=', Carbon::now()->addDays(7))
            ->where('period_end', '>', Carbon::now())
            ->get();

        foreach ($approachingGoals as $goal) {
            PerformanceAlert::updateOrCreate(
                [
                    'type' => 'deadline_approaching',
                    'employee_id' => $goal->employee_id,
                    'kpi_id' => $goal->kpi_id,
                    'goal_id' => $goal->id,
                ],
                [
                    'title' => 'Goal Deadline Approaching: ' . $goal->kpi->name,
                    'message' => 'The performance goal for ' . $goal->kpi->name . ' deadline is approaching in ' . $goal->getDaysRemaining() . ' days.',
                    'priority' => 'medium',
                    'is_read' => false,
                    'status' => 'active',
                ]
            );
        }

        // Check for achieved goals
        $achievedGoals = PerformanceGoal::where('status', 'active')
            ->where('achievement_percentage', '>=', 100)
            ->get();

        foreach ($achievedGoals as $goal) {
            PerformanceAlert::updateOrCreate(
                [
                    'type' => 'goal_achieved',
                    'employee_id' => $goal->employee_id,
                    'kpi_id' => $goal->kpi_id,
                    'goal_id' => $goal->id,
                ],
                [
                    'title' => 'Goal Achieved: ' . $goal->kpi->name,
                    'message' => 'Congratulations! The performance goal for ' . $goal->kpi->name . ' has been achieved.',
                    'priority' => 'low',
                    'is_read' => false,
                    'status' => 'active',
                ]
            );
        }
    }
}
