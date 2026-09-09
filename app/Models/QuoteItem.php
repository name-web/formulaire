<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id',
        'designation',
        'description',
        'quantity',
        'unit_price',
        'total',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'total' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
