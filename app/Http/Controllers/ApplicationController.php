<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\Notification;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    // ── POST /api/applications ────────────────────────────────
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

        $application->load('job');

        // ── Notify the seeker ─────────────────────────────────
        Notification::create([
            'user_id' => $user->id,
            'type'    => 'application_submitted',
            'title'   => 'Application Submitted',
            'body'    => "Your application for \"{$application->job->title}\" at {$application->job->company} was received.",
            'link'    => '/applications',
        ]);

        // ── Notify the employer ───────────────────────────────
        if ($application->job->user_id) {
            Notification::create([
                'user_id' => $application->job->user_id,
                'type'    => 'new_application',
                'title'   => 'New Application Received',
                'body'    => "{$request->name} applied for \"{$application->job->title}\".",
                'link'    => '/dashboard/employer',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully!',
            'data'    => $application,
        ], 201);
    }

    // ── GET /api/seeker/applications ──────────────────────────
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
    public function checkApplied(Request $request)
    {
        $request->validate(['job_id' => 'required|integer']);

        $applied = Application::where('user_id', $request->user()->id)
            ->where('job_id', $request->job_id)
            ->exists();

        return response()->json(['applied' => $applied]);
    }

    // ── GET /api/admin/applications ───────────────────────────
    public function index(Request $request)
    {
        $query = Application::with(['job', 'user'])->latest();

        if ($request->filled('job_id')) $query->where('job_id', $request->job_id);
        if ($request->filled('status')) $query->where('status', $request->status);

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

    // ── PATCH /api/employer/applications/{id}/status ──────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'      => 'required|in:pending,reviewed,accepted,rejected',
            'status_note' => 'nullable|string|max:1000',
        ]);

        $application = Application::with('job')->findOrFail($id);
        $oldStatus   = $application->status;

        $application->update([
            'status'      => $request->status,
            'status_note' => $request->status_note,
        ]);

        // ── Notify the seeker when status changes ─────────────
        if ($oldStatus !== $request->status && $application->user_id) {
            $statusLabels = [
                'reviewed' => 'is being reviewed',
                'accepted' => 'has been accepted 🎉',
                'rejected' => 'was not selected this time',
                'pending'  => 'is pending review',
            ];
            $label = $statusLabels[$request->status] ?? $request->status;
            $jobTitle = $application->job?->title ?? 'your position';

            Notification::create([
                'user_id' => $application->user_id,
                'type'    => 'application_status',
                'title'   => 'Application Update',
                'body'    => "Your application for \"{$jobTitle}\" {$label}.",
                'link'    => '/applications',
            ]);
        }

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
