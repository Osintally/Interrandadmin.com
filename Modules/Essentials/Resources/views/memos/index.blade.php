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
<<<<<<< HEAD
                <input type="hidden" name="_method" id="form_method" value="POST">
                <input type="hidden" name="memo_id" id="memo_id" value="">
=======
>>>>>>> 8bb22bf (Implement corporate memos system)
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

<<<<<<< HEAD
    // Initialize TinyMCE when modal is shown
    function initTinyMCE() {
        // Destroy existing instance if it exists
        if (tinymce.get('memo_body')) {
            tinymce.get('memo_body').destroy();
        }
        
        tinymce.init({
            selector: '#memo_body',
            height: 300,
            plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
                editor.on('init', function () {
                    // Remove required attribute from original textarea to prevent validation issues
                    $('#memo_body').removeAttr('required');
                });
            }
        });
    }
    
    // Destroy TinyMCE when modal is hidden
    $('#compose_memo_modal').on('hidden.bs.modal', function () {
        if (tinymce.get('memo_body')) {
            tinymce.get('memo_body').destroy();
        }
=======
    tinymce.init({
        selector: '#memo_body',
        height: 300,
        plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
>>>>>>> 8bb22bf (Implement corporate memos system)
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
<<<<<<< HEAD
        resetMemoForm();
        $('.modal-title').text('@lang("essentials::lang.compose_memo")');
        $('#form_method').val('POST');
        $('#memo_id').val('');
        $('#compose_memo_modal').modal('show');
        
        // Initialize TinyMCE after modal is shown
        setTimeout(function() {
            initTinyMCE();
        }, 300);
    });
    
    function resetMemoForm() {
        $('#compose_memo_form')[0].reset();
        $('#to_recipients, #cc_recipients, #bcc_recipients').val(null).trigger('change');
        
        // Clear TinyMCE content if it exists
        if (tinymce.get('memo_body')) {
            tinymce.get('memo_body').setContent('');
        }
        
        // Clear attachment preview
        $('#attachment_preview').empty();
        
        // Reset form method and memo ID
        $('#form_method').val('POST');
        $('#memo_id').val('');
    }
=======
        $('#compose_memo_form')[0].reset();
        $('#to_recipients, #cc_recipients, #bcc_recipients').val(null).trigger('change');
        tinymce.get('memo_body').setContent('');
        $('#attachment_preview').empty();
        $('#compose_memo_modal').modal('show');
    });
>>>>>>> 8bb22bf (Implement corporate memos system)

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
<<<<<<< HEAD
        
        // Save TinyMCE content before validation
        if (tinymce.get('memo_body')) {
            tinymce.triggerSave();
        }
        
        // Validate required fields
        var isValid = true;
        
        // Validate subject
        var subject = $('[name="subject"]').val();
        if (!subject || subject.trim() === '') {
            toastr.error('Subject is required.');
            isValid = false;
        }
        
        // Validate body content from TinyMCE
        var bodyContent = '';
        if (tinymce.get('memo_body')) {
            bodyContent = tinymce.get('memo_body').getContent();
        } else {
            bodyContent = $('[name="body"]').val();
        }
        
        if (!bodyContent || bodyContent.trim() === '' || bodyContent.trim() === '<p></p>' || bodyContent.trim() === '<p>&nbsp;</p>') {
            toastr.error('Message is required.');
            isValid = false;
        }
        
        // Validate recipients
        if ($('#to_recipients').val() === null || $('#to_recipients').val().length === 0) {
            toastr.error('At least one recipient is required.');
            isValid = false;
        }
        
        if (!isValid) {
            return false;
        }
=======
        tinymce.triggerSave();
>>>>>>> 8bb22bf (Implement corporate memos system)
        
        var formData = new FormData(this);
        formData.append('send', '1');
        
<<<<<<< HEAD
        // Determine URL and method based on form mode
        var url, method;
        var memoId = $('#memo_id').val();
        
        if (memoId) {
            url = "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'update'], '') }}/" + memoId;
            method = 'POST'; // Laravel handles PUT via _method field
        } else {
            url = "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'store']) }}";
            method = 'POST';
        }
        
        // Disable submit button to prevent double submission
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
=======
        $.ajax({
            url: "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'store']) }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
>>>>>>> 8bb22bf (Implement corporate memos system)
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
<<<<<<< HEAD
                console.error('Send memo error:', xhr);
                var errorMessage = 'An error occurred while sending the memo.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join(', ');
                } else if (xhr.status === 422) {
                    errorMessage = 'Validation error. Please check your input.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please try again later.';
                }
                toastr.error(errorMessage);
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalText);
=======
                toastr.error('An error occurred while sending the memo.');
