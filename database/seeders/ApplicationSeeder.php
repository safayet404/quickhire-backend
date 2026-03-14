<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $jobs    = Job::all();
        $seekers = User::where('role', 'seeker')->get();

        if ($jobs->isEmpty() || $seekers->isEmpty()) {
            $this->command->warn('⚠️  No jobs or seekers found. Run JobSeeder and SeekerProfileSeeder first.');
            return;
        }

        $statuses   = ['pending', 'pending', 'pending', 'reviewed', 'accepted', 'rejected'];
        $coverNotes = [
            "I'm excited about this opportunity and believe my background is a strong match for the role.",
            "I've been following your company for some time and would love to contribute to your team.",
            "My experience directly aligns with the requirements listed and I'm eager to bring my skills to this position.",
            "I'm passionate about this field and have built several projects that demonstrate my capabilities.",
            "I believe I can make an immediate impact given my background in similar roles.",
        ];

        $applications = [];

        foreach ($seekers as $seeker) {
            // Each seeker applies to 3-5 random jobs
            $appliedJobs = $jobs->random(min(4, $jobs->count()));

            foreach ($appliedJobs as $job) {
                $applications[] = [
                    'job_id'      => $job->id,
                    'user_id'     => $seeker->id,
                    'name'        => $seeker->name,
                    'email'       => $seeker->email,
                    'resume_link' => "https://example.com/resumes/{$seeker->id}.pdf",
                    'cover_note'  => $coverNotes[array_rand($coverNotes)],
                    'status'      => $statuses[array_rand($statuses)],
                    'status_note' => null,
                    'created_at'  => now()->subDays(rand(1, 30)),
                    'updated_at'  => now()->subDays(rand(0, 5)),
                ];
            }
        }

        // Deduplicate by job_id + email (unique constraint)
        $seen = [];
        $unique = [];
        foreach ($applications as $app) {
            $key = $app['job_id'] . '_' . $app['email'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $app;
            }
        }

        foreach ($unique as $app) {
            Application::create($app);
        }

        $this->command->info('✅ Applications seeded: ' . count($unique) . ' applications created.');
    }
}
