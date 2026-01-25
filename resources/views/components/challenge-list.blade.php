@props(["challenges"])

<div class="basis-full bg-base-100 rounded-xl overflow-hidden p-3">
    <table class="table table-zebra">
        <!-- head -->
        <thead class="">
            <tr class="text-center text-neutral-200">
                <th class="bg-slate-700/50 rounded-tl-xl">ID</th>
                <th class="bg-slate-700/50">Title</th>
                <th class="bg-slate-700/50">Difficulty</th>
                <th class="bg-slate-700/50">Language</th>
                <th class="bg-slate-700/50">Points</th>
                <th class="bg-slate-700/50 rounded-tr-xl">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($challenges as $challenge)
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
            @endforeach
        </tbody>
    </table>
</div>
