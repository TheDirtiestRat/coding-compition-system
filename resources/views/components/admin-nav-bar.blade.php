<header class="border-b border-base-300 admin-chrome px-4 sm:px-6">
    <div class="flex min-h-16 items-center justify-between gap-4">
        <div class="flex min-w-0 items-center gap-3">
            <span class="hidden text-xs admin-muted md:inline">Workspace</span>
            <span class="hidden text-xs admin-muted md:inline" aria-hidden="true">/</span>
            <h1 class="text-sm font-semibold wrap-anywhere">@yield('admin-title', 'Admin')</h1>
        </div>
        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            @include('components.admin-theme-toggle')
            <form action="{{ url('/logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-ghost admin-toolbar-button" aria-label="Log out" title="Log out">
                    <x-admin-icon name="logout" />
                    <span class="hidden sm:inline">Log out</span>
                </button>
            </form>
        </div>
    </div>
    <details class="pb-4 lg:hidden">
        <summary class="cursor-pointer rounded-lg border border-base-300 px-4 py-3 text-sm font-medium">Admin menu</summary>
        <div class="pt-3">@include('components.admin-navigation')</div>
    </details>
</header>
