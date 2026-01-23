@extends('layouts.code-editor')

@section('code-content')
    {{-- Alerts --}}
    <div id="alert_container" class="w-full flex flex-col gap-2 mb-3 hidden">
        <div role="alert" id="alert_success" class="alert alert-success shadow w-fit mx-auto hidden">
            <span id="success_alert_text">Code Verified! Points Added +999</span>
        </div>

        <div role="alert" id="alert_info" class="alert alert-info shadow w-fit mx-auto hidden">
            <span id="info_alert_text">Information Alert Message</span>
        </div>

        <div role="alert" id="alert_error" class="alert alert-error shadow w-fit mx-auto hidden">
            <span>Code Verification Failed.</span>
        </div>
    </div>

    <div class="flex flex-col-reverse md:flex-row h-full w-full gap-4">
        {{-- LEFT: Editor --}}
        <div class="card bg-base-300 basis-full shadow-lg flex flex-col overflow-hidden">
            {{-- Toolbar --}}
            <div class="flex items-center gap-2 p-3 bg-base-200 border-b border-base-content/10">
                <select class="select select-sm select-bordered" id="theme-selector">
                    <option value="vs-light">Light</option>
                    <option value="vs-dark" selected>Dark</option>
                </select>

                <select class="select select-sm select-bordered" id="language-selector">
                    <option value="java" selected>Java</option>
                    <option value="python">Python</option>
                </select>

                <div class="ml-auto flex gap-2">
                    <button id="run_code_btn" class="btn btn-sm btn-primary">
                        Run
                    </button>
                    <button
                        id="submit_code_btn"
                        class="btn btn-sm btn-secondary"
                        @isset($details) value="{{ $details[0]->id }}" @endisset
                    >
                        Submit
                    </button>
                </div>
            </div>

            {{-- Editor --}}
            <div
                id="monaco-editor-container"
                class="w-full h-[600px] bg-base-100"
            ></div>

            {{-- Footer --}}
            <div class="px-3 py-2 text-sm text-base-content/60 bg-base-200 border-t border-base-content/10">
                Write your solution here. Use <kbd class="kbd kbd-sm">Run</kbd> to test before submitting.
            </div>
        </div>

        {{-- RIGHT: Problem & Output --}}
        <div class="flex flex-col w-full gap-4">
            {{-- Next challenge --}}
            <div class="flex justify-end">
                <button class="btn btn-sm btn-outline rounded-xl">
                    Next Challenge →
                </button>
            </div>

            {{-- Problem details --}}
            <div class="card bg-base-300 shadow-lg p-4 space-y-2">
                @isset($details)
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold">
                            {{ $details[0]->title }}
                        </h2>
                        <span class="badge badge-outline">
                            {{ ucfirst($details[0]->difficulty) }}
                        </span>
                    </div>

                    <p class="text-sm text-base-content/70">
                        Points: {{ $details[0]->points }}
                    </p>

                    <div class="prose max-w-none text-base-content">
                        {!! nl2br(e($details[0]->description)) !!}
                    </div>
                @else
                    <span class="text-base-content/60">
                        Problem description will appear here.
                    </span>
                @endisset
            </div>

            {{-- Output --}}
            <div class="divider">Code Output</div>

            <div
                id="code_output_area"
                class="card bg-base-300 shadow-lg p-3 font-mono text-sm whitespace-pre-wrap break-all min-h-[120px]"
            >
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
