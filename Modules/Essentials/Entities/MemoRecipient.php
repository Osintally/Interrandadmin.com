<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class MemoRecipient extends Model
{
    protected $table = 'essentials_memo_recipients';
    protected $guarded = ['id'];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function memo()
    {
        return $this->belongsTo(Memo::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}
