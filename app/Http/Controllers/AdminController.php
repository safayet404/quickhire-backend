<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Job;
use App\Models\Application;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ── GET /api/admin/stats ──────────────────────────────────
    public function stats()
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'total_users'        => User::count(),
                'total_jobs'         => Job::count(),
                'active_jobs'        => Job::where('is_active', true)->count(),
                'total_applications' => Application::count(),
                'pending_apps'       => Application::where('status', 'pending')->count(),
                'accepted_apps'      => Application::where('status', 'accepted')->count(),
                'total_seekers'      => User::where('role', 'seeker')->count(),
                'total_employers'    => User::where('role', 'employer')->count(),
            ],
        ]);
    }

    // ── GET /api/admin/users ──────────────────────────────────
    public function users(Request $request)
    {
        $query = User::withCount(['applications', 'jobListings'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('role'))   $query->where('role', $request->role);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
        }

        $users = $query->paginate(20);

        return response()->json([
            'success'    => true,
            'data'       => $users->items(),
            'pagination' => [
                'total'        => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
            ],
        ]);
    }

    // ── PATCH /api/admin/users/{id}/role ──────────────────────
    public function updateRole(Request $request, $id)
    {
        $request->validate(['role' => 'required|in:seeker,employer,admin']);

        $user = User::findOrFail($id);

        // Prevent removing the last admin
        if ($user->role === 'admin' && $request->role !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return response()->json(['success' => false, 'message' => 'Cannot remove the last admin.'], 422);
            }
        }

        $user->update(['role' => $request->role]);

        return response()->json(['success' => true, 'message' => 'User role updated.', 'data' => $user]);
    }

    // ── DELETE /api/admin/users/{id} ──────────────────────────
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json(['success' => false, 'message' => 'Cannot delete admin users.'], 422);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted.']);
    }

    // ── GET /api/admin/jobs ───────────────────────────────────
    public function jobs(Request $request)
    {
        $query = Job::withCount('applications')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('title', 'like', "%{$s}%")->orWhere('company', 'like', "%{$s}%"));
        }
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $jobs = $query->paginate(20);

        return response()->json([
            'success'    => true,
            'data'       => $jobs->items(),
            'pagination' => [
                'total'        => $jobs->total(),
                'current_page' => $jobs->currentPage(),
                'last_page'    => $jobs->lastPage(),
            ],
        ]);
    }

    // ── PATCH /api/admin/jobs/{id}/toggle ─────────────────────
    public function toggleJob($id)
    {
        $job = Job::findOrFail($id);
        $job->update(['is_active' => !$job->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $job->is_active,
            'message'   => $job->is_active ? 'Job activated.' : 'Job deactivated.',
        ]);
    }

    // ── DELETE /api/admin/jobs/{id} ───────────────────────────
    public function deleteJob($id)
    {
        Job::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Job deleted.']);
    }
}
