@extends('layouts.app')

@section('title', __('essentials::lang.memos'))

@section('content')
@include('essentials::layouts.nav_essentials')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('essentials::lang.corporate_memos')</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-primary" id="compose_memo_btn">
                            <i class="fa fa-plus"></i> @lang('essentials::lang.compose_memo')
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="memos_table">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.subject')</th>
                                    <th>@lang('essentials::lang.sender')</th>
                                    <th>@lang('essentials::lang.recipients')</th>
                                    <th>@lang('essentials::lang.date')</th>
                                    <th>@lang('essentials::lang.status')</th>
                                    <th>@lang('essentials::lang.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Compose Modal -->
<div class="modal fade" id="compose_memo_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@lang('essentials::lang.compose_memo')</h4>
            </div>
            <form id="compose_memo_form" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>@lang('essentials::lang.to') *</label>
                                <select name="to_recipients[]" id="to_recipients" class="form-control select2" multiple required style="width: 100%;">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('essentials::lang.cc')</label>
                                <select name="cc_recipients[]" id="cc_recipients" class="form-control select2" multiple style="width: 100%;">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('essentials::lang.bcc')</label>
                                <select name="bcc_recipients[]" id="bcc_recipients" class="form-control select2" multiple style="width: 100%;">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>@lang('essentials::lang.subject') *</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('essentials::lang.message') *</label>
                        <textarea name="body" id="memo_body" class="form-control" rows="10" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>@lang('essentials::lang.attachments')</label>
                        <input type="file" name="attachments[]" id="memo_attachments" multiple class="form-control">
                        <small class="text-muted">@lang('essentials::lang.max_file_size_50mb')</small>
                        <div id="attachment_preview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('essentials::lang.cancel')</button>
                    <button type="button" class="btn btn-info" id="save_draft_btn">@lang('essentials::lang.save_draft')</button>
                    <button type="submit" class="btn btn-primary">@lang('essentials::lang.send')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Memo Modal -->
<div class="modal fade" id="view_memo_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="view_memo_content">
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var memos_table = $('#memos_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'index']) }}",
        columns: [
            {data: 'subject', name: 'subject'},
            {data: 'sender', name: 'sender'},
            {data: 'recipients_count', name: 'recipients_count'},
            {data: 'created_at', name: 'created_at'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[3, 'desc']]
    });

    tinymce.init({
        selector: '#memo_body',
        height: 300,
        plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
    });

    $('#to_recipients, #cc_recipients, #bcc_recipients').select2({
        ajax: {
            url: "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'searchUsers']) }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            processResults: function (data, params) {
                return {
                    results: data.results
                };
            },
            cache: true
        },
        placeholder: 'Select users...',
        minimumInputLength: 1
    });

    $('#compose_memo_btn').click(function() {
        $('#compose_memo_form')[0].reset();
        $('#to_recipients, #cc_recipients, #bcc_recipients').val(null).trigger('change');
        tinymce.get('memo_body').setContent('');
        $('#attachment_preview').empty();
        $('#compose_memo_modal').modal('show');
    });

    $('#memo_attachments').change(function() {
        var files = this.files;
        var preview = $('#attachment_preview');
        preview.empty();
        
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var fileSize = (file.size / 1024 / 1024).toFixed(2);
            var fileItem = $('<div class="alert alert-info"><i class="fa fa-file"></i> ' + file.name + ' (' + fileSize + ' MB)</div>');
            preview.append(fileItem);
        }
    });

    $('#compose_memo_form').submit(function(e) {
        e.preventDefault();
        tinymce.triggerSave();
        
        var formData = new FormData(this);
        formData.append('send', '1');
        
        $.ajax({
            url: "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'store']) }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#compose_memo_modal').modal('hide');
                    memos_table.ajax.reload();
                    toastr.success(response.msg);
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while sending the memo.');
            }
        });
    });

    $('#save_draft_btn').click(function() {
        tinymce.triggerSave();
        
        var formData = new FormData($('#compose_memo_form')[0]);
        
        $.ajax({
            url: "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'store']) }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#compose_memo_modal').modal('hide');
                    memos_table.ajax.reload();
                    toastr.success(response.msg);
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while saving the draft.');
            }
        });
    });

    $(document).on('click', '.view-memo', function() {
        var memo_id = $(this).data('id');
        
        $.ajax({
            url: "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'show'], '') }}/" + memo_id,
            type: 'GET',
            success: function(response) {
                $('#view_memo_content').html(response);
                $('#view_memo_modal').modal('show');
            },
            error: function(xhr) {
                toastr.error('Error loading memo details.');
            }
        });
    });

    $(document).on('click', '.delete-memo', function() {
        var memo_id = $(this).data('id');
        
        swal({
            title: LANG.sure,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((confirmed) => {
            if (confirmed) {
                $.ajax({
                    url: "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'destroy'], '') }}/" + memo_id,
                    type: 'DELETE',
                    data: {
                        '_token': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            memos_table.ajax.reload();
                            toastr.success(response.msg);
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error deleting memo.');
                    }
                });
            }
        });
    });
});
</script>
@endsection
