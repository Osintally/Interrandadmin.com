@extends('layouts.app')
@section('title', __('essentials::lang.attendance') . ' - All Users Summary')

@section('content')
@include('essentials::layouts.nav_hrm')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        All Employees - Attendance Summary
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-users"></i> All Employees
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ url('/hrm/attendance/dashboard') }}" class="btn btn-sm btn-default">
                            <i class="fa fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <p class="text-muted">
                        Click on any employee to view their detailed 21-day attendance calendar report.
                    </p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="users_table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>@lang('lang_v1.name')</th>
                                    <th>@lang('lang_v1.username')</th>
                                    <th>Employee Code</th>
                                    <th>Status</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    // Initialize DataTable
    var users_table = $('#users_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('/hrm/attendance/all-users-summary') }}",
            type: "GET"
        },
        columns: [
            { data: 'full_name', name: 'full_name' },
            { data: 'username', name: 'users.username' },
            { data: 'employee_code', name: 'essentials_employee_settings.employee_code' },
            { data: 'status', name: 'essentials_employee_settings.status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true
    });

    // Handle view attendance button click
    $(document).on('click', '.view-attendance', function() {
        var userId = $(this).data('user-id');
        window.open("{{ url('/hrm/attendance/user-summary') }}/" + userId, '_blank');
    });
});
</script>
@endsection
