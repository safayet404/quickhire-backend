<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Hash;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'user' => [
                    'name'     => 'Nomad Inc.',
                    'email'    => 'nomad@example.com',
                    'password' => Hash::make('password'),
                    'role'     => 'employer',
                ],
                'profile' => [
                    'company_name' => 'Nomad',
                    'logo_url'     => 'https://logo.clearbit.com/nomad.com',
                    'website'      => 'https://nomad.com',
                    'industry'     => 'Technology',
                    'company_size' => '51-200',
                    'founded_year' => '2015',
                    'location'     => 'San Francisco, USA',
                    'description'  => 'Nomad is a remote-first technology company building tools for distributed teams.',
                    'linkedin'     => 'https://linkedin.com/company/nomad',
                    'is_verified'  => true,
                ],
            ],
            [
                'user' => [
                    'name'     => 'Stripe HR',
                    'email'    => 'stripe@example.com',
                    'password' => Hash::make('password'),
                    'role'     => 'employer',
                ],
                'profile' => [
                    'company_name' => 'Stripe',
                    'logo_url'     => 'https://logo.clearbit.com/stripe.com',
                    'website'      => 'https://stripe.com',
                    'industry'     => 'Fintech',
                    'company_size' => '1000+',
                    'founded_year' => '2010',
                    'location'     => 'San Francisco, USA',
                    'description'  => 'Stripe is a technology company that builds economic infrastructure for the internet.',
                    'linkedin'     => 'https://linkedin.com/company/stripe',
                    'is_verified'  => true,
                ],
            ],
            [
                'user' => [
                    'name'     => 'Vercel HR',
                    'email'    => 'vercel@example.com',
                    'password' => Hash::make('password'),
                    'role'     => 'employer',
                ],
                'profile' => [
                    'company_name' => 'Vercel',
                    'logo_url'     => 'https://logo.clearbit.com/vercel.com',
                    'website'      => 'https://vercel.com',
                    'industry'     => 'Cloud Infrastructure',
                    'company_size' => '51-200',
                    'founded_year' => '2015',
                    'location'     => 'Remote',
                    'description'  => 'Vercel is the platform for frontend developers, providing the speed and reliability innovators need to create at the moment of inspiration.',
                    'linkedin'     => 'https://linkedin.com/company/vercel',
                    'is_verified'  => true,
                ],
            ],
        ];

        foreach ($companies as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                $data['user']
            );

            CompanyProfile::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($data['profile'], ['user_id' => $user->id])
            );
        }

        $this->command->info('✅ Company profiles seeded successfully!');
    }
}
