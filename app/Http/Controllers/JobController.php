<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Application;
use Illuminate\Http\Request;

class JobController extends Controller
{
    // ── GET /api/jobs ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Job::active()->withCount('applications');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('location'))  $query->where('location', 'like', "%{$request->location}%");
        if ($request->filled('type'))      $query->where('type', $request->type);

        $jobs = $query->latest()->paginate($request->get('per_page', 12));

        return response()->json([
            'success'    => true,
            'data'       => $jobs->items(),
            'pagination' => [
                'total'        => $jobs->total(),
                'per_page'     => $jobs->perPage(),
                'current_page' => $jobs->currentPage(),
                'last_page'    => $jobs->lastPage(),
            ],
        ]);
    }

    // ── GET /api/jobs/featured ────────────────────────────────
    public function featured()
    {
        $jobs = Job::active()->featured()->withCount('applications')->latest()->take(6)->get();
        return response()->json(['success' => true, 'data' => $jobs]);
    }

    // ── GET /api/jobs/categories ──────────────────────────────
    public function categories()
    {
        $categories = Job::active()
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    // ── GET /api/jobs/{id} ────────────────────────────────────
    public function show($id)
    {
        $job = Job::withCount('applications')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $job]);
    }

    // ── GET /api/employer/jobs ────────────────────────────────
    public function employerJobs(Request $request)
    {
        $jobs = Job::where('user_id', $request->user()->id)
            ->withCount('applications')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success'    => true,
            'data'       => $jobs->items(),
            'pagination' => [
                'total'        => $jobs->total(),
                'per_page'     => $jobs->perPage(),
                'current_page' => $jobs->currentPage(),
                'last_page'    => $jobs->lastPage(),
            ],
        ]);
    }

    // ── GET /api/employer/stats ───────────────────────────────
    public function employerStats(Request $request)
    {
        $userId = $request->user()->id;

        $totalJobs       = Job::where('user_id', $userId)->count();
        $activeJobs      = Job::where('user_id', $userId)->where('is_active', true)->count();
        $totalApplicants = Application::whereHas('job', fn($q) => $q->where('user_id', $userId))->count();
        $pendingReview   = Application::whereHas('job', fn($q) => $q->where('user_id', $userId))
            ->where('status', 'pending')->count();

        return response()->json([
            'success' => true,
            'data'    => compact('totalJobs', 'activeJobs', 'totalApplicants', 'pendingReview'),
        ]);
    }

    // ── GET /api/employer/jobs/{id}/applications ──────────────
    public function jobApplications(Request $request, $id)
    {
        $job = Job::where('user_id', $request->user()->id)->findOrFail($id);

        $applications = Application::with('user.seekerProfile')
            ->where('job_id', $job->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success'    => true,
            'job'        => $job,
            'data'       => $applications->items(),
            'pagination' => [
                'total'        => $applications->total(),
                'current_page' => $applications->currentPage(),
                'last_page'    => $applications->lastPage(),
            ],
        ]);
    }

    // ── POST /api/employer/jobs ───────────────────────────────
    public function store(Request $request)
    {
        // capture the return value of validate() — this is validated data
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'company'      => 'required|string|max:255',
            'company_logo' => 'nullable|url',
            'location'     => 'required|string|max:255',
            'category'     => 'required|string|max:100',
            'type'         => 'required|in:full-time,part-time,remote,contract,internship',
            'salary_min'   => 'nullable|integer|min:0',
            'salary_max'   => 'nullable|integer|min:0',
            'description'  => 'required|string',
            'requirements' => 'nullable|array',
            'is_featured'  => 'boolean',
        ]);

        $job = Job::create(array_merge($validated, [
            'user_id'   => $request->user()->id,
            'is_active' => true,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Job posted successfully.',
            'data'    => $job,
        ], 201);
    }

    // ── PUT /api/employer/jobs/{id} ───────────────────────────
    public function update(Request $request, $id)
    {
        $job = Job::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'company'      => 'sometimes|string|max:255',
            'company_logo' => 'nullable|url',
            'location'     => 'sometimes|string|max:255',
            'category'     => 'sometimes|string|max:100',
            'type'         => 'sometimes|in:full-time,part-time,remote,contract,internship',
            'salary_min'   => 'nullable|integer|min:0',
            'salary_max'   => 'nullable|integer|min:0',
            'description'  => 'sometimes|string',
            'requirements' => 'nullable|array',
            'is_featured'  => 'boolean',
            'is_active'    => 'boolean',
        ]);

        $job->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully.',
            'data'    => $job,
        ]);
    }

    // ── PATCH /api/employer/jobs/{id}/toggle ──────────────────
    public function toggleActive(Request $request, $id)
    {
        $job = Job::where('user_id', $request->user()->id)->findOrFail($id);
        $job->update(['is_active' => !$job->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $job->is_active,
            'message'   => $job->is_active ? 'Job activated.' : 'Job deactivated.',
        ]);
    }

    // ── DELETE /api/employer/jobs/{id} ────────────────────────
    public function destroy(Request $request, $id)
    {
        $job = Job::where('user_id', $request->user()->id)->findOrFail($id);
        $job->delete();

        return response()->json(['success' => true, 'message' => 'Job deleted successfully.']);
    }
}
