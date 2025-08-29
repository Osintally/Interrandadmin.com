<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">{{ $memo->subject }}</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>@lang('essentials::lang.from'):</strong> {{ $memo->sender->getUserFullNameAttribute() }}
                        </div>
                        <div class="col-md-6">
                            <strong>@lang('essentials::lang.date'):</strong> {{ $memo->created_at->format('M d, Y H:i') }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            @if($memo->toRecipients->count() > 0)
                                <div class="mb-2">
                                    <strong>@lang('essentials::lang.to'):</strong>
                                    @foreach($memo->toRecipients as $recipient)
                                        <span class="label label-primary">{{ $recipient->user->getUserFullNameAttribute() }}</span>
                                    @endforeach
                                </div>
                            @endif
                            
                            @if($memo->ccRecipients->count() > 0)
                                <div class="mb-2">
                                    <strong>@lang('essentials::lang.cc'):</strong>
                                    @foreach($memo->ccRecipients as $recipient)
                                        <span class="label label-info">{{ $recipient->user->getUserFullNameAttribute() }}</span>
                                    @endforeach
                                </div>
                            @endif
                            
                            @php
                                $user_id = request()->session()->get('user.id');
                                $is_sender = $memo->sender_id == $user_id;
                            @endphp
                            
                            @if($is_sender && $memo->bccRecipients->count() > 0)
                                <div class="mb-2">
                                    <strong>@lang('essentials::lang.bcc'):</strong>
                                    @foreach($memo->bccRecipients as $recipient)
                                        <span class="label label-default">{{ $recipient->user->getUserFullNameAttribute() }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <strong>@lang('essentials::lang.subject'):</strong> {{ $memo->subject }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="memo-body">
                                {!! $memo->body !!}
                            </div>
                        </div>
                    </div>
                    
                    @if($memo->attachments->count() > 0)
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <strong>@lang('essentials::lang.attachments'):</strong>
                                <div class="attachments-list mt-2">
                                    @foreach($memo->attachments as $attachment)
                                        <div class="attachment-item mb-2 p-2 border rounded">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <i class="fa fa-file"></i>
                                                    <strong>{{ $attachment->filename }}</strong>
                                                    <small class="text-muted">({{ $attachment->getFileSizeFormattedAttribute() }})</small>
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    <a href="{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'downloadAttachment'], [$memo->id, $attachment->id]) }}" 
                                                       class="btn btn-xs btn-primary">
                                                        <i class="fa fa-download"></i> @lang('essentials::lang.download')
                                                    </a>
                                                    @if($attachment->is_image)
                                                        <button class="btn btn-xs btn-info preview-image" 
                                                                data-url="{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'downloadAttachment'], [$memo->id, $attachment->id]) }}">
                                                            <i class="fa fa-eye"></i> @lang('essentials::lang.preview')
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if($attachment->is_image)
                                                <div class="image-preview mt-2" style="display: none;">
                                                    <img src="{{ action([\Modules\Essentials\Http\Controllers\MemoController::class, 'downloadAttachment'], [$memo->id, $attachment->id]) }}" 
                                                         class="img-responsive" style="max-height: 300px;">
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('essentials::lang.close')</button>
</div>

<script>
$(document).ready(function() {
    $('.preview-image').click(function() {
        var $this = $(this);
        var $preview = $this.closest('.attachment-item').find('.image-preview');
        
        if ($preview.is(':visible')) {
            $preview.hide();
            $this.html('<i class="fa fa-eye"></i> @lang("essentials::lang.preview")');
        } else {
            $preview.show();
            $this.html('<i class="fa fa-eye-slash"></i> @lang("essentials::lang.hide")');
        }
    });
});
</script>
