<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Pré-visualização da questão #{{ $question->id }} | Papirar</title>
    <style>
        :root {
            --papirar-blue: #0B1F3A;
            --papirar-blue-2: #123862;
            --papirar-yellow: #F2C94C;
            --papirar-bg: #F3F6FB;
            --papirar-text: #1F2937;
            --papirar-muted: #6B7280;
            --papirar-border: #DDE5F0;
            --papirar-card: #FFFFFF;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--papirar-bg);
            color: var(--papirar-text);
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.55;
        }

        .preview-topbar {
            background: linear-gradient(135deg, var(--papirar-blue), var(--papirar-blue-2));
            color: #fff;
            padding: 18px 24px;
            border-bottom: 4px solid var(--papirar-yellow);
        }

        .preview-topbar-inner {
            max-width: 1050px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 20px;
            letter-spacing: .2px;
        }

        .brand-badge {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--papirar-yellow);
            color: var(--papirar-blue);
            display: grid;
            place-items: center;
            font-weight: 900;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            font-size: 13px;
            border: 1px solid rgba(255, 255, 255, .25);
        }

        .container {
            max-width: 1050px;
            margin: 28px auto;
            padding: 0 18px 40px;
        }

        .notice {
            background: #FFF8DF;
            border: 1px solid #F6E6A8;
            color: #6D5200;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .question-card {
            background: var(--papirar-card);
            border: 1px solid var(--papirar-border);
            border-radius: 20px;
            box-shadow: 0 18px 50px rgba(11, 31, 58, .08);
            overflow: hidden;
        }

        .question-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--papirar-border);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .question-title {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: var(--papirar-blue);
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tag {
            background: #EEF4FF;
            color: #24476F;
            border: 1px solid #D9E7FB;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .question-body {
            padding: 24px 22px 26px;
        }

        .statement {
            font-size: 17px;
            margin-bottom: 24px;
        }

        .statement p:first-child { margin-top: 0; }
        .statement p:last-child { margin-bottom: 0; }

        .statement img,
        .commented-answer img,
        .alternative-text img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 16px auto;
            border-radius: 12px;
            border: 1px solid var(--papirar-border);
        }

        .alternatives {
            display: grid;
            gap: 12px;
        }

        .alternative {
            display: grid;
            grid-template-columns: 44px 1fr;
            gap: 12px;
            align-items: start;
            border: 1px solid var(--papirar-border);
            border-radius: 16px;
            padding: 14px;
            background: #fff;
        }

        .alternative-letter {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #F2F5FA;
            border: 1px solid #DDE5F0;
            color: var(--papirar-blue);
            font-weight: 900;
        }

        .alternative-text { padding-top: 6px; }
        .alternative-text p:first-child { margin-top: 0; }
        .alternative-text p:last-child { margin-bottom: 0; }

        .preview-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 800;
            font-size: 14px;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--papirar-blue);
            color: #fff;
        }

        .btn-secondary {
            background: #fff;
            color: var(--papirar-blue);
            border-color: var(--papirar-border);
        }

        .commented-answer {
            margin-top: 22px;
            border-top: 1px solid var(--papirar-border);
            padding-top: 18px;
            color: #374151;
        }

        .commented-answer h3 {
            margin: 0 0 8px;
            font-size: 16px;
            color: var(--papirar-blue);
        }

        @media (max-width: 700px) {
            .preview-topbar-inner,
            .question-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .question-body { padding: 20px 16px; }
            .alternative { grid-template-columns: 38px 1fr; }
        }
    </style>
</head>
<body>
    <header class="preview-topbar">
        <div class="preview-topbar-inner">
            <div class="brand">
                <span class="brand-badge">P</span>
                <span>Papirar Concursos</span>
            </div>
            <span class="admin-badge">Pré-visualização administrativa</span>
        </div>
    </header>

    <main class="container">
        <div class="notice">
            Esta é uma prévia da questão salva no banco. Alterações ainda não salvas no formulário de edição não aparecem aqui.
        </div>

        <article class="question-card">
            <header class="question-header">
                <h1 class="question-title">Questão #{{ $question->id }}</h1>
                <div class="tags">
                    @if($question->subject)
                        <span class="tag">{{ $question->subject->name }}</span>
                    @endif
                    @if($question->topic)
                        <span class="tag">{{ $question->topic->name }}</span>
                    @endif
                    @if($question->corporation)
                        <span class="tag">{{ $question->corporation->name }}</span>
                    @endif
                    @if($question->difficulty)
                        <span class="tag">{{ ucfirst($question->difficulty) }}</span>
                    @endif
                </div>
            </header>

            <div class="question-body">
                <div class="statement">
                    {!! $question->statement !!}
                </div>

                <div class="alternatives">
                    @foreach($question->alternatives as $alternative)
                        <div class="alternative">
                            <div class="alternative-letter">{{ $alternative->letter }}</div>
                            <div class="alternative-text">{!! $alternative->text !!}</div>
                        </div>
                    @endforeach
                </div>

                @if($question->commented_answer)
                    <section class="commented-answer">
                        <h3>Comentário / gabarito comentado</h3>
                        {!! $question->commented_answer !!}
                    </section>
                @endif
            </div>
        </article>

        <div class="preview-actions">
            <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-secondary">Voltar para edição</a>
            <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir prévia</button>
        </div>
    </main>
</body>
</html>
