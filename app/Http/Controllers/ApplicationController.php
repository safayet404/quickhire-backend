<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    /**
     * POST /api/applications
     * Submit a job application
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id'     => 'required|exists:jobs,id',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'resume_link'=> 'required|url',
            'cover_note' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Prevent duplicate applications from the same email for same job
        $exists = Application::where('job_id', $request->job_id)
            ->where('email', $request->email)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied to this job.',
            ], 409);
        }

        $application = Application::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully! We will get back to you soon.',
            'data'    => $application,
        ], 201);
    }

    /**
     * GET /api/applications
     * List all applications (Admin)
     */
    public function index(Request $request)
    {
        $query = Application::with('job')->latest();

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->job_id);
        }

        $applications = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $applications->items(),
            'pagination' => [
                'total'        => $applications->total(),
                'per_page'     => $applications->perPage(),
                'current_page' => $applications->currentPage(),
                'last_page'    => $applications->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/applications/{id}
     * Get a single application (Admin)
     */
    public function show($id)
    {
        $application = Application::with('job')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $application,
        ]);
    }

    /**
     * DELETE /api/applications/{id}
     * Delete an application (Admin)
     */
    public function destroy($id)
    {
        $application = Application::findOrFail($id);
        $application->delete();

        return response()->json([
            'success' => true,
            'message' => 'Application deleted successfully.',
        ]);
    }
}
