@props(['active' => 'home'])

<div class="navbar bg-base-100 shadow rounded-xl border border-gray-500 sticky top-0 z-10">
    <div class="flex-1">
        <a href="{{ url('/home') }}" class="btn btn-ghost text-xl">The Hunger Games (Coding Competition)</a>
    </div>
    <div class="flex-none">
        <ul class="menu menu-horizontal px-1">
            <li>
                <a href="{{ url('/home') }}" @class(['font-semibold' => $active === 'home'])>Home</a>
            </li>
            <li>
                <a href="{{ url('/home#about') }}">About</a>
            </li>
            <li>
                <a href="{{ url('/credits') }}" @class(['font-semibold' => $active === 'credits'])>Credits</a>
            </li>
            <li>
                <a href="{{ url('/login') }}">Login</a>
            </li>
        </ul>
    </div>
</div>
