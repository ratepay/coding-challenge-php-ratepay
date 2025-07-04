<?php

namespace App\Models;

use App\Http\Filters\V1\TaskFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Http\Filters\V1\QueryFilter;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Apply filters to the query.
     */
    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }

    /**
     * Scope a query to only include tasks of a given status.
     */
    /*
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    */

    /**
     * Scope a query to only include tasks of a given priority.
     */
    /*
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
    */

    /**
     * Scope a query to only include tasks due before a given date.
     */
    /*
    public function scopeDueBefore($query, $date)
    {
        return $query->where('due_date', '<=', $date);
    }
    */

    /**
     * Scope a query to search tasks by title and description.
     */
    /*
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        });
    }
    */
}
