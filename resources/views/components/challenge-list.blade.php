@props(["challenges"])

<div class="admin-card overflow-x-auto">
    <table class="table table-zebra">
        <!-- head -->
        <thead class="">
            <tr class="text-center admin-muted">
                <th class="bg-base-200 rounded-tl-xl">ID</th>
                <th class="bg-base-200">Title</th>
                <th class="bg-base-200">Difficulty</th>
                <th class="bg-base-200">Language</th>
                <th class="bg-base-200">Points</th>
                <th class="bg-base-200 rounded-tr-xl">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($challenges as $challenge)
                <tr class="text-center">
                    <th>{{ $challenge->id }}</th>
                    <td>{{ $challenge->title }}</td>
                    <td>{{ $challenge->difficulty }}</td>
                    <td>{{ $challenge->language }}</td>
                    <td>{{ $challenge->points }}</td>
                    <td class="flex flex-row items-center justify-center gap-2">
                        <button class="btn btn-xs" onclick="edit_challenge({{ $challenge->id }})">Edit</button>
                        <form action="{{ url('/admin/delete-challenge/' . $challenge->id) }}" class="challenge_delete_form"
                            method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-error">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="admin-muted">No challenges yet. Add a coding problem to get started.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
