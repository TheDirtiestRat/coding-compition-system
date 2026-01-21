<div
    onclick="view_submission('{{ $submission->id }}')"
    class="bg-base-300 rounded-md p-3 hover:bg-base-200 hover:cursor-grab"
>
    <div class="flex flex-row justify-between items-center">
        <p class="text-sm">{{ $submission->candidate_name }}</p>
        <p class="badge badge-xs badge-primary">{{ $submission->challenge_difficulty }}</p>
    </div>
    <p class="">{{ $submission->challenge_title }}</p>
    <p class="mt-2 text-xs"> {{ $submission->submitted_at }}</p>
</div>
