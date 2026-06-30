<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employment extends Model
{
    use HasFactory;

    protected $table = 'employment';

    protected $fillable = [
        'user_id', 'employer_name', 'position', 'employment_type',
        'gross_salary', 'net_salary', 'pay_frequency',
        'start_date', 'end_date', 'is_current',
    ];

    protected $casts = [
        'gross_salary' => 'decimal:2',
        'net_salary'   => 'decimal:2',
        'start_date'   => 'date',
        'end_date'     => 'date',
        'is_current'   => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
