<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #fafafa; color: #18181b; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #fafafa; padding-bottom: 40px; }
        .main { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e4e4e7; border-radius: 24px; margin-top: 40px; overflow: hidden; }
        .header { padding: 32px; text-align: center; }
        .content { padding: 0 40px 40px 40px; text-align: center; }
        .title { font-size: 24px; font-weight: 700; letter-spacing: -0.025em; margin-bottom: 16px; }
        .text { font-size: 16px; line-height: 24px; color: #52525b; margin-bottom: 32px; }
        .credentials-box { background-color: #f4f4f5; border-radius: 16px; padding: 24px; margin-bottom: 32px; text-align: left; }
        .credentials-item { margin-bottom: 12px; font-size: 14px; color: #18181b; }
        .credentials-label { font-weight: 600; color: #71717a; display: block; margin-bottom: 4px; }
        .credentials-value { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-weight: 600; background-color: #ffffff; padding: 8px 12px; border: 1px solid #e4e4e7; border-radius: 8px; display: block; }
        .button { display: inline-block; background-color: #18181b; color: #ffffff !important; padding: 14px 32px; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 14px; transition: background-color 0.2s; }
        .footer { padding: 32px; text-align: center; font-size: 12px; color: #a1a1aa; }
        .divider { height: 1px; background-color: #f4f4f5; margin: 32px 0; }
    </style>
</head>
<body>
<div class="wrapper">
    <table class="main" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header">
                <img src="{{ asset('images/favicon/android-chrome-192x192.png') }}" width="44" height="44" alt="Drafto Logo" style="border-radius: 12px; border: 1px solid #e4e4e7;">
                <div style="margin-top: 12px; font-weight: 600; font-size: 14px; color: #18181b;">{{ config('app.name') }}</div>
            </td>
        </tr>
        <tr>
            <td class="content">
                <h1 class="title">Seja muito bem-vindo!</h1>
                <p class="text">
                    Olá, <strong>{{ $user->name }}</strong>!<br>
                    Sua conta na Drafto foi criada com sucesso. Estamos muito felizes em ter você conosco.
                </p>

                <div class="credentials-box">
                    <div class="credentials-item">
                        <span class="credentials-label">E-mail de acesso:</span>
                        <span class="credentials-value">{{ $user->email }}</span>
                    </div>
                    <div class="credentials-item" style="margin-bottom: 0;">
                        <span class="credentials-label">Senha temporária:</span>
                        <span class="credentials-value">{{ $password }}</span>
                    </div>
                </div>

                <p class="text" style="font-size: 14px; margin-bottom: 24px;">
                    Para começar a publicar seus conteúdos e interagir com outros escritores, acesse sua conta pelo link abaixo:
                </p>

                <a href="{{ $url }}" class="button">Acessar minha conta</a>

                <div class="divider"></div>

                <p style="font-size: 12px; color: #71717a; line-height: 18px;">
                    <strong>Dica:</strong> Para garantir a segurança total da sua conta, <a href="{{ $verificationUrl }}" style="color: #18181b; font-weight: 600;">clique aqui para confirmar seu e-mail</a>.<br>
                    Recomendamos que você altere sua senha assim que realizar o primeiro login para garantir a segurança da sua conta.
                </p>
            </td>
        </tr>
    </table>

    <div class="footer">
        © {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
    </div>
</div>
</body>
</html>
