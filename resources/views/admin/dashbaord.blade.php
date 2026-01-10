@extends('layouts.admin')

@section('admin-content')
    <div class="flex flex-row gap-3 w-full">
        {{-- candidate ranking --}}
        <div class="basis-full bg-base-100 rounded-xl p-3 h-80">
        </div>

        <div class="basis-full bg-base-100 rounded-xl p-3 h-80 overflow-y-scroll">
            <table class="table table-zebra">
                <!-- head -->
                <thead class="bg-slate-700/50 text-center text-neutral-200">
                    <tr>
                        <th>Ranking</th>
                        <th>Name</th>
                        <th>Points</th>
                        <th>Group</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($candidates as $candidate)
                        <tr class="text-center">
                            @if ($candidate->total_points == 0)
                                <th class="text-neutral-400 font-light">N/A</th>
                            @else
                                <th>{{ $candidate->rank }}</th>
                            @endif
                            <td>{{ $candidate->fullname }}</td>
                            <td>{{ $candidate->total_points }}</td>
                            <td><p class="badge badge-primary badge-soft">{{ $candidate->group }}</p></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
