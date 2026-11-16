<?php

namespace Tests\Feature;

use App\Jobs\ReviewSubmission;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SqliteAiQueueTest extends TestCase
{
    public function test_ai_job_reservation_waits_for_another_sqlite_writer_instead_of_failing_the_job(): void
    {
        $database = tempnam(sys_get_temp_dir(), 'ai-queue-');
        $signal = $database.'.ready';
        $process = null;
        config([
            'database.connections.ai_queue_test' => array_replace(config('database.connections.sqlite'), ['database' => $database]),
            'queue.connections.ai-reviews.connection' => 'ai_queue_test',
        ]);

        try {
            Schema::connection('ai_queue_test')->create('jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue');
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
            $queue = Queue::connection('ai-reviews');
            $queue->push(new ReviewSubmission('queue-test'));
            $writer = <<<'PHP'
                $pdo = new PDO('sqlite:'.$argv[1]);
                $pdo->exec('BEGIN IMMEDIATE');
                $pdo->exec('UPDATE jobs SET attempts = attempts');
                file_put_contents($argv[2], 'ready');
                usleep(700000);
                $pdo->exec('COMMIT');
                PHP;
            $process = proc_open([PHP_BINARY, '-r', $writer, $database, $signal], [], $pipes);
            $this->assertIsResource($process);
            $deadline = microtime(true) + 5;
            while (! file_exists($signal) && microtime(true) < $deadline) {
                usleep(10000);
            }
            $this->assertFileExists($signal);

            $job = $queue->pop('ai-reviews');

            $this->assertNotNull($job);
            $this->assertSame(1, $job->attempts());
            $job->delete();
            $this->assertSame(0, DB::connection('ai_queue_test')->table('jobs')->count());
        } finally {
            if (is_resource($process)) {
                proc_close($process);
            }
            DB::purge('ai_queue_test');
            @unlink($signal);
            @unlink($database);
        }
    }
}
