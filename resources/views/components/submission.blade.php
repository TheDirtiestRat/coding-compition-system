<button type="button"
    onclick="view_submission('{{ $submission->id }}')"
    class="admin-submission h-fit"
>
    <span class="flex flex-row justify-between items-center">
        <span class="text-sm">{{ $submission->candidate_name }}</span>
        <span class="badge badge-xs badge-primary">{{ $submission->challenge_difficulty }}</span>
    </span>
    <span class="mt-2 block font-medium">{{ $submission->challenge_title }}</span>
    <span class="mt-2 block text-xs admin-muted"> {{ $submission->submitted_at }}</span>
</button>
