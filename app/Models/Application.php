<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
    'candidate_id',
    'job_offer_id',
    'status',
    'cv_path',
    'buildcvpro_cv_id',
    'message',
];

    // Relations
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function jobOffer()
    {
        return $this->belongsTo(JobOffer::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}