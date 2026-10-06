<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'department_id',
        'employee_type_id',
        'employee_role_id',
        'manager_id',
        'created_by',
        'employment_status',
        'is_active',
        'joining_date',
        'address_line',
        'city',
        'state',
        'postal_code',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
    ];

    protected $dates = ['date_of_birth', 'joining_date'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function employeeType()
    {
        return $this->belongsTo(EmployeeType::class);
    }

    public function employeeRole()
    {
        return $this->belongsTo(EmployeeRole::class);
    }

    public function manager()
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    public function bankDetails()
    {
        return $this->hasOne(EmployeeBankDetail::class);
    }

    public function experiences()
    {
        return $this->hasMany(EmployeeExperience::class)->orderByDesc('start_date');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class)->orderByDesc('created_at');
    }
}
