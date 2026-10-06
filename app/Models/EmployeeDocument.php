<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'original_name',
        'storage_path',
        'mime_type',
        'file_size',
    ];

    protected $hidden = ['storage_path'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
