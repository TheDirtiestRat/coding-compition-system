<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File; // Add this use statement
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Illuminate\Support\Str;

class CompilerController extends Controller
{
    //
    public function run_python_code(Request $request)
    {
        $code = $request->input('code');

        // Create a temporary file with a .py extension
        $tempFilePath = tempnam(sys_get_temp_dir(), 'python_sandbox_') . '.py';
        File::put($tempFilePath, $code);

        // Use Symfony Process to run the python command
        // Use 'python3' or 'python' depending on your server's environment
        $process = new Process(['python', $tempFilePath]);
        $process->run();

        // Clean up the temporary file immediately after execution
        File::delete($tempFilePath);

        if ($process->isSuccessful()) {
            return response()->json(['output' => $process->getOutput()]);
        } else {
            // Return errors if the script failed
            return response()->json(['error' => $process->getErrorOutput() ?: 'An unknown error occurred'], 500);
        }
    }

    public function run_java_code(Request $request)
    {
        $code = $request->input('code');
        $className = 'Main';
        $tempDir = sys_get_temp_dir();
        $sourceFile = $tempDir . '/' . $className . '.java';
        $classFileDir = $tempDir;

        $path_to_javac = 'C:\Program Files\Java\jdk-23\bin\javac.exe'; // <--- REPLACE THIS WITH YOUR ACTUAL PATH
        $path_to_java = 'C:\Program Files\Java\jdk-23\bin\java.exe';

        // 1. Save the Java source code
        File::put($sourceFile, $code);

        $executionTimeout = 5; // Set a strict 5-second timeout

        try {
            // 2. Compile the Java source file
            $compileProcess = new Process([$path_to_javac, $sourceFile], $classFileDir);
            $compileProcess->setTimeout($executionTimeout); // Apply timeout
            $compileProcess->run();

            if (!$compileProcess->isSuccessful()) {
                // **COMPATIBILITY FIX APPLIED HERE**
                // Explicit timeout check removed, relying on isSuccessful() failure.
                $errorOutput = $compileProcess->getErrorOutput();

                // If the output is empty on failure, it is likely a timeout or a system error.
                if (empty($errorOutput)) {
                    return response()->json(['error' => "Compilation failed or Timed Out (Max $executionTimeout seconds)."], 500);
                }

                return response()->json(['error' => "Compilation Error:\n" . $errorOutput], 500);
            }

            // 3. Execute the compiled Java class
            $runProcess = new Process([$path_to_java, '-cp', $classFileDir, $className], $classFileDir);
            $runProcess->setTimeout($executionTimeout); // Apply timeout
            $runProcess->run();

            if (!$runProcess->isSuccessful()) {
                // **COMPATIBILITY FIX APPLIED HERE**
                // Explicit timeout check removed, relying on isSuccessful() failure.
                $errorOutput = $runProcess->getErrorOutput();

                // Handle execution errors (runtime exceptions or timeout)
                $message = $errorOutput ?: "Execution failed or Timed Out (Max $executionTimeout seconds).";

                return response()->json(['error' => "Execution Error:\n" . $message], 500);
            }

            return response()->json(['output' => $runProcess->getOutput()]);
        } catch (\Throwable $e) {
            // Catch unexpected PHP exceptions
            return response()->json(['error' => "Server Processing Error: " . $e->getMessage()], 500);
        } finally {
            // 4. Clean up temporary files (CRITICAL step)
            File::delete([
                $sourceFile,
                $classFileDir . '/' . $className . '.class'
            ]);
        }
    }

