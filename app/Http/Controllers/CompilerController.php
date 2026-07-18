<?php

namespace App\Http\Controllers;

use App\Models\ProgrammingLanguage;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Illuminate\Support\Str;

class CompilerController extends Controller
{
    public function run_python_code(Request $request)
    {
        $code = $request->input('code');
        $input = $request->input('input', '');

        $tempFilePath = tempnam(sys_get_temp_dir(), 'python_sandbox_') . '.py';
        File::put($tempFilePath, $code);

        $process = new Process(['python', $tempFilePath]);
        if ($input) {
            $process->setInput($input);
        }
        $process->run();

        File::delete($tempFilePath);

        if ($process->isSuccessful()) {
            return response()->json(['output' => $process->getOutput()]);
        }

        return response()->json(['error' => $process->getErrorOutput() ?: 'An unknown error occurred'], 500);
    }

    public function run_java_code_unique(Request $request)
    {
        $code = $request->input('code');
        $input = $request->input('input', '');
        $executionTimeout = 5;

        $uniqueId = Str::random(10);
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'java_exec_' . $uniqueId;
        File::makeDirectory($tempDir);

        $className = 'Main';
        $sourceFile = $tempDir . DIRECTORY_SEPARATOR . $className . '.java';

        $path_to_javac = 'C:\Program Files\Java\jdk-23\bin\javac.exe';
        $path_to_java = 'C:\Program Files\Java\jdk-23\bin\java.exe';

        try {
            File::put($sourceFile, $code);

            $compileProcess = new Process([$path_to_javac, $className . '.java'], $tempDir);
            $compileProcess->setTimeout($executionTimeout);
            $compileProcess->run();

            if (!$compileProcess->isSuccessful()) {
                return response()->json(['error' => "Compilation Error:\n" . $compileProcess->getErrorOutput()], 400);
            }

            $runProcess = new Process([$path_to_java, '-cp', '.', $className], $tempDir);
            $runProcess->setTimeout($executionTimeout);
            if ($input) {
                $runProcess->setInput($input);
            }
            $runProcess->run();

            return response()->json(['output' => $runProcess->getOutput()]);
        } catch (ProcessTimedOutException $e) {
            return response()->json(['error' => "Process timed out (Max {$executionTimeout}s). Check for infinite loops!"], 408);
        } catch (\Throwable $e) {
            return response()->json(['error' => "Server Error: " . $e->getMessage()], 500);
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    public function run_code_with_language(Request $request)
    {
        $code = $request->input('code');
        $languageId = $request->input('language_id');
        $input = $request->input('input', '');
        $executionTimeout = 5;

        $language = ProgrammingLanguage::find($languageId);
        if (!$language) {
            return response()->json(['error' => 'Programming language not found'], 404);
        }

        $uniqueId = Str::random(10);
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'exec_' . $uniqueId;
        File::makeDirectory($tempDir);

        $className = 'Main';
        $sourceFile = $tempDir . DIRECTORY_SEPARATOR . $className . $language->file_extension;
        File::put($sourceFile, $code);

        try {
            if ($language->compile_command) {
                $compileCmd = str_replace('{file}', $sourceFile, $language->compile_command);
                $compileProcess = Process::fromShellCommandline($compileCmd, $tempDir);
                $compileProcess->setTimeout($executionTimeout);
                $compileProcess->run();

                if (!$compileProcess->isSuccessful()) {
                    return response()->json(['error' => "Compilation Error:\n" . $compileProcess->getErrorOutput()], 400);
                }
            }

            $filename = $className . $language->file_extension;
            $runCmd = str_replace('{file}', $filename, $language->run_command);
            $runCmd = str_replace('{dir}', $tempDir, $runCmd);
            $runProcess = Process::fromShellCommandline($runCmd, $tempDir);
            $runProcess->setTimeout($executionTimeout);
            if ($input) {
                $runProcess->setInput($input);
            }
            $runProcess->run();

            if ($runProcess->isSuccessful()) {
                return response()->json(['output' => $runProcess->getOutput()]);
            }

            return response()->json(['error' => $runProcess->getErrorOutput() ?: 'Execution error'], 500);
        } catch (ProcessTimedOutException $e) {
            return response()->json(['error' => "Process timed out (Max {$executionTimeout}s)"], 408);
        } catch (\Throwable $e) {
            return response()->json(['error' => "Server Error: " . $e->getMessage()], 500);
        } finally {
            File::deleteDirectory($tempDir);
        }
    }
}
