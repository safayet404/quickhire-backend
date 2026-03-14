<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SavedJob;
use App\Models\User;
use App\Models\Job;

class SavedJobSeeder extends Seeder
{
    public function run(): void
    {
        $seekers = User::where('role', 'seeker')->get();
        $jobs    = Job::all();

        if ($seekers->isEmpty() || $jobs->isEmpty()) {
            $this->command->warn('⚠️  No seekers or jobs found.');
            return;
        }

        $count = 0;
        foreach ($seekers as $seeker) {
            $toSave = $jobs->random(min(5, $jobs->count()));
            foreach ($toSave as $job) {
                SavedJob::firstOrCreate([
                    'user_id' => $seeker->id,
                    'job_id'  => $job->id,
                ]);
                $count++;
            }
        }

        $this->command->info("✅ Saved jobs seeded: {$count} bookmarks created.");
    }
}