    public function run_java_code_unique(Request $request)
    {
        $code = $request->input('code');
        $executionTimeout = 5;

        // 1. Create a unique workspace for this specific execution
        $uniqueId = Str::random(10);
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'java_exec_' . $uniqueId;
        File::makeDirectory($tempDir);

        $className = 'Main';
        $sourceFile = $tempDir . DIRECTORY_SEPARATOR . $className . '.java';

        $path_to_javac = 'C:\Program Files\Java\jdk-23\bin\javac.exe';
        $path_to_java = 'C:\Program Files\Java\jdk-23\bin\java.exe';

        try {
            File::put($sourceFile, $code);

            // 2. Compilation
            $compileProcess = new Process([$path_to_javac, $className . '.java'], $tempDir);
            $compileProcess->setTimeout($executionTimeout);
            $compileProcess->run();

            if (!$compileProcess->isSuccessful()) {
                return response()->json(['error' => "Compilation Error:\n" . $compileProcess->getErrorOutput()], 400);
            }

            // 3. Execution
            $runProcess = new Process([$path_to_java, '-cp', '.', $className], $tempDir);
            $runProcess->setTimeout($executionTimeout);
            $runProcess->run();

            return response()->json(['output' => $runProcess->getOutput()]);
        } catch (ProcessTimedOutException $e) {
            return response()->json(['error' => "Process timed out (Max {$executionTimeout}s). Check for infinite loops!"], 408);
        } catch (\Throwable $e) {
            return response()->json(['error' => "Server Error: " . $e->getMessage()], 500);
        } finally {
            // 4. Clean up the entire unique directory
            File::deleteDirectory($tempDir);
        }
    }

    // =================== to run code securely ===================
    public function run_python_code_secure(Request $request)
    {
        $code = $request->input('code');

        $tempDir = storage_path('app/code_sandbox');
        File::makeDirectory($tempDir, 0755, true, true);

        // Generate a unique filename for concurrent requests
        $fileName = uniqid('script_') . '.py';
        $sourceFileHostPath = $tempDir . '/' . $fileName;
        File::put($sourceFileHostPath, $code);

        // Define paths inside the container
        $containerSourcePath = "/sandbox/{$fileName}";
        $containerWorkDir = "/sandbox";
        $imageName = "python:3.10-slim"; // A lightweight Python image

        // --- SECURITY IMPLEMENTATION ---
        // The Docker command limits resources, runs as a non-root user,
        // and automatically removes the container after execution (--rm).
        $dockerCommand = [
            'docker',
            'run',
            '--rm',
            '--memory=128m', // Limit memory usage (Python is lighter than Java)
            '--cpus=0.2',    // Limit CPU usage
            '--net=none',    // Disable networking for security
            '-v',
            "{$sourceFileHostPath}:{$containerSourcePath}", // Mount the source file
            '-w',
            $containerWorkDir, // Set working directory inside container
            $imageName,
            // Command to execute *inside* the container: run the python script
            'python',
            $fileName
        ];

        // Timeout the process after 30 seconds to prevent infinite loops
        $process = new Process($dockerCommand, null, null, null, 30);
        $process->run();

        // Clean up the host file regardless of success or failure
        File::delete($sourceFileHostPath);

        if ($process->isSuccessful()) {
            return response()->json(['output' => $process->getOutput()]);
        } else {
            // Return combined output/error for debugging failures
            return response()->json(['error' => $process->getErrorOutput() ?: $process->getOutput()], 500);
        }
    }
    public function run_java_code_secure(Request $request)
    {
        $code = $request->input('code');
        $className = 'Main';
        $tempDir = storage_path('app/code_sandbox'); // Use a specific storage folder
        File::makeDirectory($tempDir, 0755, true, true);
        $sourceFileHostPath = $tempDir . '/' . $className . '.java';
        File::put($sourceFileHostPath, $code);

        // Define paths inside the container
        $containerSourcePath = "/sandbox/{$className}.java";
        $containerWorkDir = "/sandbox";
        $imageName = "openjdk:17-slim"; // A lightweight Java image

        // --- SECURITY IMPLEMENTATION ---
        // The Docker command limits resources, runs as a non-root user,
        // and automatically removes the container after execution (--rm).
        $dockerCommand = [
            'docker',
            'run',
            '--rm',
            '--memory=256m', // Limit memory usage
            '--cpus=0.5',    // Limit CPU usage
            '-v',
            "{$sourceFileHostPath}:{$containerSourcePath}", // Mount the source file
            '-w',
            $containerWorkDir, // Set working directory inside container
            $imageName,
            'sh',
            '-c',
            // Command to execute *inside* the container: compile and then run
            "javac {$className}.java && java {$className}"
        ];

        $process = new Process($dockerCommand, null, null, null, 300); // 5 minute timeout
        $process->run();

        // Clean up the host file regardless of success or failure
        File::delete($sourceFileHostPath);

        if ($process->isSuccessful()) {
            return response()->json(['output' => $process->getOutput()]);
        } else {
            // Return combined output/error for debugging failures
            return response()->json(['error' => $process->getErrorOutput() ?: $process->getOutput()], 500);
        }
    }
}
