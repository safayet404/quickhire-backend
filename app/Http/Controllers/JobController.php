<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    /**
     * GET /api/jobs
     * List all active jobs with search + filter
     */
    public function index(Request $request)
    {
        $query = Job::active()->withCount('applications');

        // Search by title or company
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Filter by job type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $perPage = $request->get('per_page', 12);
        $jobs = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $jobs->items(),
            'pagination' => [
                'total' => $jobs->total(),
                'per_page' => $jobs->perPage(),
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/jobs/featured
     * Get featured jobs for homepage
     */
    public function featured()
    {
        $jobs = Job::active()->featured()->withCount('applications')->latest()->take(6)->get();

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    /**
     * GET /api/jobs/categories
     * Get job categories with counts
     */
    public function categories()
    {
        $categories = Job::active()
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * GET /api/jobs/{id}
     * Get single job details
     */
    public function show($id)
    {
        $job = Job::active()->withCount('applications')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }

    /**
     * POST /api/jobs
     * Create a new job (Admin)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'company'     => 'required|string|max:255',
            'company_logo'=> 'nullable|url',
            'location'    => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'type'        => 'required|in:full-time,part-time,remote,contract,internship',
            'salary_min'  => 'nullable|integer|min:0',
            'salary_max'  => 'nullable|integer|min:0',
            'description' => 'required|string',
            'requirements'=> 'nullable|array',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $job = Job::create(array_merge($validator->validated(), [
            'is_active' => true,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Job created successfully.',
            'data'    => $job,
        ], 201);
    }

    /**
     * PUT /api/jobs/{id}
     * Update a job (Admin)
     */
    public function update(Request $request, $id)
    {
        $job = Job::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|string|max:255',
            'company'     => 'sometimes|string|max:255',
            'company_logo'=> 'nullable|url',
            'location'    => 'sometimes|string|max:255',
            'category'    => 'sometimes|string|max:100',
            'type'        => 'sometimes|in:full-time,part-time,remote,contract,internship',
            'salary_min'  => 'nullable|integer|min:0',
            'salary_max'  => 'nullable|integer|min:0',
            'description' => 'sometimes|string',
            'requirements'=> 'nullable|array',
            'is_featured' => 'boolean',
            'is_active'   => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $job->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully.',
            'data'    => $job,
        ]);
    }

    /**
     * DELETE /api/jobs/{id}
     * Delete a job (Admin)
     */
    public function destroy($id)
    {
        $job = Job::findOrFail($id);
        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully.',
        ]);
    }
}
