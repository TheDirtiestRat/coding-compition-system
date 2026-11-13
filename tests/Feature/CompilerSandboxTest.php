<?php

namespace Tests\Feature;

use App\Models\ProgrammingLanguage;
use App\Models\User;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class CompilerSandboxTest extends TestCase
{
    public function test_all_compiler_routes_fail_closed_when_sandbox_is_unavailable(): void
    {
        Process::fake(fn () => Process::result(exitCode: 125));
        $this->actingAs(User::factory()->create());
        $python = ProgrammingLanguage::create([
            'name' => 'python', 'file_extension' => '.py', 'run_command' => 'python {file}', 'is_active' => true,
        ]);
        ProgrammingLanguage::create([
            'name' => 'java', 'file_extension' => '.java', 'compile_command' => 'javac {file}',
            'run_command' => 'java -cp {dir} Main', 'is_active' => true,
        ]);

        foreach (['/run-code', '/run-python-code', '/run-java-code'] as $route) {
            $this->postJson($route, ['language_id' => $python->id, 'code' => 'untrusted code', 'input' => '0'])
                ->assertStatus(503)
                ->assertJsonPath('error', 'Isolated code execution is unavailable. Ask an administrator to check the sandbox runtime and image.');
        }

        Process::assertRanTimes(fn ($process) => $process->command[0] === 'docker' && $process->command[1] === 'run', 3);
    }
}
