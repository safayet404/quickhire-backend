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
    // Public list of all company profiles
    public function companies(Request $request)
    {
        $companies = CompanyProfile::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json($companies);
    }

    // ── GET /api/companies/{id} ───────────────────────────────
    public function companyShow($id)
    {
        $company = CompanyProfile::with('user')->findOrFail($id);
        return response()->json($company);
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
