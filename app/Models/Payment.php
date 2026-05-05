<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'owner_id', 'amount', 'method',
        'transaction_id', 'status', 'payment_date', 'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function owner()   { return $this->belongsTo(User::class, 'owner_id'); }

    public function scopeForOwner($query, $ownerId) { return $query->where('owner_id', $ownerId); }
}