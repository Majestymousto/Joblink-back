<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'sender_id',
        'content',
        'read',
    ];

    protected $casts = [
        'read' => 'boolean',
    ];

    // Relations
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}