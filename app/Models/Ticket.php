<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Un biglietto appartiene a un ordine
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function changes()
    {
        return $this->hasMany(TicketChange::class)->latest();
    }
}
