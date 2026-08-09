<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verifique seu e-mail</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 12px 30px rgba(0,0,0,.08);">
                    <tr>
                        <td align="center" style="background:#ffffff;padding:32px 24px 16px;">
                            <img src="https://pub-f968f26c5ae542c2a6c19b82d190b8fb.r2.dev/Imagens/icon-logo.png" alt="Prep4You" width="120" style="display:block;border:0;">
                            <h2>Prep4You</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 48px 32px;color:#0f172a;">
                            <h1 style="margin:0;font-size:28px;font-weight:700;">Olá {{ $name }},</h1>
                            <p style="margin:16px 0 24px;font-size:16px;line-height:1.7;color:#475569;">
                                Por favor, clique no botão abaixo para verificar seu endereço de e-mail.
                            </p>
                            <table cellpadding="0" cellspacing="0" width="100%" style="margin:0 auto 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}" style="display:inline-block;background:#ff7a00;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-weight:700;font-size:16px;">Verificar endereço de e-mail</a>
                                    </td>
                                </tr>
                            </table>
                            <p><strong>Senha para o primeiro acesso:</strong> 123456</p>
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#475569;">
                                Se você não criou uma conta, nenhuma ação adicional é necessária.
                            </p>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#94a3b8;">
                                Se estiver tendo problemas para clicar no botão, copie e cole o link a seguir no seu navegador:
                            </p>
                            <p style="word-break:break-all;font-size:12px;line-height:1.7;color:#475569;margin-top:12px;">
                                <a href="{{ $url }}" style="text-decoration:none;">{{ $url }}</a>
                            </p>
                       </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc;padding:24px 48px;color:#475569;font-size:14px;">
                            Atenciosamente,<br>
                            <strong>Prep4You</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
