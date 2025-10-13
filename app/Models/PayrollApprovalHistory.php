<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollApprovalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_record_id',
        'user_id',
        'action',
        'comments'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the payroll record that owns this approval history.
     */
    public function payrollRecord()
    {
        return $this->belongsTo(PayrollRecord::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}
