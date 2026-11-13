<?php

namespace App\Http\Controllers;

use App\Models\Challenges;
use App\Models\ProgrammingLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProgrammingLanguageController extends Controller
{
    public function index()
    {
        $languages = ProgrammingLanguage::all();
        return view('admin.programming-languages', compact('languages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:programming_languages,name',
            'compile_command' => 'nullable|string|max:500',
            'run_command' => 'required|string|max:500',
            'file_extension' => 'required|string|max:50',
        ]);

        ProgrammingLanguage::create($validated);

        return redirect('/admin/programming-languages')->with('success', 'Language added successfully!');
    }

    public function update(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:programming_languages,id']);
        $language = ProgrammingLanguage::findOrFail($request->integer('id'));
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('programming_languages', 'name')->ignore($language)],
            'compile_command' => 'nullable|string|max:500',
            'run_command' => 'required|string|max:500',
            'file_extension' => 'required|string|max:50',
        ]);

        DB::transaction(function () use ($language, $validated) {
            $language->update([
                'name' => $validated['name'],
                'compile_command' => $validated['compile_command'] ?? null,
                'run_command' => $validated['run_command'],
                'file_extension' => $validated['file_extension'],
            ]);

            // The name is also the editor language identifier cached on linked challenges.
            Challenges::where('language_id', $language->id)->update(['language' => $validated['name']]);
        });

        return redirect('/admin/programming-languages')->with('success', 'Language updated successfully!');
    }

    public function destroy(string $id)
    {
        ProgrammingLanguage::destroy($id);
        return redirect('/admin/programming-languages')->with('success', 'Language deleted successfully.');
    }

    public function api_list()
    {
        return response()->json(ProgrammingLanguage::all());
    }
}
