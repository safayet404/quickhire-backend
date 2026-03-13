<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    // ── POST /api/applications ────────────────────────────────
    // Seeker submits an application (must be authenticated)
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'job_id'      => 'required|exists:job_listings,id',
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'resume_link' => 'required|url',
            'cover_note'  => 'nullable|string|max:2000',
        ]);

        // Prevent duplicate applications
        $exists = Application::where('job_id', $request->job_id)
            ->where('email', $request->email)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied to this job.',
            ], 409);
        }

        $application = Application::create([
            'job_id'      => $request->job_id,
            'user_id'     => $user->id,
            'name'        => $request->name,
            'email'       => $request->email,
            'resume_link' => $request->resume_link,
            'cover_note'  => $request->cover_note,
            'status'      => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully!',
            'data'    => $application->load('job'),
        ], 201);
    }

    // ── GET /api/seeker/applications ──────────────────────────
    // Seeker sees their own applications with status
    public function myApplications(Request $request)
    {
        $applications = Application::with(['job'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success'    => true,
            'data'       => $applications->items(),
            'pagination' => [
                'total'        => $applications->total(),
                'per_page'     => $applications->perPage(),
                'current_page' => $applications->currentPage(),
                'last_page'    => $applications->lastPage(),
            ],
        ]);
    }

    // ── GET /api/seeker/applications/check?job_id=X ──────────
    // Check if seeker already applied to a job
    public function checkApplied(Request $request)
    {
        $request->validate(['job_id' => 'required|integer']);

        $applied = Application::where('user_id', $request->user()->id)
            ->where('job_id', $request->job_id)
            ->exists();

        return response()->json(['applied' => $applied]);
    }

    // ── GET /api/admin/applications ───────────────────────────
    // Admin / Employer sees all applications
    public function index(Request $request)
    {
        $query = Application::with(['job', 'user'])->latest();

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->job_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->paginate(20);

        return response()->json([
            'success'    => true,
            'data'       => $applications->items(),
            'pagination' => [
                'total'        => $applications->total(),
                'per_page'     => $applications->perPage(),
                'current_page' => $applications->currentPage(),
                'last_page'    => $applications->lastPage(),
            ],
        ]);
    }

    // ── GET /api/admin/applications/{id} ─────────────────────
    public function show($id)
    {
        $application = Application::with(['job', 'user'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $application]);
    }

    // ── PATCH /api/admin/applications/{id}/status ─────────────
    // Admin/Employer updates application status
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'      => 'required|in:pending,reviewed,accepted,rejected',
            'status_note' => 'nullable|string|max:1000',
        ]);

        $application = Application::findOrFail($id);
        $application->update([
            'status'      => $request->status,
            'status_note' => $request->status_note,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application status updated.',
            'data'    => $application->fresh(),
        ]);
    }

    // ── DELETE /api/admin/applications/{id} ───────────────────
    public function destroy($id)
    {
        Application::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Application deleted.']);
    }
}
