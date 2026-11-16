@extends('layouts.admin')

@section('admin-title', 'Overview')
@section('admin-description', 'Control the competition timer, registration and standings.')

@section('admin-content')
    @if (session('success'))
        <div role="alert" class="alert alert-success w-full mb-3">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div role="alert" class="alert alert-error w-full mb-3">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid min-w-0 gap-6 xl:grid-cols-2">
        <section aria-labelledby="timer-title" class="admin-card flex flex-col items-center justify-center gap-6 p-5 sm:p-6">
            <div class="flex w-full items-center gap-3">
                <span class="admin-icon-badge"><x-admin-icon name="timer" /></span>
                <h2 id="timer-title" class="text-base font-semibold">Competition timer</h2>
            </div>
            <p id="countdown" role="timer" aria-label="Time remaining" class="text-4xl sm:text-6xl font-mono font-semibold tracking-tight text-center tabular-nums">00:00:00</p>
            <div class="flex w-full max-w-sm flex-col items-center gap-5">
                <div id="timeInputContainer" class="grid w-full grid-cols-3 gap-3">
                    <label for="hourInput" class="text-xs font-medium admin-muted">Hours
                        <input type="number" class="input mt-2" id="hourInput" placeholder="00" min="0" max="23">
                    </label>
                    <label for="minInput" class="text-xs font-medium admin-muted">Minutes
                        <input type="number" class="input mt-2" id="minInput" placeholder="00" min="0" max="59">
                    </label>
                    <label for="secInput" class="text-xs font-medium admin-muted">Seconds
                        <input type="number" class="input mt-2" id="secInput" placeholder="00" min="0" max="59">
                    </label>
                </div>
                <div class="flex flex-wrap justify-center gap-3">
                    <button id="getTimerBtn" class="btn btn-outline" type="button">Refresh</button>
                    <button id="setTimerBtn" class="btn btn-primary" type="button">Set timer</button>
                    <button id="resetTimerBtn" class="btn btn-outline" type="button">Reset</button>
                </div>
                <p class="text-sm text-center admin-muted">Setting a duration starts the competition timer.</p>
            </div>
        </section>

        <section aria-labelledby="standings-title" class="admin-card overflow-hidden">
            <div class="flex items-center gap-3 border-b border-base-300 p-5 sm:p-6">
                <span class="admin-icon-badge"><x-admin-icon name="trophy" /></span>
                <div class="min-w-0">
                    <h2 id="standings-title" class="text-base font-semibold">Standings</h2>
                    <p class="mt-1 text-sm admin-muted wrap-anywhere">@include('components.title')</p>
                </div>
            </div>
            <div class="max-h-96 overflow-auto">
            <table class="table table-zebra">
                <!-- head -->
                <thead class=" text-center admin-muted">
                    <tr>
                        <th class="bg-base-200 rounded-tl-xl">Ranking</th>
                        <th class="bg-base-200">Name</th>
                        <th class="bg-base-200">Points</th>
                        <th class="bg-base-200 rounded-tr-xl">Group</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- genereates the rank --}}
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
                            <td>
                                <p class="badge badge-primary badge-soft">{{ $candidate->group }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="admin-muted">No competitors in the active event yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </section>
    </div>

    @php
        $regOpen = DB::table('options')->where('option_name', 'registration_open')->value('option_value');
    @endphp
    <section aria-labelledby="registration-title" class="admin-card mt-6 flex flex-wrap items-center justify-between gap-5 p-5 sm:p-6">
        <div>
            <h2 id="registration-title" class="text-base font-semibold">Registration</h2>
            <p class="mt-2 text-sm admin-muted">Allow competitors to create accounts for the active event.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="badge">{{ $regOpen === '1' ? 'Open' : 'Closed' }}</span>
            <form method="POST" action="{{ url('/admin/toggle-registration') }}">
                @csrf
                <button type="submit" class="btn {{ $regOpen === '1' ? 'btn-outline' : 'btn-primary' }}">
                    {{ $regOpen === '1' ? 'Close registration' : 'Open registration' }}
                </button>
            </form>
        </div>
    </section>
@endsection

@section('admin-scripts')
    @include('components.countdown-script')
@endsection
