<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class MemoAttachment extends Model
{
    protected $table = 'essentials_memo_attachments';
    protected $guarded = ['id'];

    public function memo()
    {
        return $this->belongsTo(Memo::class);
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIsImageAttribute()
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);
    }

    public function getIsPdfAttribute()
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getIsOfficeDocAttribute()
    {
        $office_types = [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];
        return in_array($this->mime_type, $office_types);
    }
}
