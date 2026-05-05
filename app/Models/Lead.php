<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'source',
        'status',       // New | Quoted | Won | Lost | Customer
        'priority',
        'value',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'lead_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    public function scopeCustomers($query)
    {
        return $query->where('status', 'Customer');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name',  'like', "%{$term}%")
              ->orWhere('email',      'like', "%{$term}%")
              ->orWhere('company',    'like', "%{$term}%")
              ->orWhere('phone',      'like', "%{$term}%");
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function isCustomer(): bool
    {
        return $this->status === 'Customer';
    }
}