<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;
    protected $table = 'job_listings';


    protected $fillable = [
        'title',
        'company',
        'company_logo',
        'location',
        'category',
        'type',
        'salary_min',
        'salary_max',
        'description',
        'requirements',
        'is_featured',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'requirements' => 'array',
        'salary_min' => 'integer',
        'salary_max' => 'integer',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            'user_id',
            true
        );
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
