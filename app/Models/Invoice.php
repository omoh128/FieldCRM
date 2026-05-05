<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id', 'job_id', 'lead_id', 'invoice_number',
        'amount', 'paid_amount', 'status', 'due_date', 'paid_date',
        'notes', 'stripe_payment_intent_id', 'stripe_checkout_session_id',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date'    => 'date',
        'paid_date'   => 'date',
    ];

    public function owner()    { return $this->belongsTo(User::class, 'owner_id'); }
    public function job()      { return $this->belongsTo(Job::class); }
    public function lead()     { return $this->belongsTo(Lead::class); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function scopeForOwner($query, $ownerId) { return $query->where('owner_id', $ownerId); }
    public function scopeStatus($query, $status)    { return $query->where('status', $status); }

    public function isPaid(): bool     { return $this->status === 'paid'; }
    public function isOverdue(): bool  { return $this->due_date && $this->due_date->isPast() && !$this->isPaid(); }
    public function balance(): float   { return max(0, $this->amount - $this->paid_amount); }

    // Auto-generate invoice number
    public static function generateNumber(int $ownerId): string
    {
        $count = static::where('owner_id', $ownerId)->count() + 1;
        return 'INV-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}