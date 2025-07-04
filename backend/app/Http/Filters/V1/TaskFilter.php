<?php

namespace App\Http\Filters\V1;

class TaskFilter extends QueryFilter
{
    protected $sortable = [
        'title',
        'status',
        'priority',
        'dueDate' => 'due_date',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    public function include($value)
    {
        return $this->builder->with($value);
    }

    public function status($value)
    {
        return $this->builder->whereIn('status', explode(',', $value));
    }

    public function priority($value)
    {
        return $this->builder->whereIn('priority', explode(',', $value));
    }

    public function title($value)
    {
        $strLike = str_replace('*', '%', $value);
        return $this->builder->where('title', 'like', $strLike);
    }

    public function description($value)
    {
        $strLike = str_replace('*', '%', $value);
        return $this->builder->where('description', 'like', $strLike);
    }

    public function dueDate($value)
    {
        $dates = explode(',', $value);
        if (count($dates) > 1) {
            return $this->builder->whereBetween('due_date', $dates);
        }

        return $this->builder->whereDate('due_date', $value);
    }

    public function dueBefore($value)
    {
        return $this->builder->where('due_date', '<=', $value);
    }

    public function search($value)
    {
        $strLike = str_replace('*', '%', $value);
        return $this->builder->where(function ($query) use ($strLike) {
            $query->where('title', 'like', $strLike)
                  ->orWhere('description', 'like', $strLike);
        });
    }

    public function createdAt($value)
    {
        $dates = explode(',', $value);
        if (count($dates) > 1) {
            return $this->builder->whereBetween('created_at', $dates);
        }

        return $this->builder->whereDate('created_at', $value);
    }

    public function updatedAt($value)
    {
        $dates = explode(',', $value);
        if (count($dates) > 1) {
            return $this->builder->whereBetween('updated_at', $dates);
        }

        return $this->builder->whereDate('updated_at', $value);
    }

    public function userId($value)
    {
        return $this->builder->whereIn('user_id', explode(',', $value));
    }
} 