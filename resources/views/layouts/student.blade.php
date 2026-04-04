<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Papirar')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #0d6efd;
            --bg: #f5f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --success-bg: #ecfdf3;
            --success-border: #a7f3d0;
            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .student-navbar {
            background: #fff;
            border-bottom: 1px solid var(--border);
        }

        .student-shell {
            max-width: 980px;
            margin: 0 auto;
            padding: 32px 16px 56px;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: var(--muted);
            margin-bottom: 28px;
        }

        .card-soft {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        }

        .meta-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: .875rem;
            color: var(--muted);
        }

        .question-statement {
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .alternative-option {
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
            transition: .2s ease;
            cursor: pointer;
        }

        .alternative-option:hover {
            border-color: #cbd5e1;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
        }

        .alternative-option.selected {
            border-color: var(--brand);
            background: #eff6ff;
        }

        .alternative-option.correct {
            border-color: #10b981;
            background: var(--success-bg);
        }

        .alternative-option.wrong {
            border-color: #ef4444;
            background: var(--danger-bg);
        }

        .alternative-radio {
            transform: scale(1.15);
            margin-top: 4px;
        }

        .progress-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .progress {
            height: 10px;
            border-radius: 999px;
            background: #e5e7eb;
        }

        .feedback-box-success {
            border: 1px solid var(--success-border);
            background: var(--success-bg);
            border-radius: 16px;
        }

        .feedback-box-danger {
            border: 1px solid var(--danger-border);
            background: var(--danger-bg);
            border-radius: 16px;
        }

        .stats-card {
            border-radius: 18px;
            border: 1px solid var(--border);
            background: #fff;
            padding: 20px;
            height: 100%;
        }

        .stats-value {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .stats-label {
            color: var(--muted);
            font-size: .95rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg student-navbar">
        <div class="container-fluid" style="max-width: 1180px;">
            <a class="navbar-brand fw-bold" href="{{ route('study.index') }}">Papirar</a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="{{ route('study.index') }}" class="text-decoration-none text-dark">Estudar</a>
                <span class="text-muted">{{ session('user.email') }}</span>
            </div>
        </div>
    </nav>

    <main class="student-shell">
        @if($errors->any())
            <div class="alert alert-danger rounded-4">
                <div class="fw-semibold mb-2">Corrija os erros abaixo:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>