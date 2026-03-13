<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = ['name', 'email', 'password', 'role', 'avatar'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isEmployer(): bool { return $this->role === 'employer'; }
    public function isSeeker(): bool   { return $this->role === 'seeker'; }

    public function seekerProfile()  { return $this->hasOne(SeekerProfile::class); }
    public function companyProfile() { return $this->hasOne(CompanyProfile::class); }
    public function jobListings()    { return $this->hasMany(Job::class, 'user_id'); }
    public function applications()   { return $this->hasMany(Application::class, 'user_id'); }
    public function savedJobs()      { return $this->hasMany(SavedJob::class); }
}
