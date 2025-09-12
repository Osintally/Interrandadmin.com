@extends('layouts.app')
@section('title', __('essentials::lang.attendance') . ' - Dashboard')

@section('content')
@include('essentials::layouts.nav_hrm')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('essentials::lang.attendance') Dashboard
    </h1>
</section>

<!-- Main content -->
<section class="content">
    
    <!-- Dashboard Cards -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <!-- Total Employees -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ $stats['total_employees'] }}</h3>
                    <p>Total Employees</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-stalker"></i>
                </div>
                <a href="{{ url('/hrm/attendance/employee-management') }}" class="small-box-footer">
                    Manage Employees <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <!-- Punctual Today -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $stats['punctual_today'] }}</h3>
                    <p>Punctual Today (≤8:00 AM)</p>
                </div>
                <div class="icon">
                    <i class="fa fa-clock-o"></i>
                </div>
                <a href="{{ url('/hrm/attendance') }}" class="small-box-footer">
                    View Details <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <!-- Late Today -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $stats['late_today'] }}</h3>
                    <p>Late Today (>8:15 AM)</p>
                </div>
                <div class="icon">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <a href="{{ url('/hrm/attendance') }}?status=late" class="small-box-footer">
                    View Late Employees <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <!-- Inactive Employees -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $stats['inactive_employees'] }}</h3>
                    <p>Inactive (21+ Days Absent)</p>
                </div>
                <div class="icon">
                    <i class="fa fa-user-times"></i>
                </div>
                <a href="#" data-toggle="modal" data-target="#inactive_employees_modal" class="small-box-footer">
                    View Details <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Monthly Statistics</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-blue"><i class="fa fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Working Days</span>
                                    <span class="info-box-number">{{ $stats['monthly_stats']['total_working_days'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-green"><i class="fa fa-percent"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Avg Attendance</span>
                                    <span class="info-box-number">{{ $stats['monthly_stats']['average_attendance'] }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Late Arrivals</span>
                                    <span class="info-box-number">{{ $stats['monthly_stats']['total_late_arrivals'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-red"><i class="fa fa-sign-out"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Early Leaves</span>
                                    <span class="info-box-number">{{ $stats['monthly_stats']['total_early_leaves'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Quick Actions</h3>
                </div>
                <div class="box-body">
                    <div class="btn-group" role="group">
                        <a href="{{ url('/hrm/attendance/all-users-summary') }}" class="btn btn-info">
                            <i class="fa fa-users"></i> View All Employee Reports
                        </a>
                        <a href="{{ url('/hrm/attendance') }}" class="btn btn-primary">
                            <i class="fa fa-table"></i> Detailed Attendance Records
                        </a>
                        @can('essentials.crud_all_attendance')
                        <a href="{{ url('/hrm/attendance/employee-management') }}" class="btn btn-success">
                            <i class="fa fa-cog"></i> Employee Management
                        </a>
                        @endcan
                        <a href="{{ url('/hrm/payroll') }}" class="btn btn-warning">
                            <i class="fa fa-money"></i> Payroll Management
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

<!-- Inactive Employees Modal -->
<div class="modal fade" id="inactive_employees_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Inactive Employees (21+ Days Absent)</h4>
            </div>
            <div class="modal-body">
                @if(count($stats['inactive_employee_list']) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Username</th>
                                    <th>Days Absent</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['inactive_employee_list'] as $item)
                                <tr>
                                    <td>{{ $item['employee']->first_name }} {{ $item['employee']->last_name }}</td>
                                    <td>{{ $item['employee']->username }}</td>
                                    <td><span class="label label-danger">{{ $item['absent_days'] }} days</span></td>
                                    <td>
                                        <a href="{{ url('/hrm/attendance/user-summary/' . $item['employee']->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fa fa-eye"></i> View Report
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted">No inactive employees found.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    // Auto-refresh dashboard every 30 minutes
    setInterval(function() {
        location.reload();
    }, 1800000); // 30 minutes in milliseconds
});
</script>
@endsection
