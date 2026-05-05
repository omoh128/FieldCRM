<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'lead_id',
        'title',
        'type',
        'status',
        'assigned_to',
        'scheduled_date',
        'due_date',
        'value',
        'progress',
        'address',
        'notes',
    ];

    protected $casts = [
        'value'          => 'decimal:2',
        'progress'       => 'integer',
        'scheduled_date' => 'date',
        'due_date'       => 'date',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function scopeForOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title',        'like', "%{$term}%")
              ->orWhere('assigned_to', 'like', "%{$term}%")
              ->orWhere('address',     'like', "%{$term}%")
              ->orWhereHas('lead', fn($l) =>
                  $l->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
              );
        });
    }
}
