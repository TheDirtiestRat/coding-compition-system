<?php

namespace App\Http\Controllers;

use App\Models\ProgrammingLanguage;
use App\Services\CodeRunner;
use Illuminate\Http\Request;

class CompilerController extends Controller
{
    public function run_python_code(Request $request)
    {
        return $this->run_code($request, ProgrammingLanguage::where('name', 'python')->where('is_active', true)->first());
    }

    public function run_java_code_unique(Request $request)
    {
        return $this->run_code($request, ProgrammingLanguage::where('name', 'java')->where('is_active', true)->first());
    }

    public function run_code_with_language(Request $request)
    {
        $validated = $request->validate([
            'language_id' => 'required|integer',
        ]);

        $language = ProgrammingLanguage::where('is_active', true)->find($validated['language_id']);

        return $this->run_code($request, $language);
    }

    private function run_code(Request $request, ?ProgrammingLanguage $language)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'input' => 'nullable|string',
        ]);

        if (! $language) {
            return response()->json(['error' => 'Programming language not found or inactive'], 404);
        }

        try {
            $output = app(CodeRunner::class)->run($validated['code'], $language, $validated['input'] ?? '');

            return response()->json(['output' => $output]);
        } catch (\RuntimeException $e) {
            $status = in_array($e->getCode(), [400, 408, 422, 500], true) ? $e->getCode() : 500;

            return response()->json(['error' => $e->getMessage()], $status);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => 'Unable to execute code.'], 500);
        }
    }
}
