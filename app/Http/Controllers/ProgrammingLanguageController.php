<?php

namespace App\Http\Controllers;

use App\Models\ProgrammingLanguage;
use Illuminate\Http\Request;

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
            'name' => 'required|string|max:255|unique:programming_languages,name',
            'compile_command' => 'nullable|string|max:500',
            'run_command' => 'required|string|max:500',
            'file_extension' => 'required|string|max:50',
        ]);

        ProgrammingLanguage::create($validated);

        return redirect('/admin/programming-languages')->with('success', 'Language added successfully!');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:programming_languages,id',
            'name' => 'required|string|max:255|unique:programming_languages,name,' . $request->id,
            'compile_command' => 'nullable|string|max:500',
            'run_command' => 'required|string|max:500',
            'file_extension' => 'required|string|max:50',
        ]);

        ProgrammingLanguage::where('id', $validated['id'])->update([
            'name' => $validated['name'],
            'compile_command' => $validated['compile_command'],
            'run_command' => $validated['run_command'],
            'file_extension' => $validated['file_extension'],
        ]);

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
