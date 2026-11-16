<section aria-labelledby="progress-title" class="admin-card mt-8 p-5 sm:p-6" id="scoreTimeline" data-series="{{ json_encode($scoreTimeline) }}">
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <h2 id="progress-title" class="text-xl font-bold">Points over time</h2>
        <span class="admin-muted text-sm">All competitors</span>
    </div>
    <p class="admin-muted mt-2 text-sm">Each dot marks a score award. Earlier scores without a recorded award time are excluded.</p>
    <p data-empty class="admin-muted mt-6 text-sm" hidden>No scoring events recorded yet.</p>
    <div data-chart-content hidden>
        <div data-plot class="mt-6" style="position:relative">
            <div class="overflow-x-auto">
                <svg data-chart viewBox="0 0 960 340" role="img" tabindex="0" aria-label="Cumulative points by competitor over time. Use left and right arrow keys to inspect scoring times." aria-describedby="scoreTimelineTooltip" style="width:100%;min-width:640px;height:auto"></svg>
            </div>
            <div id="scoreTimelineTooltip" data-tooltip role="tooltip" class="bg-base-100 border border-base-300 rounded-lg shadow-lg" style="position:absolute;z-index:10;width:240px;max-width:calc(100% - 16px);max-height:65vh;overflow:auto;padding:12px;font-size:12px" hidden></div>
        </div>
        <div data-legend class="mt-3 flex flex-wrap gap-2" aria-label="Show or hide competitors"></div>
    </div>
</section>
@push('scripts')
    <script src="{{ asset('js/score-timeline.js') }}" defer></script>
@endpush
