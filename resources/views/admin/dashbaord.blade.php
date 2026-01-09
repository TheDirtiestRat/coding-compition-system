@extends('layouts.admin')

@section('admin-content')
    <div class="flex flex-row gap-3 w-full">
        {{-- candidate ranking --}}
        <div class="basis-full bg-base-100 rounded-xl p-3">
        </div>

        <div class="basis-full bg-base-100 rounded-xl p-3">
            <table class="table table-zebra">
                <!-- head -->
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Points</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($candidates as $candidate)
                        <tr>
                            <th>{{ $candidate->id }}</th>
                            <td>{{ $candidate->fullname }}</td>
                            <td>{{ 32342 }}</td>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
