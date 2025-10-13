<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Memo extends Model
{
    use LogsActivity;

    protected $table = 'essentials_memos';
    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['subject', 'body', 'status'])
            ->logOnlyDirty();
    }

    public function sender()
    {
        return $this->belongsTo(\App\User::class, 'sender_id');
    }

    public function recipients()
    {
        return $this->hasMany(MemoRecipient::class);
    }

    public function attachments()
    {
        return $this->hasMany(MemoAttachment::class);
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function toRecipients()
    {
        return $this->recipients()->where('recipient_type', 'to');
    }

    public function ccRecipients()
    {
        return $this->recipients()->where('recipient_type', 'cc');
    }

    public function bccRecipients()
    {
        return $this->recipients()->where('recipient_type', 'bcc');
    }

    public function getRecipientsListAttribute()
    {
        $recipients = [];
        foreach ($this->recipients as $recipient) {
            $recipients[$recipient->recipient_type][] = $recipient->user->getUserFullNameAttribute();
        }
        return $recipients;
    }

    public function getHasAttachmentsAttribute()
    {
        return $this->attachments()->count() > 0;
    }
}
