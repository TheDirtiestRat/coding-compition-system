@extends('layouts.admin')

@section('admin-content')
    {{-- alert --}}
    @if (session('success') || session('error') || $errors->any())
        <div id="alert_container" class="w-full flex-col gap-1 flex mb-3">
            @if (session('success'))
                <div role="alert" id="alert_success" class="alert alert-success w-full flex">
                    <span id="success_alert_text">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div role="alert" id="alert_error" class="alert alert-error w-full flex">
                    <span id="success_alert_text">{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div role="alert" id="alert_error" class="alert alert-error w-full flex">
                    <ul class="p-2 px-3 rounded-b-xl">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="flex flex-row gap-3 w-full">
        <!-- Submissions List -->
        <div class="basis-1/2 max-h-screen bg-base-100 rounded-xl p-3">
            <p class="mb-2 text-2xl font-medium text-center">Submissions</p>

            <!-- List Container -->
            <div class="flex flex-col gap-2 h-[500px] overflow-y-auto">
                @foreach (array_fill(0, 20, "hellfasfasfso") as $challenge)
                    @include("components.submission", [$challenge])
                @endforeach
            </div>
        </div>

        {{-- Submitted Code --}}
        <div class="basis-full flex flex-col bg-base-100 rounded-xl p-3 gap-4">
            <div>
                <div class="flex flex-row justify-between items-center">
                    <p class="text-2xl">Candidate Name</p>
                    <p class="text-sm">Submitted Date</p>
                </div>
                <p class="">Challenge Name</p>
            </div>

            <!-- Code Container -->
            <div class="bg-gray-900 h-full rounded-lg p-4">
                <div class="flex flex-row justify-between items-center mb-3">
                    <p class="text-gray-400 text-sm font-mono">Java</p>
                    <button class="btn btn-sm btn-ghost">Copy Code</button>
                </div>
                <pre class="text-gray-100 text-sm font-mono overflow-x-auto">
                                        <code></code>
                                   </pre>

            </div>

            <!-- Submit Input and Button -->
            <div class="space-x-4">
                <label for="score">Score: </label>
                <input type="number" name="score" id="score" min="0" class="input input-sm max-w-32">
                <button type="button" class="btn btn-sm btn-primary">Submit</button>
            </div>
        </div>
    </div>
@endsection

@section('admin-scripts')
    <script>
        console.log(editorInstance)
    </script>
@endsection
