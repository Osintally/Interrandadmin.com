@extends('layouts.app')
@section('title', __('essentials::lang.attendance') . ' - ' . $employee->first_name . ' ' . $employee->last_name)

@section('content')
@include('essentials::layouts.nav_hrm')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        Attendance Report - {{ $employee->first_name }} {{ $employee->last_name }}
        <small>{{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }} (21 Days)</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    
    <!-- Employee Info & Statistics -->
    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Employee Information</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Username:</strong></td>
                            <td>{{ $employee->username }}</td>
                        </tr>
                        @if($employeeSettings)
                        <tr>
                            <td><strong>Employee Code:</strong></td>
                            <td>{{ $employeeSettings->employee_code ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="label label-{{ $employeeSettings->status === 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($employeeSettings->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Work Hours:</strong></td>
                            <td>{{ $employeeSettings->default_start_time }} - {{ $employeeSettings->default_end_time }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">21-Day Summary Statistics</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Present Days</span>
                                    <span class="info-box-number">{{ $stats['total_present'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-red"><i class="fa fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Absent Days</span>
                                    <span class="info-box-number">{{ $stats['total_absent'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Late Days</span>
                                    <span class="info-box-number">{{ $stats['total_late'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-blue"><i class="fa fa-hourglass-half"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Avg Hours</span>
                                    <span class="info-box-number">{{ $stats['average_hours'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-green"><i class="fa fa-percent"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Attendance Percentage</span>
                                    <span class="info-box-number">{{ $stats['attendance_percentage'] }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-blue"><i class="fa fa-star"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Punctuality Percentage</span>
                                    <span class="info-box-number">{{ $stats['punctuality_percentage'] }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 21-Day Calendar -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">21-Day Attendance Calendar</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ url('/hrm/attendance/all-users-summary') }}" class="btn btn-sm btn-default">
                            <i class="fa fa-arrow-left"></i> Back to All Employees
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <!-- Legend -->
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-12">
                            <div class="legend">
                                <span class="legend-item">
                                    <span class="legend-color present"></span> Present
                                </span>
                                <span class="legend-item">
                                    <span class="legend-color late"></span> Late
                                </span>
                                <span class="legend-item">
                                    <span class="legend-color absent"></span> Absent
                                </span>
                                <span class="legend-item">
                                    <span class="legend-color weekend"></span> Weekend
                                </span>
                                <span class="legend-item">
                                    <span class="legend-color today"></span> Today
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="calendar-grid">
                        @foreach(array_chunk($calendar, 7) as $week)
                        <div class="calendar-week">
                            @foreach($week as $day)
                            <div class="calendar-day 
                                @if($day['is_weekend']) weekend @endif
                                @if($day['is_today']) today @endif
                                @if($day['attendance'])
                                    @if($day['attendance']->status === 'present' && $day['attendance']->late_minutes == 0) present
                                    @elseif($day['attendance']->status === 'present' && $day['attendance']->late_minutes > 0) late
                                    @elseif($day['attendance']->status === 'late') late
                                    @elseif($day['attendance']->status === 'absent') absent
                                    @else {{ $day['attendance']->status }}
                                    @endif
                                @elseif(!$day['is_weekend'] && $day['date']->lte(now())) absent
                                @endif
                            ">
                                <div class="date-number">{{ $day['date']->day }}</div>
                                <div class="date-month">{{ $day['date']->format('M') }}</div>
                                
                                @if($day['attendance'])
                                <div class="attendance-info">
                                    @if($day['attendance']->clock_in_time)
                                        <div class="clock-time">
                                            <i class="fa fa-sign-in"></i> {{ $day['attendance']->clock_in_time->format('H:i') }}
                                        </div>
                                    @endif
                                    
                                    @if($day['attendance']->clock_out_time)
                                        <div class="clock-time">
                                            <i class="fa fa-sign-out"></i> {{ $day['attendance']->clock_out_time->format('H:i') }}
                                        </div>
                                    @endif
                                    
                                    @if($day['attendance']->late_minutes > 0)
                                        <div class="late-info">
                                            <small><i class="fa fa-clock-o"></i> {{ $day['attendance']->late_minutes }}min late</small>
                                        </div>
                                    @endif
                                    
                                    @if($day['attendance']->total_hours > 0)
                                        <div class="hours-info">
                                            <small><i class="fa fa-hourglass-half"></i> {{ $day['attendance']->total_hours }}h</small>
                                        </div>
                                    @endif
                                </div>
                                @elseif(!$day['is_weekend'] && $day['date']->lte(now()))
                                <div class="attendance-info">
                                    <small class="text-muted">No Record</small>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

@endsection

@section('css')
<style>
.legend {
    margin-bottom: 15px;
}

.legend-item {
    display: inline-block;
    margin-right: 20px;
}

.legend-color {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin-right: 5px;
    border-radius: 3px;
    vertical-align: middle;
}

.legend-color.present {
    background-color: #00a65a;
}

.legend-color.late {
    background-color: #f39c12;
}

.legend-color.absent {
    background-color: #dd4b39;
}

.legend-color.weekend {
    background-color: #95a5a6;
}

.legend-color.today {
    background-color: #3c8dbc;
    border: 2px solid #2c5282;
}

.calendar-grid {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.calendar-week {
    display: flex;
    gap: 5px;
}

.calendar-day {
    flex: 1;
    min-height: 120px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    padding: 8px;
    position: relative;
    background-color: #ffffff;
    transition: all 0.3s ease;
}

.calendar-day:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.calendar-day.present {
    background-color: #d4edda;
    border-color: #00a65a;
}

.calendar-day.late {
    background-color: #fff3cd;
    border-color: #f39c12;
}

.calendar-day.absent {
    background-color: #f8d7da;
    border-color: #dd4b39;
}

.calendar-day.weekend {
    background-color: #f8f9fa;
    border-color: #95a5a6;
}

.calendar-day.today {
    border-color: #3c8dbc;
    border-width: 3px;
    box-shadow: 0 0 10px rgba(60, 141, 188, 0.3);
}

.date-number {
    font-size: 18px;
    font-weight: bold;
    text-align: center;
    color: #2c3e50;
}

.date-month {
    font-size: 11px;
    text-align: center;
    color: #7f8c8d;
    margin-bottom: 5px;
}

.attendance-info {
    font-size: 10px;
    color: #2c3e50;
}

.clock-time {
    margin: 2px 0;
}

.late-info {
    color: #d68910;
    font-weight: bold;
}

.hours-info {
    color: #148f77;
}

@media (max-width: 768px) {
    .calendar-week {
        flex-wrap: wrap;
    }
    
    .calendar-day {
        flex: 0 0 calc(50% - 5px);
        min-height: 100px;
    }
}

@media (max-width: 480px) {
    .calendar-day {
        flex: 0 0 100%;
        min-height: 80px;
    }
}
</style>
@endsection
