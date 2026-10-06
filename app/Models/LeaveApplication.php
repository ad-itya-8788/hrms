<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $table = 'leave_applications';

    protected $fillable = [
        'employee_id',
        'leave_type',
        'from_date',
        'to_date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'admin_remark',
    ];

    protected $dates = [
        'from_date',
        'to_date',
        'approved_at',
        'created_at',
        'updated_at',
    ];

    public function employee()
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id'
        );
    }

    public function approver()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }
}