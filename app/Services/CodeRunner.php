<?php

namespace App\Services;

use App\Models\ProgrammingLanguage;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class CodeRunner
{
    public function run(string $code, ProgrammingLanguage $language, ?string $input = '', int $timeout = 5): string
    {
        if (! preg_match('/^\.[a-zA-Z0-9]+$/', $language->file_extension) || ! $language->run_command) {
            throw new \RuntimeException('Invalid programming language configuration.', 422);
        }

        $name = 'code-exec-'.Str::lower(Str::random(24));
        $payload = json_encode([
            'code' => $code,
            'extension' => $language->file_extension,
            'compile_command' => $this->containerCommand($language->compile_command),
            'run_command' => $this->containerCommand($language->run_command),
            'input' => $input ?? '',
            'timeout' => $timeout,
        ], JSON_THROW_ON_ERROR);

        try {
            $outputBytes = 0;
            $result = Process::timeout(2 * $timeout + 10)->input($payload)->run([
                'docker', 'run', '--rm', '--pull=never', '--interactive', '--name', $name,
                '--network=none', '--read-only', '--cap-drop=ALL',
                '--security-opt=no-new-privileges', '--user=65534:65534',
                '--pids-limit=64', '--memory=512m', '--memory-swap=512m', '--cpus=1',
                '--ulimit=nofile=128:128', '--ulimit=core=0',
                '--tmpfs=/workspace:rw,nosuid,nodev,size=64m,mode=1777',
                '--workdir=/workspace', config('code_runner.image'),
            ], function (string $type, string $output) use (&$outputBytes) {
                $outputBytes += strlen($output);
                if ($outputBytes > 1024 * 1024) {
                    throw new \RuntimeException('Execution output exceeded 1 MB.', 500);
                }
            });

            return match ($result->exitCode()) {
                0 => $result->output(),
                65 => throw new \RuntimeException("Compilation Error:\n".$result->errorOutput(), 400),
                70 => throw new \RuntimeException($result->errorOutput() ?: 'Execution error', 500),
                124 => throw new \RuntimeException("Process timed out (Max {$timeout}s)", 408),
                default => throw new \RuntimeException('Isolated code execution is unavailable. Ask an administrator to check the sandbox runtime and image.', 503),
            };
        } catch (ProcessTimedOutException $e) {
            throw new \RuntimeException("Process timed out (Max {$timeout}s)", 408, $e);
        } catch (\RuntimeException $e) {
            if (in_array($e->getCode(), [400, 408, 500, 503], true)) {
                throw $e;
            }

            throw new \RuntimeException('Isolated code execution is unavailable. Ask an administrator to check the sandbox runtime and image.', 503, $e);
        } finally {
            // Killing the Docker client alone does not stop submitted code in its container.
            try {
                Process::timeout(5)->run(['docker', 'rm', '--force', '--volumes', $name]);
            } catch (\RuntimeException) {
                // A missing runtime also prevents cleanup; execution has already failed closed.
            }
        }
    }

    private function containerCommand(?string $command): ?string
    {
        // Keep the original Windows seed usable with the Linux sandbox image.
        return match ($command) {
            '"C:\\Program Files\\Java\\jdk-23\\bin\\javac.exe" {file}' => 'javac {file}',
            '"C:\\Program Files\\Java\\jdk-23\\bin\\java.exe" -cp {dir} Main' => 'java -cp {dir} Main',
            default => $command,
        };
    }
}
