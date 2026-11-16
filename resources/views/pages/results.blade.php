<x-admin-display-layout title="Results">
    @php
        $leaders = $candidates->filter(fn ($candidate) => $candidate->total_score > 0)->take(3);
    @endphp
    <style>
        .results-page { max-width: 1400px; margin: 0 auto; }
        .results-header { padding: 1rem 0 2rem; border-bottom: 1px solid var(--results-rule); }
        .results-page { --results-rule: #d5d5d5; }
        [data-theme="dark"] .results-page { --results-rule: #3c3c3c; }
        .results-heading { font-size: clamp(2rem, 4vw, 3.25rem); line-height: 1.1; font-weight: 750; letter-spacing: -.035em; }
        .podium-grid { display: grid; gap: 1rem; align-items: end; }
        .podium-card { position: relative; overflow: hidden; min-width: 0; border: 1px solid var(--metal-edge); border-radius: .75rem; background: linear-gradient(145deg, var(--metal-light), var(--metal-mid) 60%, var(--metal-dark)); color: var(--metal-text); padding: 1.5rem; box-shadow: inset 0 1px 0 rgba(255,255,255,.65), 0 5px 14px rgba(0,0,0,.09); }
        .podium-gold { --metal-light: #fff3c5; --metal-mid: #d6ad55; --metal-dark: #a87524; --metal-edge: #a87524; --metal-text: #271a08; }
        .podium-silver { --metal-light: #f7f9fb; --metal-mid: #b9c1ca; --metal-dark: #7d8896; --metal-edge: #7d8896; --metal-text: #17202a; }
        .podium-bronze { --metal-light: #ffe1c5; --metal-mid: #c98250; --metal-dark: #a65e35; --metal-edge: #a65e35; --metal-text: #29150e; }
        .podium-rank { font-size: .75rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .podium-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
        .podium-medal { display: grid; place-items: center; width: 3.5rem; height: 3.5rem; flex: none; border: 1px solid currentColor; border-radius: 50%; box-shadow: inset 0 0 0 4px rgba(255,255,255,.2); font: 700 1.5rem/1 Georgia, serif; }
        .podium-score { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; border-top: 1px solid rgba(0,0,0,.25); margin-top: 1.25rem; padding-top: .85rem; }
        .podium-score-label { font-size: .75rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }

        @media (min-width: 768px) {
            .podium-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .podium-gold { grid-column: 2; grid-row: 1; min-height: 14rem; }
            .podium-silver { grid-column: 1; grid-row: 1; min-height: 12rem; }
            .podium-bronze { grid-column: 3; grid-row: 1; min-height: 11rem; }
        }
        .results-table th, .results-table td { padding: 1rem 1.25rem; }
        .results-table td:last-child, .results-table th:last-child { text-align: right; font-variant-numeric: tabular-nums; }
        .results-page:fullscreen { max-width: none; width: 100%; height: 100%; overflow: auto; padding: clamp(16px, 2vw, 40px); background: var(--admin-background); }
        .results-page:fullscreen [data-results-actions], .results-page:fullscreen [data-results-status] { display: none; }
        @page { size: A4 portrait; margin: 14mm; }
        @media print {
            html, body.admin-shell { background: white !important; color: #111 !important; }
            body > header, body > a, [data-results-actions], [data-results-status], [data-tooltip] { display: none !important; }
            #admin-content { padding: 0; }
            .results-page { max-width: none; font-size: 11px; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .results-header { padding: 0 0 5mm; }
            .results-heading { font-size: 26px; }
            .results-page .admin-muted { color: #525252 !important; }
            .results-page .admin-card { background: white !important; color: #111 !important; border-color: #ccc; box-shadow: none; overflow: visible; }
            .results-page .overflow-x-auto { overflow: visible; }
            .results-page section { margin-top: 6mm; }
            .results-page h2 { font-size: 17px; break-after: avoid; }
            .podium-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3mm; break-inside: avoid; }
            .podium-card { padding: 4mm; box-shadow: none; }
            .podium-card h3 { font-size: 15px; }
            .podium-gold { grid-column: 2; grid-row: 1; min-height: 48mm; }
            .podium-silver { grid-column: 1; grid-row: 1; min-height: 43mm; }
            .podium-bronze { grid-column: 3; grid-row: 1; min-height: 40mm; }
            .podium-top { gap: 2mm; }
            .podium-medal { width: 8mm; height: 8mm; font-size: 15px; }
            .podium-score { flex-wrap: wrap; }
            .podium-score > span:last-child { font-size: 23px; }
            .results-table { width: 100%; color: #111; }
            .admin-shell .results-table th, .admin-shell .results-table td { padding: 2mm 4mm; font-size: 12px; line-height: 1.3; }
            .results-table tr { break-inside: avoid; }
            .results-table thead { display: table-header-group; }
            #scoreTimeline { break-before: page; break-inside: avoid; padding: 4mm; }
            #scoreTimeline svg { min-width: 0 !important; outline: none; }
            #scoreTimeline svg text { font-size: 18px; }
            #scoreTimeline svg g[aria-label] { display: block !important; opacity: 1 !important; }
            #scoreTimeline [data-legend] button { opacity: 1 !important; border: 0; box-shadow: none; padding: 0 2mm; min-height: 6mm; height: auto; font-size: 11px; }
        }
    </style>
    <div class="results-page">
        <header class="results-header">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="admin-muted text-sm font-medium wrap-anywhere">@include('components.title')</p>
                <div data-results-actions class="flex flex-wrap gap-2">
                    <a href="{{ url('/admin/certificates') }}" class="btn btn-outline btn-sm">Certificates</a>
                    <button type="button" data-export-pdf class="btn btn-outline btn-sm">Export PDF</button>
                    <button type="button" data-results-fullscreen class="btn btn-outline btn-sm" title="Press Esc to exit fullscreen">Fullscreen</button>
                </div>
            </div>
            <p data-results-status role="alert" class="mt-3 text-sm" hidden></p>
            <h1 class="results-heading mt-3">Competition results</h1>
            <p class="admin-muted mt-3 text-sm">ACLC Programmers' Guild</p>
        </header>

        <section aria-labelledby="leaders-title" class="mt-8">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
                <h2 id="leaders-title" class="text-2xl font-bold">Top competitors</h2>

            </div>
            @if ($leaders->isNotEmpty())
                <div class="podium-grid">
                    @foreach ($leaders as $candidate)
                        <article class="podium-card {{ [1 => 'podium-gold', 2 => 'podium-silver', 3 => 'podium-bronze'][$loop->iteration] }}">
                            <div class="podium-top">
                                <div class="min-w-0">
                                    <p class="podium-rank">{{ [1 => 'First place', 2 => 'Second place', 3 => 'Third place'][$loop->iteration] }}</p>
                                    <h3 class="mt-3 text-xl font-bold wrap-anywhere">{{ $candidate->fullname }}</h3>
                                </div>
                                <span class="podium-medal" aria-hidden="true">{{ $loop->iteration }}</span>
                            </div>
                            <div class="podium-score">
                                <span class="podium-score-label">Points earned</span>
                                <span class="text-3xl font-mono font-bold tabular-nums">{{ $candidate->total_score }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="admin-card p-6"><p class="font-semibold">The leaderboard is waiting.</p><p class="admin-muted mt-2 text-sm">Competitors will appear here once points have been earned.</p></div>
            @endif
        </section>

        <section aria-labelledby="results-title" class="admin-card mt-8 overflow-hidden">
            <div class="p-5 sm:p-6"><h2 id="results-title" class="text-xl font-bold">Full standings</h2></div>
            <div class="overflow-x-auto">
                <table class="table results-table w-full">
                    <thead class="admin-muted"><tr><th scope="col">Rank</th><th scope="col">Competitor</th><th scope="col">Points</th></tr></thead>
                    <tbody>
                        @forelse ($candidates as $candidate)
                            <tr>
                                <th scope="row" class="tabular-nums">{{ $candidate->total_score == 0 ? 'N/A' : $loop->iteration }}</th>
                                <td class="font-semibold wrap-anywhere">{{ $candidate->fullname }}</td>
                                <td class="font-mono font-semibold">{{ $candidate->total_score }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="admin-muted">No competitors in the active event yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @include('components.score-timeline')

    </div>
    @push('scripts')
        <script src="{{ asset('js/results-export.js') }}" defer></script>
    @endpush
</x-admin-display-layout>
