<div class="drawer-side is-drawer-close:overflow-visible p-2">
    <label for="my-drawer-4" aria-label="close sidebar" class="drawer-overlay"></label>
    <div
        class="flex min-h-full flex-col items-start bg-base-300 rounded-2xl shadow is-drawer-close:w-14 is-drawer-open:w-64 ">
        <!-- Sidebar content here -->
        <ul class="menu w-full grow">
            <!-- List item -->
            <li>
                <a href="{{ url('/admin/dashboard') }}">
                    <button class="is-drawer-close:tooltip is-drawer-close:tooltip-right" data-tip="Dashboard">
                        <!-- Home icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linejoin="round"
                            stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor"
                            class="my-1.5 inline-block size-4">
                            <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
                            <path
                                d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z">
                            </path>
                        </svg>
                        <span class="is-drawer-close:hidden">Dashboard</span>
                    </button>
                </a>
            </li>

            <!-- List item -->
            <li>
                <a href="{{ url('/admin/candidates') }}">
                    <button class="is-drawer-close:tooltip is-drawer-close:tooltip-right" data-tip="Candidates">
                        <!-- Settings icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linejoin="round"
                            stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor"
                            class="my-1.5 inline-block size-4">
                            <path d="M20 7h-9"></path>
                            <path d="M14 17H5"></path>
                            <circle cx="17" cy="17" r="3"></circle>
                            <circle cx="7" cy="7" r="3"></circle>
                        </svg>
                        <span class="is-drawer-close:hidden">Candidates</span>
                    </button>
                </a>
            </li>

            <!-- List item -->
            <li>
                <a href="{{ url('/admin/challenges') }}">
                    <button class="is-drawer-close:tooltip is-drawer-close:tooltip-right" data-tip="Challenges">
                        <!-- Settings icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linejoin="round"
                            stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor"
                            class="my-1.5 inline-block size-4">
                            <path d="M20 7h-9"></path>
                            <path d="M14 17H5"></path>
                            <circle cx="17" cy="17" r="3"></circle>
                            <circle cx="7" cy="7" r="3"></circle>
                        </svg>
                        <span class="is-drawer-close:hidden">Code Problems</span>
                    </button>
                </a>
            </li>

            <!-- List item -->
            <li class="basis-auto">
                <a href="{{ url('admin/options') }}">
                    <button class="is-drawer-close:tooltip is-drawer-close:tooltip-right" data-tip="Options">
                        <!-- Settings icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linejoin="round"
                            stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor"
                            class="my-1.5 inline-block size-4">
                            <path d="M20 7h-9"></path>
                            <path d="M14 17H5"></path>
                            <circle cx="17" cy="17" r="3"></circle>
                            <circle cx="7" cy="7" r="3"></circle>
                        </svg>
                        <span class="is-drawer-close:hidden">Options</span>
                    </button>
                </a>
            </li>
        </ul>
    </div>
</div>
