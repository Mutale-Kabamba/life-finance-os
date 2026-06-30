<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id', 'employee_id', 'gross_salary', 'napsa_employee',
        'napsa_employer', 'nhima_employee', 'paye', 'other_deductions', 'net_salary',
    ];

    protected $casts = [
        'gross_salary'    => 'decimal:2',
        'napsa_employee'  => 'decimal:2',
        'napsa_employer'  => 'decimal:2',
        'nhima_employee'  => 'decimal:2',
        'paye'            => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_salary'      => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTotalDeductionsAttribute(): float
    {
        return $this->napsa_employee + $this->nhima_employee + $this->paye + $this->other_deductions;
    }
}
