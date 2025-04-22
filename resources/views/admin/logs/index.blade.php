<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laravel Log Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: monospace;
            padding: 2rem;
        }
        .log-container {
            background: #1e1e1e;
            padding: 1.5rem;
            border-radius: 8px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .log-line {
            white-space: pre-wrap;
            border-bottom: 1px solid #333;
            padding: 0.3rem 0;
        }
    </style>
</head>
<body>

    <h2>📄 My Petra Log Viewer</h2>

    <div class="log-container mt-3">
        @forelse ($logLines as $line)
            <div class="log-line">{{ $line }}</div>
        @empty
            <p>No log entries found.</p>
        @endforelse
    </div>

</body>
</html>
