<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExitPass extends Model
{
    /**
     * Database table.
     *
     * @var string
     */
    protected $table = 'exit_passes';

    /**
     * Mass assignable fields.
     *
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'exit_date',
        'exit_time',
        'expected_return_time',
        'reason',
        'destination',
        'remarks',
        'status',
        'approved_by',
        'approved_at',
        'admin_remark',
    ];

    /**
     * Attribute casting.
     *
     * @var array
     */
    protected $casts = [
        'exit_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Employee who requested the Exit Pass.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * User who approved/rejected the Exit Pass.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}