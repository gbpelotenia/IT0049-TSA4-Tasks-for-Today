<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    public const STATUSES = ['pending' => 'Pending', 'in_progress' => 'In progress', 'done' => 'Done'];

    public const PRIORITIES = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'];

    protected $fillable = ['title', 'description', 'due_date', 'priority', 'status'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'is_archived' => 'boolean'];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_archived', false);
    }
}
