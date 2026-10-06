<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeEducation extends Model
{
    protected $table = 'employee_educations';

    protected $fillable = [
        'level',
        'degree',
        'institution',
        'board_university',
        'year_of_passing',
        'grade',
        'certificate_path',
        'certificate_original_name',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
