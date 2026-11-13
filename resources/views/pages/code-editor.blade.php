@extends('layouts.code-editor')

@section('code-content')


    <textarea hidden name="submittedCode" id="submittedCode">{{ $details->code ?? '' }}</textarea>

     @isset($details->code)
    @if ($details->status === 'submitted')
        <div role="alert" class="fixed bottom-4 left-4 z-50 max-w-sm alert alert-info shadow-lg flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-info-circle">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                <path d="M12 9h.01" />
                <path d="M11 12h1v4h1" />
            </svg>

            <span class="text-sm">
                Submitted for review. You may re-submit until it&apos;s given a score.
            </span>
        </div>
    @elseif ($details->status === 'reviewed')
        <div role="alert"
            class="fixed bottom-4 left-4 z-50 max-w-sm alert alert-success shadow-lg flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-info-circle">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                <path d="M12 9h.01" />
                <path d="M11 12h1v4h1" />
            </svg>

            <p class="text-sm">
                Code has already been reviewed with a score of <br>
            <p class="text-xl font-bold">{{ $details->score }}/{{ $details->points }}</p>
            </p>
        </div>
    @endif
    @endisset

    <div class="flex flex-col-reverse md:flex-row h-full w-full gap-4">
        {{-- LEFT: Editor --}}
        <div class="card rounded-2xl bg-base-300 basis-full shadow flex flex-col overflow-hidden">
            {{-- Toolbar --}}
            <div class="flex flex-row flex-nowrap items-center gap-2 p-3 bg-base-300 rounded-xl">
                <select class="select select-sm select-bordered basis-full" id="theme-selector">
                    <option value="vs-light">Light</option>
                    <option value="vs-dark" selected>Dark</option>
                </select>

                <select class="select select-sm select-bordered basis-full" id="language-selector"
                    data-language="{{ $details->language ?? '' }}" data-language-id="{{ $details->language_id ?? '' }}">
                    <option value="">Loading...</option>
                </select>

                <button id="run_code_btn" class="btn btn-sm btn-primary w-[200px]">
                    Run
                </button>
                @isset($details)
                @if ($details->status !== 'reviewed')
                    <button id="submit_code_btn" class="btn rounded-tr-xl btn-sm btn-secondary w-[200px]"
                        @isset($details)
                    value="{{ $details->id }}" @endisset>
                        Submit
                    </button>
                @endif
                @endisset
            </div>

            {{-- Editor --}}
            <div id="monaco-editor-container" class="w-full h-[500px] bg-base-100"></div>

            {{-- Footer --}}
            <div class="px-3 p-2 text-sm text-base-content/60 bg-base-300 rounded-b-xl">
                Write your solution here. Use <kbd class="kbd kbd-sm">Run</kbd> to test before submitting.
            </div>
        </div>

        {{-- RIGHT: Problem & Output --}}
        <div class="flex flex-col w-full gap-1">
            {{-- Alerts --}}
            @isset($details)
                @if ($details->status === 'submitted')
                    <div role="alert" class="alert alert-info shadow">
                        <span>Your solution has been submitted for review. Please wait for the admin to score it.</span>
                    </div>
                @elseif ($details->status === 'reviewed')
                    <div role="alert" class="alert alert-success shadow">
                        <span>Your solution has been reviewed. Score: {{ $details->score }} / {{ $details->points }}</span>
                    </div>
                @endif
            @endisset

            <div id="alert_container" class="w-full flex flex-col gap-2 mb-3 hidden">
                <div role="alert" id="alert_success" class="alert alert-success shadow hidden"></div>
                <div role="alert" id="alert_error" class="alert alert-error shadow hidden"></div>
            </div>
            {{-- Problem details --}}
            <div class="card rounded-2xl bg-base-300 shadow-lg p-4 space-y-2">
                @isset($details)
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold">
                            {{ $details->title }}
                        </h2>

                        {{-- Next challenge --}}
                        @isset($next_challenge_id)
                            <div class="flex justify-end">
                                <a href="{{ url('/code-editor/' . $next_challenge_id) }}">
                                    <button class="btn btn-sm btn-soft rounded-xl">
                                        Next Challenge →
                                    </button>
                                </a>
                            </div>
                        @endisset
                    </div>

                    <div class="flex items-center w-full flex-row gap-3">
                        <p class="text-sm text-base-content/70 text-nowrap">
                            Points: {{ $details->points }}
                        </p>
                        <div class="divider basis-full"></div>
                        <span class="badge badge-outline">
                            {{ ucfirst($details->difficulty) }}
                        </span>
                    </div>


                    @if ($details->relationLoaded('photos') && $details->photos->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($details->photos as $photo)
                                <img src="{{ $photo->url ?? asset('storage/' . $photo->photo_path) }}" alt="Challenge photo" class="max-h-48 rounded-xl object-cover" />
                            @endforeach
                        </div>
                    @endif

                    <div class="prose max-w-none text-base-content">
                        {!! nl2br(e($details->description)) !!}
                    </div>
                @else
                    <span class="text-base-content/60">
                        Problem description will appear here.
                    </span>
                @endisset
            </div>

            <div class="card rounded-2xl bg-base-300 shadow-lg p-4 space-y-2">
                <label for="code_input" class="font-semibold">Program input (stdin)</label>
                <textarea id="code_input" name="input" rows="4"
                    class="textarea textarea-bordered w-full font-mono text-sm"
                    placeholder="Enter input for your program"></textarea>
            </div>

            {{-- Output --}}
            <div class="divider">Code Output</div>

            <div id="code_output_area"
                class="card rounded-2xl bg-base-300 shadow-lg p-4 font-mono text-sm whitespace-pre-wrap break-all h-full overflow-y-auto max-h-[400px]">
                This is where the output of the code will be.
            </div>
        </div>
    </div>

    {{-- Instructions --}}
    <div class="mt-4 p-3 text-sm bg-base-200 rounded-xl text-base-content/70">
        <strong>Instructions</strong><br>
        Write and test your solution in the editor. When ready, submit your code for review to earn points.
    </div>
@endsection
