<div class="navbar bg-base-300 shadow rounded-xl px-3">
    {{-- for small screen --}}
    <div class="navbar-start">
        <div class="dropdown">
            <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                </svg>
            </div>
            <ul tabindex="-1" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                <li><a href="{{ url('code-problems') }}">Problems</a></li>
                <li><a href="{{ url('code-editor') }}">Code Editor</a></li>
            </ul>
        </div>
        <a class="btn btn-ghost text-xl">Coding Editor</a>
    </div>

    {{-- for big screen --}}
    <div class="navbar-center hidden lg:flex">
        <ul class="menu menu-horizontal px-1">
            <li><a href="{{ url('code-problems') }}">Problems</a></li>
            <li><a href="{{ url('code-editor') }}">Code Editor</a></li>
        </ul>
    </div>

    <form method="POST" action="{{ url('/logout') }}" class="navbar-end flex gap-3">
        @csrf
        @if (Auth::check())
            <p>Welcome, {{ auth()->user()->name }}</p>
        @endif
        <button type="submit" class="btn btn-soft rounded-xl">
            Logout
        </button>
    </form>
</div>
