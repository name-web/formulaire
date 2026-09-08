<?php

namespace App\Models;

use Database\Factories\ClientCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientComment extends Model
{
    /** @use HasFactory<ClientCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'content',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
