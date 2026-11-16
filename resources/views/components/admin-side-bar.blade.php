<aside class="fixed inset-y-0 left-0 hidden w-64 overflow-y-auto flex-col border-r border-base-300 admin-chrome p-3 lg:flex">
    <a href="{{ url('/admin/dashboard') }}" class="flex min-h-12 items-center gap-3 rounded-lg px-3">
        <span class="admin-brand-mark"><x-admin-icon name="code" /></span>
        <span class="text-sm font-semibold">Coding Competition<span class="mt-1 block text-xs font-normal admin-muted">Admin workspace</span></span>
    </a>
    <div class="mt-6">
        @include('components.admin-navigation')
    </div>
    <div class="mt-auto border-t border-base-300 px-3 pt-4 text-xs leading-5 admin-muted">
        <p class="font-medium text-base-content">ACLC Programmers' Guild</p>
        <p>School competition portal</p>
        <a href="{{ url('/') }}" class="mt-3 inline-flex min-h-11 items-center gap-2 text-base-content hover:underline underline-offset-4">View public site <x-admin-icon name="external" /></a>
    </div>
</aside>
