<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docverin - Workspaces</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 16px; }
        .card { border: 1px solid #ddd; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        input, button { padding: 10px 12px; font-size: 14px; }
        input { width: 100%; max-width: 420px; }
        button { cursor: pointer; }
        a { text-decoration: none; color: #0a58ca; }
    </style>
</head>
<body>
    <h1>Docverin</h1>
    <p>Workspaces</p>

    <div class="card">
        <h2>Create workspace</h2>

        <form method="POST" action="{{ route('workspaces.store') }}">
            @csrf
            <div style="margin-bottom: 12px;">
                <input type="text" name="name" placeholder="Workspace name" required>
            </div>
            <button type="submit">Create</button>
        </form>

        @if ($errors->any())
            <div style="margin-top: 12px; color: #b00020;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="card">
        <h2>Existing workspaces</h2>

        @forelse ($workspaces as $workspace)
            <div style="margin-bottom: 10px;">
                <a href="{{ route('workspaces.show', $workspace) }}">
                    {{ $workspace->name }}
                </a>
                <div style="font-size: 12px; color: #666;">{{ $workspace->slug }}</div>
            </div>
        @empty
            <p>No workspaces yet.</p>
        @endforelse
    </div>
</body>
</html>