<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .button {
            background-color: #4F46E5;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body style="font-family: sans-serif; color: #374151; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #111827;">Olá!</h2>
        <p>Obrigado por se interessar pelo <strong>Radar Drafto</strong>. Estamos quase lá!</p>
        <p>Para confirmar sua inscrição e começar a receber as melhores histórias e novidades da plataforma, por favor clique no botão abaixo:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $verificationUrl }}" class="button" style="color: #ffffff;">Confirmar Inscrição</a>
        </div>
        
        <p style="font-size: 14px; color: #6B7280;">Se você não solicitou esta inscrição, pode ignorar este e-mail com segurança.</p>
        
        <hr style="border: 0; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        <p style="font-size: 12px; color: #9CA3AF; text-align: center;">Drafto - A sua estante digital de grandes histórias.</p>
    </div>
</body>
</html>
