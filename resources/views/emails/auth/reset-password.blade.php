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
        .button { display: inline-block; background-color: #18181b; color: #ffffff !important; padding: 14px 32px; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 14px; }
        .footer { padding: 32px; text-align: center; font-size: 12px; color: #a1a1aa; }
        .divider { height: 1px; background-color: #f4f4f5; margin: 32px 0; }
        .help-text { font-size: 12px; color: #71717a; line-height: 18px; margin-top: 24px; }
    </style>
</head>
<body>
<div class="wrapper">
    <table class="main" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header">
                <div class="logo">W</div>
                <div style="margin-top: 12px; font-weight: 600; font-size: 14px; color: #18181b;">Drafto</div>
            </td>
        </tr>
        <tr>
            <td class="content">
                <h1 class="title">Recuperação de senha</h1>
                <p class="text">
                    Recebemos uma solicitação para redefinir a senha da sua conta.<br>
                    Se foi você, clique no botão abaixo para escolher uma nova senha.
                </p>

                <a href="{{ $url }}" class="button">Redefinir senha</a>

                <div class="divider"></div>

                <p class="help-text">
                    Este link de redefinição de senha expirará em {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutos.<br>
                    Se você não solicitou isso, nenhuma ação adicional é necessária.
                </p>
            </td>
        </tr>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} Drafto. Plataforma para escritores.<br>
        Proteja sua conta. Não compartilhe este link com ninguém.
    </div>
</div>
</body>
</html>
