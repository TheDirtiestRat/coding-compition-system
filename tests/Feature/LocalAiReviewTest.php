<?php

namespace Tests\Feature;

use App\Jobs\ReviewSubmission;
use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Events;
use App\Models\Submissions;
use App\Models\User;
use App\Services\LocalAiReview;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LocalAiReviewTest extends TestCase
{
    private function submission(): Submissions
    {
        $candidate = Candidates::create([
            'event_id' => Events::create(['title' => 'Competition', 'isActive' => true])->id,
            'user_id' => User::factory()->create()->id,
            'fullname' => 'Private student name',
        ]);
        $challenge = Challenges::create([
            'event_id' => $candidate->event_id, 'title' => 'Sum', 'description' => 'Print the sum of two integers.',
            'language' => 'python', 'difficulty' => 'easy', 'points' => 10, 'code_solution' => 'private reference solution',
        ]);

        return Submissions::create([
            'candidate_id' => $candidate->id, 'challenge_id' => $challenge->id,
            'code' => 'print(sum(map(int, input().split())))', 'score' => 3,
        ]);
    }

    private function enqueue(Submissions $submission): string
    {
        config(['local_ai.enabled' => true]);
        Queue::fake();

        return $this->postJson('/admin/api/submissions/'.$submission->id.'/ai-review', [
            'code_hash' => hash('sha256', $submission->code),
        ])->assertStatus(202)->json('review_id');
    }

    public function test_review_is_queued_and_private_to_the_requesting_admin(): void
    {
        $submission = $this->submission();
        $admin = User::factory()->create(['user_type' => 'admin']);
        $this->actingAs($admin);
        $id = $this->enqueue($submission);

        Queue::assertPushed(ReviewSubmission::class, fn ($job) => $job->connection === 'ai-reviews' && $job->queue === 'ai-reviews');
        $this->getJson('/admin/api/ai-reviews/'.$id)->assertOk()->assertJsonPath('state', 'pending')->assertJsonMissingPath('admin_id');
        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->getJson('/admin/api/ai-reviews/'.$id)->assertNotFound();
        $this->actingAs($submission->candidate->user)
            ->postJson('/admin/api/submissions/'.$submission->id.'/ai-review', [])->assertForbidden();
        $this->getJson('/admin/api/ai-reviews/'.$id)->assertForbidden();
    }

    public function test_local_review_returns_advice_without_changing_the_score_or_sending_identity(): void
    {
        $submission = $this->submission();
        $before = $submission->fresh()->getAttributes();
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        $id = $this->enqueue($submission);
        Http::preventStrayRequests();
        Http::fake(['127.0.0.1:11434/*' => Http::response(['message' => ['content' => json_encode([
            'summary' => 'Uses a valid alternative approach.', 'findings' => ['<script>untrusted text</script>'],
            'limitations' => 'Only the supplied evidence was reviewed.', 'score' => 10,
        ])]])]);

        (new ReviewSubmission($id))->handle(app(LocalAiReview::class));

        $this->getJson('/admin/api/ai-reviews/'.$id)->assertOk()
            ->assertJsonPath('state', 'completed')->assertJsonPath('review.summary', 'Uses a valid alternative approach.')
            ->assertJsonMissingPath('review.score');
        $this->assertSame($before, $submission->fresh()->getAttributes());
        Http::assertSent(function ($request) {
            $payload = json_encode($request->data());
            $this->assertStringNotContainsString('Private student name', $payload);
            $this->assertStringNotContainsString('private reference solution', $payload);
            $this->assertArrayNotHasKey('tools', $request->data());

            return $request['stream'] === false && $request['format']['type'] === 'object';
        });
    }

    public function test_disabled_and_changed_submissions_do_not_start_a_review(): void
    {
        $submission = $this->submission();
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        Queue::fake();
        $url = '/admin/api/submissions/'.$submission->id.'/ai-review';
        $this->postJson($url, ['code_hash' => hash('sha256', $submission->code)])->assertStatus(503);
        config(['local_ai.enabled' => true]);
        $this->postJson($url, ['code_hash' => str_repeat('0', 64)])->assertStatus(409);
        Queue::assertNothingPushed();
    }

    public function test_malformed_model_output_and_changed_code_fail_without_saving_a_grade(): void
    {
        $submission = $this->submission();
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        $id = $this->enqueue($submission);
        Http::fake(['*' => Http::response(['message' => ['content' => '{broken']])]);
        (new ReviewSubmission($id))->handle(app(LocalAiReview::class));
        $this->getJson('/admin/api/ai-reviews/'.$id)->assertOk()->assertJsonPath('state', 'failed');

        $id = $this->enqueue($submission);
        $submission->update(['code' => 'changed']);
        Http::fake();
        (new ReviewSubmission($id))->handle(app(LocalAiReview::class));
        Http::assertNothingSent();
        $this->assertSame('failed', Cache::store('database')->get('ai-review:'.$id)['state']);
        $this->assertFalse($submission->fresh()->is_score_submitted);
        $this->assertEquals(3, $submission->fresh()->score);
    }

    public function test_nonlocal_urls_and_oversized_inputs_are_rejected_without_network_access(): void
    {
        Http::fake();
        config(['local_ai.url' => 'https://example.com']);
        try {
            app(LocalAiReview::class)->review($this->submission());
            $this->fail('A remote URL must be rejected.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('local', $e->getMessage());
        }
        config(['local_ai.url' => 'http://127.0.0.1:11434']);
        $submission = $this->submission();
        $submission->code = str_repeat('x', 9000);
        try {
            app(LocalAiReview::class)->review($submission);
            $this->fail('Oversized input must be rejected.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('size limit', $e->getMessage());
        }
        Http::assertNothingSent();
    }

    public function test_code_changed_during_inference_discards_the_review(): void
    {
        $submission = $this->submission();
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        $id = $this->enqueue($submission);
        Http::fake(function () use ($submission) {
            $submission->update(['code' => 'new version']);

            return Http::response(['message' => ['content' => json_encode([
                'summary' => 'Old version review.', 'findings' => [], 'limitations' => 'Advisory.',
            ])]]);
        });
        (new ReviewSubmission($id))->handle(app(LocalAiReview::class));
        $this->getJson('/admin/api/ai-reviews/'.$id)->assertOk()
            ->assertJsonPath('state', 'failed')->assertJsonMissingPath('review');
        $this->assertEquals(3, $submission->fresh()->score);
    }

    public function test_offline_service_and_stopped_worker_leave_manual_scoring_available(): void
    {
        $submission = $this->submission();
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        $id = $this->enqueue($submission);
        Http::fake(['*' => Http::failedConnection()]);
        (new ReviewSubmission($id))->handle(app(LocalAiReview::class));
        $this->getJson('/admin/api/ai-reviews/'.$id)->assertOk()->assertJsonPath('state', 'failed');
        $id = $this->enqueue($submission);
        (new ReviewSubmission($id))->failed(new \RuntimeException('Worker stopped'));
        $this->getJson('/admin/api/ai-reviews/'.$id)->assertOk()->assertJsonPath('state', 'failed');
        $this->assertFalse($submission->fresh()->is_score_submitted);
        $this->postJson('/admin/api/submissions/'.$submission->id.'/submit-score', [
            'score' => 7, 'code_hash' => hash('sha256', $submission->code),
        ])->assertOk();
        $this->assertEquals(7, $submission->fresh()->score);
    }

    public function test_associative_findings_are_rejected_instead_of_breaking_the_review_panel(): void
    {
        $submission = $this->submission();
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        $id = $this->enqueue($submission);
        Http::fake(['*' => Http::response(['message' => ['content' => json_encode([
            'summary' => 'Review', 'findings' => ['unexpected' => 'Not a list'], 'limitations' => 'Advisory.',
        ])]])]);
        (new ReviewSubmission($id))->handle(app(LocalAiReview::class));
        $this->getJson('/admin/api/ai-reviews/'.$id)->assertOk()
            ->assertJsonPath('state', 'failed')->assertJsonMissingPath('review');
    }
}