>>>>>>> 8bb22bf (Implement corporate memos system)
            }
        });
    });

    $('#save_draft_btn').click(function() {
<<<<<<< HEAD
        // Save TinyMCE content before processing
        if (tinymce.get('memo_body')) {
            tinymce.triggerSave();
        }
        
        var formData = new FormData($('#compose_memo_form')[0]);
        
        // Determine URL and method based on form mode
        var url, method;
        var memoId = $('#memo_id').val();
        
        if (memoId) {
            url = "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'update'], '') }}/" + memoId;
            method = 'POST'; // Laravel handles PUT via _method field
        } else {
            url = "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'store']) }}";
            method = 'POST';
        }
        
        // Disable save draft button
        var saveBtn = $(this);
        var originalText = saveBtn.html();
        saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
=======
        tinymce.triggerSave();
        
        var formData = new FormData($('#compose_memo_form')[0]);
        
        $.ajax({
            url: "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'store']) }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
>>>>>>> 8bb22bf (Implement corporate memos system)
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
<<<<<<< HEAD
                console.error('Save draft error:', xhr);
                var errorMessage = 'An error occurred while saving the draft.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join(', ');
                } else if (xhr.status === 422) {
                    errorMessage = 'Validation error. Please check your input.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please try again later.';
                }
                toastr.error(errorMessage);
            },
            complete: function() {
                // Re-enable save draft button
                saveBtn.prop('disabled', false).html(originalText);
=======
                toastr.error('An error occurred while saving the draft.');
>>>>>>> 8bb22bf (Implement corporate memos system)
            }
        });
    });

<<<<<<< HEAD
    // Handle view memo button click
    $(document).on('click', '.view-memo', function() {
        var memo_id = $(this).data('id');
        var $button = $(this);
        
        // Disable button to prevent multiple clicks
        $button.prop('disabled', true);
=======
    $(document).on('click', '.view-memo', function() {
        var memo_id = $(this).data('id');
>>>>>>> 8bb22bf (Implement corporate memos system)
        
        $.ajax({
            url: "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'show'], '') }}/" + memo_id,
            type: 'GET',
<<<<<<< HEAD
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
=======
>>>>>>> 8bb22bf (Implement corporate memos system)
            success: function(response) {
                $('#view_memo_content').html(response);
                $('#view_memo_modal').modal('show');
            },
            error: function(xhr) {
<<<<<<< HEAD
                console.error('View memo error:', xhr);
                var errorMessage = 'Error loading memo details.';
                
                if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to view this memo.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Memo not found.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                toastr.error(errorMessage);
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false);
=======
                toastr.error('Error loading memo details.');
>>>>>>> 8bb22bf (Implement corporate memos system)
            }
        });
    });

<<<<<<< HEAD
    $(document).on('click', '.edit-memo', function() {
        var memo_id = $(this).data('id');
        
        $.ajax({
            url: "{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'edit'], '') }}/" + memo_id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var memo = response.memo;
                    var users = response.users;
                    
                    // Reset form first
                    resetMemoForm();
                    
                    // Set form to edit mode
                    $('.modal-title').text('@lang("essentials::lang.edit_memo")');
                    $('#form_method').val('PUT');
                    $('#memo_id').val(memo.id);
                    
                    // Populate form fields
                    $('[name="subject"]').val(memo.subject);
                    
                    // Populate recipients first
                    populateRecipients(memo.recipients);
                    
                    // Show modal first
                    $('#compose_memo_modal').modal('show');
                    
                    // Initialize TinyMCE and set content after modal is shown
                    setTimeout(function() {
                        initTinyMCE();
                        // Wait for TinyMCE to be ready before setting content
                        setTimeout(function() {
                            if (tinymce.get('memo_body')) {
                                tinymce.get('memo_body').setContent(memo.body || '');
                            }
                        }, 500);
                    }, 300);
                } else {
                    toastr.error(response.msg || 'Error loading memo for editing.');
                }
            },
            error: function(xhr) {
                console.error('Edit memo error:', xhr);
                toastr.error('Error loading memo for editing.');
            }
        });
    });
    
    function populateRecipients(recipients) {
        var toRecipients = [];
        var ccRecipients = [];
        var bccRecipients = [];
        
        recipients.forEach(function(recipient) {
            var option = new Option(recipient.user.first_name + ' ' + recipient.user.last_name, recipient.user.id, true, true);
            
            if (recipient.recipient_type === 'to') {
                $('#to_recipients').append(option);
                toRecipients.push(recipient.user.id);
            } else if (recipient.recipient_type === 'cc') {
                $('#cc_recipients').append(option);
                ccRecipients.push(recipient.user.id);
            } else if (recipient.recipient_type === 'bcc') {
                $('#bcc_recipients').append(option);
                bccRecipients.push(recipient.user.id);
            }
        });
        
        $('#to_recipients').val(toRecipients).trigger('change');
        $('#cc_recipients').val(ccRecipients).trigger('change');
        $('#bcc_recipients').val(bccRecipients).trigger('change');
    }

=======
>>>>>>> 8bb22bf (Implement corporate memos system)
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
