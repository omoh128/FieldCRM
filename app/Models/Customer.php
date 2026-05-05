<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'address', // Added in case your lead form uses it
        'status',  // Added so your scopeStatus works
        'type',    // Added so your scopeType works
        'lifetime_value', // ADD THIS LINE
    ];

    protected $casts = [
        'tags'           => 'array',
        'lifetime_value' => 'decimal:2',
        'last_contact'   => 'date',
    ];

    // --- Accessors ---

    /**
     * Get the customer's full name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // --- Relationships ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    // --- Scopes ---

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            // Updated to search first_name and last_name instead of 'name'
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('company', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    // --- Helpers ---

    /**
     * Recalculate lifetime_value and total_jobs from actual jobs.
     */
    public function recalculateStats(): void
    {
        $this->total_jobs     = $this->jobs()->count();
        $this->lifetime_value = $this->jobs()->where('status', 'Completed')->sum('value');
        $this->last_contact   = now();
        $this->save();
    }
}