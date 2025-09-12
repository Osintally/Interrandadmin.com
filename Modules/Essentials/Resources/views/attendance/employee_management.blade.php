@extends('layouts.app')
@section('title', __('essentials::lang.attendance') . ' - Employee Management')

@section('content')
@include('essentials::layouts.nav_hrm')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        Employee Management
        <small>Manage employee settings and status</small>
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
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#employee_modal">
                            <i class="fa fa-plus"></i> Add Employee Settings
                        </button>
                        <a href="{{ url('/hrm/attendance/dashboard') }}" class="btn btn-sm btn-default">
                            <i class="fa fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="employee_table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>@lang('lang_v1.name')</th>
                                    <th>@lang('lang_v1.username')</th>
                                    <th>Employee Code</th>
                                    <th>Status</th>
                                    <th>Work Hours</th>
                                    <th>Base Salary</th>
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

<!-- Employee Settings Modal -->
<div class="modal fade" id="employee_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Employee Settings</h4>
            </div>
            
            <form id="employee_form" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id" class="required">Employee <span class="text-danger">*</span></label>
                                {!! Form::select('user_id', [], null, ['class' => 'form-control select2', 'id' => 'user_id', 'placeholder' => 'Select Employee', 'required' => true]); !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_code">Employee Code</label>
                                {!! Form::text('employee_code', null, ['class' => 'form-control', 'id' => 'employee_code', 'placeholder' => 'Enter employee code']); !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status" class="required">Status <span class="text-danger">*</span></label>
                                {!! Form::select('status', ['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'], 'active', ['class' => 'form-control', 'id' => 'status', 'required' => true]); !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="grace_minutes" class="required">Grace Minutes <span class="text-danger">*</span></label>
                                {!! Form::number('grace_minutes', 15, ['class' => 'form-control', 'id' => 'grace_minutes', 'min' => 0, 'max' => 120, 'required' => true]); !!}
                                <small class="text-muted">Minutes allowed for late arrival</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="default_start_time" class="required">Start Time <span class="text-danger">*</span></label>
                                {!! Form::time('default_start_time', '08:00', ['class' => 'form-control', 'id' => 'default_start_time', 'required' => true]); !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="default_end_time" class="required">End Time <span class="text-danger">*</span></label>
                                {!! Form::time('default_end_time', '17:00', ['class' => 'form-control', 'id' => 'default_end_time', 'required' => true]); !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="base_salary" class="required">Base Salary <span class="text-danger">*</span></label>
                                {!! Form::number('base_salary', 0, ['class' => 'form-control', 'id' => 'base_salary', 'step' => '0.01', 'min' => 0, 'required' => true]); !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="overtime_rate" class="required">Overtime Rate <span class="text-danger">*</span></label>
                                {!! Form::number('overtime_rate', 1.5, ['class' => 'form-control', 'id' => 'overtime_rate', 'step' => '0.1', 'min' => 1, 'required' => true]); !!}
                                <small class="text-muted">Multiplier for overtime hours</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    {!! Form::checkbox('overtime_eligible', 1, false, ['id' => 'overtime_eligible']); !!}
                                    Overtime Eligible
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="working_days" class="required">Working Days <span class="text-danger">*</span></label>
                                <div class="checkbox-group">
                                    <label class="checkbox-inline">
                                        {!! Form::checkbox('working_days[]', 1, true, ['id' => 'monday']); !!} Monday
                                    </label>
                                    <label class="checkbox-inline">
                                        {!! Form::checkbox('working_days[]', 2, true, ['id' => 'tuesday']); !!} Tuesday
                                    </label>
                                    <label class="checkbox-inline">
                                        {!! Form::checkbox('working_days[]', 3, true, ['id' => 'wednesday']); !!} Wednesday
                                    </label>
                                    <label class="checkbox-inline">
                                        {!! Form::checkbox('working_days[]', 4, true, ['id' => 'thursday']); !!} Thursday
                                    </label>
                                    <label class="checkbox-inline">
                                        {!! Form::checkbox('working_days[]', 5, true, ['id' => 'friday']); !!} Friday
                                    </label>
                                    <label class="checkbox-inline">
                                        {!! Form::checkbox('working_days[]', 6, false, ['id' => 'saturday']); !!} Saturday
                                    </label>
                                    <label class="checkbox-inline">
                                        {!! Form::checkbox('working_days[]', 0, false, ['id' => 'sunday']); !!} Sunday
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    // Initialize DataTable
    var employee_table = $('#employee_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('/hrm/attendance/employee-management') }}",
            type: "GET"
        },
        columns: [
            { data: 'full_name', name: 'full_name' },
            { data: 'username', name: 'users.username' },
            { data: 'employee_code', name: 'essentials_employee_settings.employee_code' },
            { data: 'status', name: 'essentials_employee_settings.status' },
            { 
                data: null, 
                name: 'work_hours', 
                render: function(data, type, row) {
                    return row.default_start_time + ' - ' + row.default_end_time;
                },
                orderable: false
            },
            { data: 'base_salary', name: 'essentials_employee_settings.base_salary' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true
    });

    // Load users for dropdown
    loadUsersDropdown();

    // Handle form submission
    $('#employee_form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: "{{ url('/hrm/attendance/store-employee-settings') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#employee_modal').modal('hide');
                    $('#employee_form')[0].reset();
                    employee_table.ajax.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    for (var key in errors) {
                        toastr.error(errors[key][0]);
                    }
                } else {
                    toastr.error('Something went wrong');
                }
            }
        });
    });

    // Handle edit employee button click
    $(document).on('click', '.edit-employee', function() {
        var id = $(this).data('id');
        
        // Load employee data and populate form
        $.ajax({
            url: "{{ url('/hrm/attendance/get-employee-settings') }}/" + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    populateForm(response.data);
                    $('#employee_modal').modal('show');
                }
            }
        });
    });

    // Handle delete employee button click
    $(document).on('click', '.delete-employee', function() {
        var id = $(this).data('id');
        
        swal({
            title: "Are you sure?",
            text: "Employee settings will be deleted permanently!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!",
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: "{{ url('/hrm/attendance/delete-employee-settings') }}/" + id,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            employee_table.ajax.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    }
                });
            }
        });
    });

    // Reset form when modal is hidden
    $('#employee_modal').on('hidden.bs.modal', function() {
        $('#employee_form')[0].reset();
        $('#user_id').val(null).trigger('change');
        $('.modal-title').text('Employee Settings');
    });
});

function loadUsersDropdown() {
    $.ajax({
        url: "{{ url('/hrm/attendance/get-users-for-dropdown') }}",
        type: 'GET',
        success: function(response) {
            var options = '<option value="">Select Employee</option>';
            $.each(response.data, function(key, value) {
                options += '<option value="' + value.id + '">' + value.text + '</option>';
            });
            $('#user_id').html(options);
        }
    });
}

function populateForm(data) {
    $('#user_id').val(data.user_id).trigger('change');
    $('#employee_code').val(data.employee_code);
    $('#status').val(data.status);
    $('#default_start_time').val(data.default_start_time);
    $('#default_end_time').val(data.default_end_time);
    $('#grace_minutes').val(data.grace_minutes);
    $('#base_salary').val(data.base_salary);
    $('#overtime_rate').val(data.overtime_rate);
    $('#overtime_eligible').prop('checked', data.overtime_eligible);
    
    // Set working days
    $('input[name="working_days[]"]').prop('checked', false);
    if (data.working_days) {
        $.each(data.working_days, function(index, day) {
            $('input[name="working_days[]"][value="' + day + '"]').prop('checked', true);
        });
    }
    
    $('.modal-title').text('Edit Employee Settings - ' + data.employee_name);
}
</script>
@endsection
