<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'name',
        'email',
        'resume_link',
        'cover_note',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
