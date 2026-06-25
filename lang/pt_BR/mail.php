<?php

declare(strict_types=1);

return [

    'common' => [
        'platform_footer' => '© :year Drafto. Escreva com clareza. Publique com identidade.',
    ],

    'auth' => [
        'reset_password' => [
            'title' => 'Esqueceu sua senha?',
            'body' => 'Recebemos uma solicitação para redefinir a senha da sua conta. Clique no botão abaixo para criar uma nova senha.',
            'action' => 'Redefinir minha senha',
            'expire' => 'Este link é temporário e expirará em :count minutos. Se você não solicitou, ignore este e-mail com segurança.',
            'footer' => 'Por segurança, nunca compartilhe links de acesso ou senhas com terceiros.',
        ],

        'magic_link' => [
            'subject' => 'Seu link de acesso à Drafto',
            'title' => 'Entre com um clique',
            'body' => 'Use o botão abaixo para acessar sua conta na Drafto. Nenhuma senha é necessária.',
            'action' => 'Acessar minha conta',
            'expire' => 'Este link é válido por :count minutos e só pode ser usado uma vez. Se você não solicitou, ignore este e-mail.',
            'footer' => 'Por segurança, nunca compartilhe este link com terceiros.',
        ],
    ],

];
