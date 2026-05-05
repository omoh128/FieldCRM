<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isOwner(): bool { return $this->role === 'owner'; }

    public function leads()       { return $this->hasMany(Lead::class, 'owner_id'); }
    public function jobs()        { return $this->hasMany(Job::class,  'owner_id'); }
    public function invoices()    { return $this->hasMany(Invoice::class, 'owner_id'); }
    public function teamMembers() { return $this->hasMany(TeamMember::class, 'owner_id'); }
    public function settings()    { return $this->hasMany(Setting::class, 'user_id'); }
}