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
        .logo { height: 44px; width: 44px; background-color: #f4f4f5; border: 1px solid #e4e4e7; border-radius: 12px; display: inline-block; line-height: 44px; font-weight: bold; font-size: 20px; }
        .content { padding: 0 40px 40px 40px; text-align: center; }
        .title { font-size: 24px; font-weight: 700; letter-spacing: -0.025em; margin-bottom: 16px; }
        .text { font-size: 16px; line-height: 24px; color: #52525b; margin-bottom: 32px; }
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
                <div style="margin-top: 12px; font-weight: 600; font-size: 14px; color: #18181b;">Drafto</div>
            </td>
        </tr>
        <tr>
            <td class="content">
                <h1 class="title">Verifique seu e-mail</h1>
                <p class="text">
                    Olá, <strong>{{ $user->name }}</strong>!<br>
                    Para começar a escrever e publicar na Drafto, confirme que este e-mail pertence a você clicando no botão abaixo.
                </p>

                <a href="{{ $url }}" class="button">Confirmar e-mail</a>

                <div class="divider"></div>

                <p style="font-size: 12px; color: #71717a; line-height: 18px;">
                    Se você não criou uma conta na Drafto, ignore este e-mail.<br>
                    Este link expira em 60 minutos.
                </p>
            </td>
        </tr>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} Drafto. Plataforma para escritores.<br>
        Escreva com clareza. Publique com identidade.
    </div>
</div>
</body>
</html>
