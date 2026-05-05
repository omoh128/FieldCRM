<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id', 'name', 'email', 'role', 'phone', 'status',
    ];

    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }

    public function scopeForOwner($query, $ownerId) { return $query->where('owner_id', $ownerId); }
}