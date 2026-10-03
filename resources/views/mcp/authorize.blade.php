<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conectar ao Papirar</title>
    <style>
        body { margin: 0; padding: 24px; background: #f1f5f9; color: #172b4d; font: 16px/1.5 system-ui, sans-serif; }
        main { max-width: 480px; margin: 8vh auto; padding: 28px; border-radius: 16px; background: white; box-shadow: 0 8px 32px #172b4d15; }
        h1 { margin-top: 0; font-size: 24px; }
        .actions { display: flex; gap: 12px; margin-top: 24px; }
        button { padding: 12px 18px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; cursor: pointer; font: inherit; }
        .approve { background: #172b4d; color: white; border-color: #172b4d; }
    </style>
</head>
<body>
<main>
    <h1>Conectar ao Papirar</h1>
    <p><strong>{{ $client->name }}</strong> solicita acesso com sua conta <strong>{{ auth()->user()->email }}</strong>.</p>
    @if (auth()->user()->is_active && in_array(auth()->user()->role, ['admin', 'content'], true))
        <p>Esta conexão permite consultar o conteúdo, criar disciplinas e tópicos e cadastrar questões em rascunho.</p>
        <p>Você pode cancelar agora ou autorizar a conexão.</p>
    @else
        <p>Sua conta não tem acesso ao Extrator. Entre com uma conta ativa de administração ou conteúdo.</p>
    @endif
    <div class="actions">
        <form method="post" action="{{ route('passport.authorizations.deny') }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="state" value="">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit">Cancelar</button>
        </form>
        @if (auth()->user()->is_active && in_array(auth()->user()->role, ['admin', 'content'], true))
            <form method="post" action="{{ route('passport.authorizations.approve') }}">
                @csrf
                <input type="hidden" name="state" value="">
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="approve">Autorizar conexão</button>
            </form>
        @endif
    </div>
</main>
</body>
</html>
