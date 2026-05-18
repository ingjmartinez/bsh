<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotSession extends Model
{
    protected $fillable = [
        'account',
        'phone',
        'step',
        'context',
        'last_message',
        'last_interaction_at',
        'message_count',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'last_interaction_at' => 'datetime',
            'message_count' => 'integer',
        ];
    }
}
