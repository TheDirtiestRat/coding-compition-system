<?php

namespace Tests\Unit;

use App\Models\ProgrammingLanguage;
use App\Services\CodeRunner;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Process\Factory;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;

class CodeRunnerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container;
        $container->instance('config', new Repository(['code_runner' => ['image' => 'coding-competition-sandbox:1']]));
        $container->instance(Factory::class, new Factory);
        Container::setInstance($container);
        Facade::setFacadeApplication($container);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);

        parent::tearDown();
    }

    private function language(): ProgrammingLanguage
    {
        return new ProgrammingLanguage([
            'name' => 'python',
            'file_extension' => '.py',
            'run_command' => 'python {file}',
        ]);
    }

    public function test_preserves_zero_input(): void
    {
        Process::fake(fn () => Process::result(output: "0\n"));
        $code = 'import sys; print(sys.stdin.read(), end="")';
        $output = (new CodeRunner)->run($code, $this->language(), '0');

        $this->assertSame("0\n", $output);
        Process::assertRan(function ($process) use ($code) {
            if ($process->command[1] !== 'run') {
                return false;
            }

            $payload = json_decode($process->input, true, flags: JSON_THROW_ON_ERROR);

            return $payload['code'] === $code && $payload['input'] === '0' && $payload['timeout'] === 5;
        });
    }

    #[DataProvider('failures')]
    public function test_preserves_execution_errors(int $exitCode, int $status, string $message): void
    {
        Process::fake(fn () => Process::result(errorOutput: 'failed', exitCode: $exitCode));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode($status);
        $this->expectExceptionMessage($message);

        (new CodeRunner)->run('raise RuntimeError("failed")', $this->language());
    }

    public static function failures(): array
    {
        return [
            'compile' => [65, 400, "Compilation Error:\nfailed"],
            'runtime' => [70, 500, 'failed'],
            'timeout' => [124, 408, 'Process timed out (Max 5s)'],
            'engine or image unavailable' => [125, 503, 'Isolated code execution is unavailable.'],
            'container killed' => [137, 503, 'Isolated code execution is unavailable.'],
        ];
    }

    public function test_only_docker_receives_code_without_host_files_mounts_or_environment(): void
    {
        Process::fake(fn () => Process::result(output: 'safe'));
        $code = 'open("../outside-sentinel", "w").write("changed")';
        $language = $this->language();
        $language->compile_command = 'arbitrary-command {file}';

        (new CodeRunner)->run($code, $language, null);

        Process::assertRanTimes(function ($process) {
            $this->assertSame('docker', $process->command[0]);
            $this->assertSame([], $process->environment);

            return true;
        }, 2);
        Process::assertRan(function ($process) use ($code) {
            if ($process->command[1] !== 'run') {
                return false;
            }

            foreach (['--pull=never', '--network=none', '--read-only', '--cap-drop=ALL',
                '--security-opt=no-new-privileges', '--user=65534:65534', '--pids-limit=64',
                '--memory=512m', '--memory-swap=512m', '--cpus=1',
                '--tmpfs=/workspace:rw,nosuid,nodev,size=64m,mode=1777'] as $flag) {
                $this->assertContains($flag, $process->command);
            }
            $this->assertNotContains('--volume', $process->command);
            $this->assertNotContains('--mount', $process->command);
            $this->assertNotContains('-v', $process->command);
            $this->assertNotContains('--env', $process->command);
            $payload = json_decode($process->input, true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame($code, $payload['code']);
            $this->assertSame('', $payload['input']);
            $this->assertSame('arbitrary-command {file}', $payload['compile_command']);
            $this->assertSame('python {file}', $payload['run_command']);

            return true;
        });
        Process::assertRan(fn ($process) => array_slice($process->command, 0, 4) === ['docker', 'rm', '--force', '--volumes'] && $process->timeout === 5);
    }

    public function test_missing_runtime_never_falls_back_to_host_execution(): void
    {
        $commands = [];
        Process::fake(function ($process) use (&$commands) {
            $commands[] = $process->command;
            throw new \RuntimeException('Runtime could not start');
        });

        try {
            (new CodeRunner)->run('print("must not run")', $this->language());
            $this->fail('Execution should fail closed.');
        } catch (\RuntimeException $e) {
            $this->assertSame(503, $e->getCode());
            $this->assertStringContainsString('Isolated code execution is unavailable.', $e->getMessage());
        }

        $this->assertCount(2, $commands);
        $this->assertSame('docker', $commands[0][0]);
        $this->assertSame(['docker', 'rm', '--force', '--volumes', $commands[0][6]], $commands[1]);
    }

    public function test_original_seeded_java_commands_are_compatible_with_the_container(): void
    {
        Process::fake(fn () => Process::result(output: 'hello'));
        $language = new ProgrammingLanguage([
            'file_extension' => '.java',
            'compile_command' => '"C:\\Program Files\\Java\\jdk-23\\bin\\javac.exe" {file}',
            'run_command' => '"C:\\Program Files\\Java\\jdk-23\\bin\\java.exe" -cp {dir} Main',
        ]);

        (new CodeRunner)->run('class Main {}', $language);

        Process::assertRan(function ($process) {
            if ($process->command[1] !== 'run') {
                return false;
            }

            $payload = json_decode($process->input, true, flags: JSON_THROW_ON_ERROR);

            return $payload['compile_command'] === 'javac {file}' && $payload['run_command'] === 'java -cp {dir} Main';
        });
    }

    public function test_missing_docker_cannot_read_or_rewrite_a_real_host_sentinel(): void
    {
        if ((new ExecutableFinder)->find('docker') !== null) {
            $this->markTestSkipped('This regression exercises a host without Docker.');
        }

        $sentinel = tempnam(sys_get_temp_dir(), 'code-runner-sentinel-');
        file_put_contents($sentinel, 'private sentinel');
        $path = json_encode(str_replace('\\', '/', $sentinel), JSON_THROW_ON_ERROR);
        $code = "from pathlib import Path; p = Path($path); print(p.read_text()); p.write_text('changed')";

        try {
            try {
                (new CodeRunner)->run($code, $this->language());
                $this->fail('Execution without Docker should fail closed.');
            } catch (\RuntimeException $e) {
                $this->assertSame(503, $e->getCode());
                $this->assertStringNotContainsString('private sentinel', $e->getMessage());
            }

            $this->assertSame('private sentinel', file_get_contents($sentinel));
        } finally {
            unlink($sentinel);
        }
    }

    public function test_rejects_extensions_that_escape_the_work_directory(): void
    {
        $language = $this->language();
        $language->file_extension = '/../outside.php';
        Process::fake();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid programming language configuration.');

        try {
            (new CodeRunner)->run('print("hello")', $language);
        } finally {
            Process::assertNothingRan();
        }
    }
}
