<div class="bg-base-300 rounded-md p-3">
    <div class="flex flex-row justify-between items-center">
        <p class="text-sm">{{ $submission->candidate_name }}</p>
        <p class="badge badge-xs badge-primary">{{ $submission->challenge_difficulty }}</p>
    </div>
    <p class="">{{ $submission->challenge_title }}</p>
    <p class="mt-2 text-xs"> {{ $submission->submitted_at }}</p>
</div>
