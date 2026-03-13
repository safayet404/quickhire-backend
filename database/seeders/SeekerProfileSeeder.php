<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SeekerProfile;
use Illuminate\Support\Facades\Hash;

class SeekerProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Create seeker users with profiles
        $seekers = [
            [
                'user' => [
                    'name'     => 'Alex Johnson',
                    'email'    => 'alex@example.com',
                    'password' => Hash::make('password'),
                    'role'     => 'seeker',
                ],
                'profile' => [
                    'headline'     => 'Senior Frontend Developer',
                    'bio'          => 'Passionate frontend developer with 5+ years of experience building modern web applications with React, Vue, and TypeScript.',
                    'phone'        => '+1 555 000 0001',
                    'location'     => 'New York, USA',
                    'website'      => 'https://alexjohnson.dev',
                    'linkedin'     => 'https://linkedin.com/in/alexjohnson',
                    'github'       => 'https://github.com/alexjohnson',
                    'resume_url'   => 'https://example.com/resumes/alex.pdf',
                    'skills'       => ['React', 'TypeScript', 'Vue.js', 'Tailwind CSS', 'Node.js'],
                    'open_to_work' => true,
                    'experience'   => [
                        [
                            'title'       => 'Senior Frontend Developer',
                            'company'     => 'TechCorp',
                            'location'    => 'New York, USA',
                            'start_date'  => '2021-01',
                            'end_date'    => null,
                            'current'     => true,
                            'description' => 'Leading frontend development for the main product.',
                        ],
                        [
                            'title'       => 'Frontend Developer',
                            'company'     => 'StartupXYZ',
                            'location'    => 'Remote',
                            'start_date'  => '2019-03',
                            'end_date'    => '2020-12',
                            'current'     => false,
                            'description' => 'Built reusable component library used across 3 products.',
                        ],
                    ],
                    'education' => [
                        [
                            'degree'     => 'B.Sc. Computer Science',
                            'school'     => 'New York University',
                            'start_year' => '2015',
                            'end_year'   => '2019',
                        ],
                    ],
                ],
            ],
            [
                'user' => [
                    'name'     => 'Sara Ahmed',
                    'email'    => 'sara@example.com',
                    'password' => Hash::make('password'),
                    'role'     => 'seeker',
                ],
                'profile' => [
                    'headline'     => 'UX Designer & Researcher',
                    'bio'          => 'UX designer focused on creating intuitive, accessible user experiences for web and mobile.',
                    'phone'        => '+1 555 000 0002',
                    'location'     => 'San Francisco, USA',
                    'linkedin'     => 'https://linkedin.com/in/saraahmed',
                    'resume_url'   => 'https://example.com/resumes/sara.pdf',
                    'skills'       => ['Figma', 'User Research', 'Prototyping', 'Wireframing', 'Adobe XD'],
                    'open_to_work' => true,
                    'experience'   => [
                        [
                            'title'       => 'UX Designer',
                            'company'     => 'DesignStudio',
                            'location'    => 'San Francisco, USA',
                            'start_date'  => '2020-06',
                            'end_date'    => null,
                            'current'     => true,
                            'description' => 'Designing end-to-end user experiences for SaaS products.',
                        ],
                    ],
                    'education' => [
                        [
                            'degree'     => 'B.A. Interaction Design',
                            'school'     => 'California College of the Arts',
                            'start_year' => '2016',
                            'end_year'   => '2020',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($seekers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                $data['user']
            );

            SeekerProfile::updateOrCreate(
                ['user_id' => $user->id],
                $data['profile']
            );
        }

        $this->command->info('✅ Seeker profiles seeded successfully!');
    }
}
