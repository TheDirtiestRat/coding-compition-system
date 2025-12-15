<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashbaord');
    }

    public function candidates()
    {
        $candidates = Candidates::with('user')->latest()->get();

        return view('admin.candidates', compact('candidates'));
    }

    public function add_candidate(Request $request)
    {
        $validatedData = $request->validate([
            'candidate_name' => 'required|string|max:255',
            'username' => 'required|string|max:128',
            'user_password' => 'required',
        ]);

        try {
            $user = User::factory()->create([
                'name' => trim($validatedData['username']),
                'email' => trim(strtolower($validatedData['username'])) . '@hungergame.com',
                'password' => $validatedData['user_password'],
            ]);

            $candidate = Candidates::create([
                'fullname' => $validatedData['candidate_name'],
                'group' => "none",
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            return redirect('/admin/candidates')->with('error', 'Error creating competitor: ' . $e->getMessage());
        }

        // if (!$user || !$candidate) {
        //     return redirect('/admin/candidates')->with('error', 'Failed to create a Competitor.');
        // }

        return redirect('/admin/candidates')->with('success', 'A Competitor is added!');
    }

    public function update_candidate(Request $request)
    {
        $validatedData = $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'edit_name' => 'required|string|max:255',
            'edit_username' => 'required|string|max:128',
            'edit_user_password' => 'required',
        ]);

        try {
            $candidate = Candidates::findOrFail($validatedData['candidate_id']);
            $user = $candidate->user;

            $user->update([
                'name' => trim($validatedData['edit_username']),
                'email' => trim(strtolower($validatedData['edit_username'])) . '@hungergame.com',
                'password' => !empty($validatedData['edit_user_password']) ? $validatedData['edit_user_password'] : $user->password,
            ]);

            if ($validatedData['edit_user_password']) {
                $user->update([
                    'password' => $validatedData['edit_user_password'],
                ]);
            }

            $candidate->update([
                'fullname' => $validatedData['edit_name'],
            ]);
        } catch (\Exception $e) {
            return redirect('/admin/candidates')->with('error', 'Error updating competitor: ' . $e->getMessage());
        }

        return redirect('/admin/candidates')->with('success', 'Competitor updated successfully!');
    }

    public function delete_candidate(string $candidate_id)
    {
        // Delete the candidate record
        Candidates::destroy($candidate_id);

        // Redirect back with a success message
        return redirect('/admin/candidates')->with('success', 'User and Candidate deleted successfully.');
    }

    public function challenges()
    {
        $challenges = Challenges::latest()->get();

        return view('admin.challenges', compact('challenges'));
    }
}
