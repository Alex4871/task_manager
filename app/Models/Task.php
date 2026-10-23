<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    public const string CREATED_AT = 'create_date';

    public const string UPDATED_AT = 'updated_at';

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'create_date',
        'status',
        'priority',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'immutable_datetime',
            'create_date' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
        ];
    }
}
