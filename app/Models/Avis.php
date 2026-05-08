<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $table = 'avis';

    protected $fillable = [
        'user_id',
        'note',
        'commentaire',
        'context',
        'status',
    ];

    protected $casts = [
        'note'       => 'integer',
        'created_at' => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}