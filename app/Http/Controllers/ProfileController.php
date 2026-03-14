<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SeekerProfile;
use App\Models\CompanyProfile;

class ProfileController extends Controller
{
    // ── GET /api/profile ─────────────────────────────────────
    // Returns the authenticated user's profile
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->isSeeker()) {
            $profile = $user->seekerProfile;
            return response()->json([
                'user'    => $this->formatUser($user),
                'profile' => $profile,
            ]);
        }

        if ($user->isEmployer()) {
            $profile = $user->companyProfile;
            return response()->json([
                'user'    => $this->formatUser($user),
                'profile' => $profile,
            ]);
        }

        return response()->json(['user' => $this->formatUser($user), 'profile' => null]);
    }

    // ── PUT /api/profile ──────────────────────────────────────
    // Create or update profile depending on role
    public function update(Request $request)
    {
        $user = $request->user();

        if ($user->isSeeker()) {
            return $this->updateSeekerProfile($request, $user);
        }

        if ($user->isEmployer()) {
            return $this->updateCompanyProfile($request, $user);
        }

        return response()->json(['message' => 'Admins do not have profiles.'], 403);
    }

    // ── GET /api/companies ────────────────────────────────────
    public function companies(Request $request)
    {
        $query = CompanyProfile::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(
                fn($q) => $q
                    ->where('company_name', 'like', "%{$s}%")
                    ->orWhere('industry', 'like', "%{$s}%")
                    ->orWhere('location', 'like', "%{$s}%")
            );
        }

        if ($request->filled('industry')) {
            $query->where('industry', $request->industry);
        }

        $companies = $query->paginate(12);

        // Attach job count to each company
        $items = collect($companies->items())->map(function ($company) {
            $company->jobs_count = \App\Models\Job::where('user_id', $company->user_id)
                ->where('is_active', true)->count();
            return $company;
        });

        return response()->json([
            'success'    => true,
            'data'       => $items,
            'pagination' => [
                'total'        => $companies->total(),
                'current_page' => $companies->currentPage(),
                'last_page'    => $companies->lastPage(),
            ],
        ]);
    }

    // ── GET /api/companies/{id} ───────────────────────────────
    public function companyShow($id)
    {
        $company = CompanyProfile::with('user')->findOrFail($id);

        // Get active jobs for this company
        $jobs = \App\Models\Job::where('user_id', $company->user_id)
            ->where('is_active', true)
            ->withCount('applications')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => array_merge($company->toArray(), [
                'jobs'       => $jobs,
                'jobs_count' => $jobs->count(),
            ]),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    private function updateSeekerProfile(Request $request, $user)
    {
        $request->validate([
            'headline'     => 'nullable|string|max:255',
            'bio'          => 'nullable|string',
            'phone'        => 'nullable|string|max:20',
            'location'     => 'nullable|string|max:255',
            'website'      => 'nullable|url',
            'linkedin'     => 'nullable|url',
            'github'       => 'nullable|url',
            'resume_url'   => 'nullable|url',
            'skills'       => 'nullable|array',
            'skills.*'     => 'string|max:50',
            'experience'   => 'nullable|array',
            'education'    => 'nullable|array',
            'open_to_work' => 'boolean',
        ]);

        $profile = SeekerProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
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
            ])
        );

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => $profile,
        ]);
    }

    private function updateCompanyProfile(Request $request, $user)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'logo_url'     => 'nullable|url',
            'website'      => 'nullable|url',
            'industry'     => 'nullable|string|max:100',
            'company_size' => 'nullable|string|max:50',
            'founded_year' => 'nullable|string|max:4',
            'location'     => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'linkedin'     => 'nullable|url',
            'twitter'      => 'nullable|url',
        ]);

        $profile = CompanyProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'company_name',
                'logo_url',
                'website',
                'industry',
                'company_size',
                'founded_year',
                'location',
                'description',
                'linkedin',
                'twitter',
            ])
        );

        return response()->json([
            'message' => 'Company profile updated successfully.',
            'profile' => $profile,
        ]);
    }

    private function formatUser($user): array
    {
        return [
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'role'   => $user->role,
            'avatar' => $user->avatar,
        ];
    }
}
