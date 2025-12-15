@extends('layouts.code-editor')

@section('code-content')
    <div id="alert_container" class="w-full flex-col gap-1 hidden">
        <div role="alert" id="alert_success" class="alert alert-success w-fit mx-auto hidden">
            <span id="success_alert_text">Code Verified! Points Added +999</span>
        </div>

        <div role="alert" id="alert_error" class="alert alert-error w-fit mx-auto hidden">
            <span>Code Verification Failed.</span>
        </div>
    </div>
    <div class="flex md:flex-row flex-col-reverse h-auto w-full gap-3">
        <div class="card bg-base-300 border border-gray-500 flex flex-col">
            {{-- <div class="card bg-base-300 rounded-box shadow border border-gray-500"> --}}
            {{-- top --}}
            <div class="card bg-base-300 rounded-t-2xl basis-auto grid grid-cols-4 gap-2 p-2">
                {{-- select a theme --}}
                <select class="select select-ghost select-sm" id="theme-selector">
                    <option value="vs-light" selected>Light Theme</option>
                    <option value="vs-dark">Dark Theme</option>
                </select>
                {{-- select a language --}}
                <select class="select select-ghost select-sm" id="language-selector">
                    {{-- <option disabled selected>Pick a Language</option> --}}
                    <option value="java" selected>Java</option>
                    <option value="python">Python</option>
                    {{-- <option>C#</option>
                    <option>C++</option> --}}
                </select>
                {{-- run the code --}}
                <button id="run_code_btn" class="btn btn-sm btn-primary">Run Code</button>
                <button id="check_code_btn" class="btn btn-sm btn-secondary">Check Code</button>
            </div>
            {{-- editor --}}
            <div id="monaco-editor-container" class="basis-auto w-auto lg:w-[600px] h-[600px]"></div>
            {{-- bot --}}
            <div class="card bg-base-300 rounded-b-2xl basis-auto p-2">
                Edit Code here...
            </div>
        </div>
        <div class="flex w-full flex-col h-full">
            <div class="card bg-base-300 rounded-box h-[300px] border border-gray-500 p-3">
                Problem Description
            </div>
            <div class="divider divider-start">Code Output</div>
            <div id="code_output_area"
                class="card bg-base-300 rounded-box h-[300px] w-full border border-gray-500 p-3 text-wrap break-all">
                This is where the output of the code will be.
            </div>
        </div>
    </div>

    <p class="p-2">
        <strong>Instruction</strong><br>
        This Page is where you code the solutions of the given problem once you are done solving the code you can click the
        check code to verify it to gain points.
    </p>
@endsection
