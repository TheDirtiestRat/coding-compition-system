<?php

namespace App\Services;

use App\Models\ProgrammingLanguage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class CodeRunner
{
    public function run(string $code, ProgrammingLanguage $language, ?string $input = '', int $timeout = 5): string
    {
        if (! preg_match('/^\.[a-zA-Z0-9]+$/', $language->file_extension) || ! $language->run_command) {
            throw new \RuntimeException('Invalid programming language configuration.', 422);
        }

        $tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'code_exec_'.Str::random(16);
        File::makeDirectory($tempDir);

        try {
            $filename = 'Main'.$language->file_extension;
            File::put($tempDir.DIRECTORY_SEPARATOR.$filename, $code);

            // Symfony escapes placeholder values for the current platform's shell.
            $placeholders = ['{file}' => '"${:CODE_FILE}"', '{dir}' => '"${:CODE_DIR}"'];
            $environment = ['CODE_FILE' => $filename, 'CODE_DIR' => $tempDir];

            if ($language->compile_command) {
                $compileProcess = Process::fromShellCommandline(
                    strtr($language->compile_command, $placeholders), $tempDir, $environment, null, $timeout
                );
                $compileProcess->run();

                if (! $compileProcess->isSuccessful()) {
                    throw new \RuntimeException("Compilation Error:\n".$compileProcess->getErrorOutput(), 400);
                }
            }

            $runProcess = Process::fromShellCommandline(
                strtr($language->run_command, $placeholders), $tempDir, $environment, $input ?? '', $timeout
            );
            $runProcess->run();

            if (! $runProcess->isSuccessful()) {
                throw new \RuntimeException($runProcess->getErrorOutput() ?: 'Execution error', 500);
            }

            return $runProcess->getOutput();
        } catch (ProcessTimedOutException $e) {
            throw new \RuntimeException("Process timed out (Max {$timeout}s)", 408, $e);
        } finally {
            File::deleteDirectory($tempDir);
        }
    }
}
