<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CodeCheckerController extends Controller
{
    public function checkCodedText(Request $request)
    {
        // 1. Validation: Ensure the 'submitted_text' field is present
        $request->validate([
            'submitted_text' => 'required|string',
            // You might add a 'challenge_id' here to select the correct master file
        ]);

        $submittedText = $request->input('submitted_text');

        $subdirectory = 'master-files/';
        $masterFilename = $subdirectory.'challenge_answer_key.txt'; // <-- Define your master file name

        // --- Step 2: Retrieve the Expected Master Text ---

        // Check if the master file exists in the storage directory
        // this local thing is located in the private folder of storage
        if (!Storage::disk('local')->exists($masterFilename)) {
            // Log a critical error if the answer key is missing
            Log::error("Master file not found: {$masterFilename}");
            return response()->json([
                'status' => 'error',
                'message' => 'Challenge key file is missing on the server.'
            ], 500);
        }

        // Read the master content from the storage disk
        $expectedText = Storage::disk('local')->get($masterFilename);

        // --- Step 3: Normalize and Compare the Texts ---

        /*
         * CRITICAL STEP: Normalization
         * Both strings must be normalized before comparison to avoid false failures
         * due to minor differences (like extra spaces or different line endings).
         */

        // Remove leading/trailing whitespace from the whole text
        $submittedText = trim($submittedText);
        $expectedText = trim($expectedText);

        // OPTIONAL: Normalize line endings to LF (Unix-style) and remove multiple newlines/spaces
        $submittedText = preg_replace('/\s+/', ' ', $submittedText);
        $expectedText = preg_replace('/\s+/', ' ', $expectedText);


        if ($submittedText === $expectedText) {
            // Match found!
            return response()->json([
                'status' => 'success',
                'message' => 'Congratulations! The coded text matches the answer key.',
                'match' => true
            ], 200);
        } else {
            // No match
            return response()->json([
                'status' => 'fail',
                'message' => 'The submitted text does not match the expected code.',
                'match' => false
            ], 200);
        }
    }
}
