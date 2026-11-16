<x-admin-display-layout title="Certificates">
    <style>
        .certificate-workspace { max-width: 1100px; margin: 0 auto; }
        .certificate { --certificate-accent: #334155; background: #fff; color: #192230; border: 1px solid #c9cdd3; padding: clamp(24px, 5vw, 56px); min-height: 660px; display: flex; flex-direction: column; justify-content: space-between; gap: 32px; margin-top: 28px; box-shadow: 0 8px 24px rgb(0 0 0 / 6%); }
        .certificate-winner { --certificate-accent: #87651f; border-top: 6px solid var(--certificate-accent); }
        .certificate-issuer { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; font-size: 14px; font-weight: 600; color: var(--certificate-accent); }
        .certificate-body { text-align: center; }
        .certificate-title { font: 400 clamp(28px, 4vw, 40px)/1.2 Georgia, serif; }
        .certificate-name { margin: 24px 0; font: 400 clamp(28px, 4vw, 46px)/1.2 Georgia, serif; overflow-wrap: anywhere; }
        .certificate-copy { font-size: 16px; line-height: 1.75; }
        .certificate-event { display: block; font-weight: 700; margin-top: 12px; overflow-wrap: anywhere; }
        .certificate-footer { display: flex; justify-content: space-between; flex-wrap: wrap; align-items: end; gap: 28px; font-size: 13px; }
        .certificate-signature { border-top: 1px solid #89919d; padding-top: 8px; min-width: 200px; }
        @page { size: A4 landscape; margin: 10mm; }
        @media print {
            html, body.admin-shell { background: white !important; color: #192230 !important; }
            body > header, body > a, .certificate-controls { display: none !important; }
            #admin-content { padding: 0; }
            .certificate-workspace { max-width: none; }
            .certificate { width: 277mm; height: 189mm; min-height: 0; margin: 0; padding: 12mm; box-shadow: none; break-inside: avoid; break-after: page; box-sizing: border-box; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .certificate:last-child { break-after: auto; }
            .certificate-title { font-size: 34px; }
            .certificate-name { font-size: 42px; }
            .certificate-name-long { font-size: 28px; }
        }
    </style>
    <div class="certificate-workspace">
        <div class="certificate-controls">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h1 class="text-2xl font-bold">Certificates</h1><p class="admin-muted mt-2 text-sm">{{ $event?->title ?? 'No active event' }}</p></div>
                <a href="{{ url('/admin/results') }}" class="btn btn-ghost btn-sm">Back to results</a>
            </div>
            @if ($errors->any())
                <div role="alert" class="admin-card mt-5 p-4">{{ $errors->first() }}</div>
            @endif
            <form data-certificate-form method="GET" action="{{ url('/admin/certificates') }}" class="admin-card mt-5 flex flex-wrap items-end gap-4 p-5">
                <div class="flex flex-col gap-2"><label for="certificateType" class="text-sm font-semibold">Certificate</label>
                    <select id="certificateType" name="type" class="select">
                        <option value="participation" @selected($type === 'participation')>Participation</option>
                        <option value="winner" @selected($type === 'winner')>Winners (top three)</option>
                    </select>
                </div>
                <div class="flex flex-col gap-2"><label for="certificateCandidate" class="text-sm font-semibold">Competitor</label>
                    <select id="certificateCandidate" name="candidate_id" class="select">
                        <option value="">All eligible competitors</option>
                        @foreach ($candidates as $candidate)
                            <option value="{{ $candidate->id }}" @selected((int) $selectedCandidate === (int) $candidate->id)>{{ $candidate->fullname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2"><label for="certificateDate" class="text-sm font-semibold">Issue date</label><input id="certificateDate" name="date" type="date" value="{{ $date }}" required class="input"></div>
                <button type="submit" class="btn btn-outline">Generate certificates</button>
                <button type="button" data-export-pdf class="btn btn-primary" @disabled($certificates->isEmpty())>Print / Save PDF</button>
            </form>
            <p data-certificate-status role="status" class="admin-muted mt-3 text-sm">{{ $certificates->count() }} {{ Str::plural('certificate', $certificates->count()) }}. Winners reflect the current standings.</p>
        </div>
        @forelse ($certificates as $certificate)
            <article class="certificate {{ $type === 'winner' ? 'certificate-winner' : '' }}" aria-label="Certificate for {{ $certificate['candidate']->fullname }}">
                <header class="certificate-issuer"><span>ACLC Programmers' Guild</span><span>Coding Competition</span></header>
                <div class="certificate-body">
                    <h2 class="certificate-title">Certificate of {{ $type === 'winner' ? 'Achievement' : 'Participation' }}</h2>
                    <p class="certificate-copy" style="margin-top:24px">Presented to</p>
                    <p class="certificate-name {{ mb_strlen($certificate['candidate']->fullname) > 60 ? 'certificate-name-long' : '' }}">{{ $certificate['candidate']->fullname }}</p>
                    <p class="certificate-copy">
                        @if ($type === 'winner')
                            For earning <strong>{{ [1 => 'first place', 2 => 'second place', 3 => 'third place'][$certificate['rank']] }}</strong> with {{ $certificate['candidate']->total_score }} points in
                        @else
                            For participating in
                        @endif
                        <span class="certificate-event">{{ $event->title }}</span>
                    </p>
                </div>
                <footer class="certificate-footer"><div class="certificate-signature">Competition organizer</div><div>Issued {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</div></footer>
            </article>
        @empty
            <p class="certificate-controls admin-muted mt-8">No eligible competitors for this selection.</p>
        @endforelse
    </div>
    @push('scripts')
        <script src="{{ asset('js/results-export.js') }}" defer></script>
    @endpush
</x-admin-display-layout>
