<x-admin-display-layout title="Results">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Competition results</h1>
        <p class="mt-2 text-sm admin-muted wrap-anywhere">@include('components.title')</p>
    </div>
    <div class="grid min-w-0 items-start gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
        <section aria-label="Top competitors" class="grid gap-4 sm:grid-cols-3 xl:grid-cols-1">
            @foreach ($candidates->take(3) as $candidate)
                <article class="admin-card min-w-0 p-5 sm:p-6">
                    <p class="text-xs font-medium admin-muted">Rank {{ $candidate->total_score == 0 ? 'N/A' : $loop->iteration }}</p>
                    <h2 class="mt-3 text-lg font-semibold wrap-anywhere">{{ $candidate->fullname }}</h2>
                    <p class="mt-4 text-3xl font-mono tabular-nums">{{ $candidate->total_score }} <span class="text-sm font-sans admin-muted">points</span></p>
                </article>
            @endforeach
        </section>
        <section aria-labelledby="results-title" class="admin-card overflow-hidden">
            <h2 id="results-title" class="border-b border-base-300 p-5 text-base font-semibold">Standings</h2>
            <div class="overflow-x-auto">
            <table class="table table-zebra">
                <!-- head -->
                <thead class=" text-center admin-muted">
                    <tr>
                        <th class="bg-base-200 rounded-tl-xl">Ranking</th>
                        <th class="bg-base-200">Name</th>
                        <th class="bg-base-200 rounded-tr-xl">Points</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $rank = 0;
                    @endphp
                    @forelse ($candidates as $candidate)
                        @php
                            $rank += 1;
                        @endphp
                        <tr class="text-center">
                            @if ($candidate->total_score == 0)
                                <th class="admin-muted font-light">N/A</th>
                            @else
                                <th>{{ $rank }}</th>
                            @endif
                            <td>{{ $candidate->fullname }}</td>
                            <td>{{ $candidate->total_score }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="admin-muted">No competitors in the active event yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </section>
    </div>
</x-admin-display-layout>
