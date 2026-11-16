<?php

namespace App\Jobs;

use App\Models\Submissions;
use App\Services\LocalAiReview;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;

class ReviewSubmission implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 180;

    public function __construct(public string $reviewId)
    {
        $this->onConnection('ai-reviews')->onQueue('ai-reviews');
    }

    public function handle(LocalAiReview $ai): void
    {
        $record = Cache::store('database')->get('ai-review:'.$this->reviewId);
        if (! $record || in_array($record['state'], ['completed', 'failed'], true)) {
            return;
        }

        try {
            $submission = Submissions::with('challenge')->find($record['submission_id']);
            if (! $submission || ! hash_equals($record['code_hash'], hash('sha256', $submission->code))) {
                throw new \RuntimeException('The submission changed. Reload it before requesting another review.');
            }
            $record['state'] = 'running';
            Cache::store('database')->put('ai-review:'.$this->reviewId, $record, now()->addMinutes(15));
            $review = $ai->review($submission);
            $current = $submission->fresh();
            if (! $current || ! hash_equals($record['code_hash'], hash('sha256', $current->code))) {
                throw new \RuntimeException('The submission changed during the review. Reload it and retry.');
            }
            $record['review'] = $review;
            $record['state'] = 'completed';
        } catch (\Throwable $e) {
            $record['state'] = 'failed';
            $record['message'] = $e instanceof \RuntimeException && ! $e instanceof ConnectionException
                ? $e->getMessage()
                : 'Local AI could not finish the review. Check the local service and retry. Official scoring is unchanged.';
            report($e);
        }

        Cache::store('database')->put('ai-review:'.$this->reviewId, $record, now()->addMinutes(15));
    }

    public function failed(?\Throwable $exception): void
    {
        $record = Cache::store('database')->get('ai-review:'.$this->reviewId);
        if ($record) {
            $record['state'] = 'failed';
            $record['message'] = 'The local review worker stopped before finishing. Retry or review manually.';
            Cache::store('database')->put('ai-review:'.$this->reviewId, $record, now()->addMinutes(15));
        }
    }
}
