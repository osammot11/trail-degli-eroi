<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketChange extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount_difference' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
