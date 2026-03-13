<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'name',
        'email',
        'resume_link',
        'cover_note',
        'status',
        'status_note',
    ];

    const STATUSES = ['pending', 'reviewed', 'accepted', 'rejected'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Status badge color helper (used in API responses)
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'yellow',
            'reviewed' => 'blue',
            'accepted' => 'green',
            'rejected' => 'red',
            default    => 'gray',
        };
    }

    protected $appends = ['status_color'];
}
