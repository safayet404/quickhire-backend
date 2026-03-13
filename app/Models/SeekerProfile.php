<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeekerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'headline',
        'bio',
        'phone',
        'location',
        'website',
        'linkedin',
        'github',
        'resume_url',
        'skills',
        'experience',
        'education',
        'open_to_work',
    ];

    protected $casts = [
        'skills'       => 'array',
        'experience'   => 'array',
        'education'    => 'array',
        'open_to_work' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
