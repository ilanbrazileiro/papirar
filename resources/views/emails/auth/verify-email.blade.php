@php
    $name = $user->name ?? 'aluno';
@endphp
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirme seu e-mail no Papirar</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f1f5f9;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:22px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 18px 50px rgba(15,23,42,.10);">
                    <tr>
                        <td style="background:#0f172a;padding:28px 32px;color:#ffffff;">
                            <div style="font-size:13px;letter-spacing:.12em;text-transform:uppercase;color:#facc15;font-weight:700;">Papirar Concursos</div>
                            <div style="font-size:28px;line-height:1.15;font-weight:800;margin-top:8px;">Confirme seu e-mail</div>
                            <div style="font-size:15px;line-height:1.6;color:#cbd5e1;margin-top:10px;max-width:500px;">
                                Seu acesso ao Papirar está quase pronto. Confirme seu e-mail para acessar a área do aluno e seus cursos.
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:16px;line-height:1.65;margin:0 0 14px;">Olá, <strong>{{ $name }}</strong>.</p>

                            <p style="font-size:16px;line-height:1.65;margin:0 0 22px;">
                                Clique no botão abaixo para confirmar seu endereço de e-mail e liberar sua conta no Papirar Concursos.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:26px 0;">
                                <tr>
                                    <td bgcolor="#1d4ed8" style="border-radius:12px;">
                                        <a href="{{ $verificationUrl }}" target="_blank" style="display:inline-block;padding:14px 22px;color:#ffffff;text-decoration:none;font-weight:800;font-size:16px;border-radius:12px;">
                                            Confirmar meu e-mail
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:16px;margin:24px 0;">
                                <div style="font-weight:800;margin-bottom:8px;color:#0f172a;">Segurança</div>
                                <div style="font-size:14px;line-height:1.6;color:#475569;">
                                    Este link expira em 24 horas. Se você não criou uma conta no Papirar, ignore esta mensagem.
                                </div>
                            </div>

                            <p style="font-size:14px;line-height:1.6;color:#64748b;margin:0 0 10px;">
                                Se o botão não funcionar, copie e cole este link no navegador:
                            </p>
                            <p style="font-size:13px;line-height:1.5;word-break:break-all;color:#1d4ed8;margin:0;">
                                <a href="{{ $verificationUrl }}" target="_blank" style="color:#1d4ed8;">{{ $verificationUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f8fafc;padding:20px 32px;border-top:1px solid #e2e8f0;">
                            <div style="font-size:13px;line-height:1.6;color:#64748b;">
                                Papirar Concursos — Plataforma de questões e preparação estratégica para concursos internos militares.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
