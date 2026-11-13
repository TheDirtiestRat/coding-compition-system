<?php

namespace Tests\Unit;

use App\Models\ProgrammingLanguage;
use App\Services\CodeRunner;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Process\Factory;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class CodeRunnerSandboxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('RUN_SANDBOX_TESTS') !== '1') {
            $this->markTestSkipped('Set RUN_SANDBOX_TESTS=1 with Docker and the built sandbox image.');
        }

        $container = new Container;
        $container->instance('config', new Repository(['code_runner' => ['image' => getenv('CODE_RUNNER_IMAGE') ?: 'coding-competition-sandbox:1']]));
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

    private function python(): ProgrammingLanguage
    {
        return new ProgrammingLanguage(['file_extension' => '.py', 'run_command' => 'python {file}']);
    }

    public function test_python_preserves_stdin_and_stdout(): void
    {
        $this->assertSame('0', (new CodeRunner)->run('import sys; print(sys.stdin.read(), end="")', $this->python(), '0'));
    }

    public function test_java_compiles_and_runs_in_the_same_workspace(): void
    {
        $language = new ProgrammingLanguage([
            'file_extension' => '.java', 'compile_command' => 'javac {file}', 'run_command' => 'java -cp {dir} Main',
        ]);
        $code = 'public class Main { public static void main(String[] args) throws Exception { System.out.print(new String(System.in.readAllBytes())); } }';

        $this->assertSame('0', (new CodeRunner)->run($code, $language, '0'));
    }

    public function test_cannot_read_or_rewrite_host_files_or_access_host_environment_or_network(): void
    {
        $sentinel = tempnam(sys_get_temp_dir(), 'sandbox-sentinel-');
        file_put_contents($sentinel, 'private sentinel');
        putenv('SANDBOX_HOST_SECRET=private-secret');
        $path = json_encode(str_replace('\\', '/', $sentinel), JSON_THROW_ON_ERROR);
        $code = <<<PYTHON
import os, socket
path = $path
for mode in ['r', 'w']:
    try:
        with open(path, mode) as f:
            f.read() if mode == 'r' else f.write('changed')
    except OSError:
        pass
    else:
        raise AssertionError('host file accessible')
assert 'SANDBOX_HOST_SECRET' not in os.environ
assert not os.path.exists('/var/run/docker.sock')
try:
    socket.create_connection(('1.1.1.1', 80), timeout=1)
except OSError:
    pass
else:
    raise AssertionError('network accessible')
print('isolated', end='')
PYTHON;

        try {
            $this->assertSame('isolated', (new CodeRunner)->run($code, $this->python()));
            $this->assertSame('private sentinel', file_get_contents($sentinel));
        } finally {
            unlink($sentinel);
            putenv('SANDBOX_HOST_SECRET');
        }
    }

    public function test_enforces_execution_timeout(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(408);

        (new CodeRunner)->run('import time; time.sleep(10)', $this->python(), '', 1);
    }

    public function test_preserves_runtime_errors(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('sandbox runtime failure');

        (new CodeRunner)->run('raise RuntimeError("sandbox runtime failure")', $this->python());
    }

    public function test_preserves_compilation_errors(): void
    {
        $language = new ProgrammingLanguage([
            'file_extension' => '.java', 'compile_command' => 'javac {file}', 'run_command' => 'java -cp {dir} Main',
        ]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Compilation Error:');

        (new CodeRunner)->run('not valid java', $language);
    }

    public function test_limits_output(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Execution output exceeded 1 MB.');

        (new CodeRunner)->run('print("x" * (2 * 1024 * 1024))', $this->python());
    }
}
