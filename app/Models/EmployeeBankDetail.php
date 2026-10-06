<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBankDetail extends Model
{
    protected $fillable = [
        'employee_id',
        'account_holder',
        'account_number_encrypted',
        'bank_name',
        'ifsc_code',
        'branch',
        'account_type',
    ];

    protected $hidden = ['account_number_encrypted'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
