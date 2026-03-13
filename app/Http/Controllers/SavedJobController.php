<?php

namespace App\Http\Controllers;

use App\Models\SavedJob;
use Illuminate\Http\Request;

class SavedJobController extends Controller
{
    // ── GET /api/saved-jobs ───────────────────────────────────
    public function index(Request $request)
    {
        $saved = SavedJob::with(['job'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success'    => true,
            'data'       => $saved->items(),
            'pagination' => [
                'total'        => $saved->total(),
                'current_page' => $saved->currentPage(),
                'last_page'    => $saved->lastPage(),
            ],
        ]);
    }

    // ── POST /api/saved-jobs/toggle ───────────────────────────
    // Save or unsave a job — returns new saved state
    public function toggle(Request $request)
    {
        $request->validate(['job_id' => 'required|exists:job_listings,id']);

        $userId = $request->user()->id;
        $jobId  = $request->job_id;

        $existing = SavedJob::where('user_id', $userId)->where('job_id', $jobId)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['success' => true, 'saved' => false, 'message' => 'Job removed from saved.']);
        }

        SavedJob::create(['user_id' => $userId, 'job_id' => $jobId]);
        return response()->json(['success' => true, 'saved' => true, 'message' => 'Job saved!']);
    }

    // ── GET /api/saved-jobs/check?job_id=X ───────────────────
    public function check(Request $request)
    {
        $request->validate(['job_id' => 'required|integer']);

        $saved = SavedJob::where('user_id', $request->user()->id)
            ->where('job_id', $request->job_id)
            ->exists();

        return response()->json(['saved' => $saved]);
    }

    // ── GET /api/saved-jobs/ids ───────────────────────────────
    // Returns array of all saved job IDs — used to highlight bookmarks on job list
    public function ids(Request $request)
    {
        $ids = SavedJob::where('user_id', $request->user()->id)->pluck('job_id');
        return response()->json(['ids' => $ids]);
    }
}
