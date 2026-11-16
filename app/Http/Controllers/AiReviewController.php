<?php

namespace App\Http\Controllers;

use App\Jobs\ReviewSubmission;
use App\Models\Submissions;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AiReviewController extends Controller
{
    public function store(Request $request, int $id)
    {
        abort_unless(config('local_ai.enabled'), 503, 'Local AI review is not enabled. Manual scoring remains available.');
        $validated = $request->validate(['code_hash' => 'required|string|size:64']);
        $submission = Submissions::findOrFail($id);
        abort_unless(hash_equals(hash('sha256', $submission->code), $validated['code_hash']), 409, 'Submission changed. Reload it before requesting AI review.');

        $reviewId = (string) Str::uuid();
        Cache::store('database')->put('ai-review:'.$reviewId, [
            'admin_id' => $request->user()->id,
            'submission_id' => $submission->id,
            'code_hash' => $validated['code_hash'],
            'model' => config('local_ai.model'),
            'state' => 'pending',
        ], now()->addMinutes(15));
        ReviewSubmission::dispatch($reviewId);

        return response()->json(['review_id' => $reviewId], 202);
    }

    public function show(Request $request, string $reviewId)
    {
        $record = Cache::store('database')->get('ai-review:'.$reviewId);
        abort_unless($record && $record['admin_id'] === $request->user()->id, 404);

        return response()->json(Arr::only($record, ['state', 'model', 'review', 'message']));
    }
}
