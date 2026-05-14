<?php

declare(strict_types=1);

return [
    'social' => [
        'subject' => 'Nova interação na Drafto: :name',
        'action' => 'Ver no Site',
        'thanks' => 'Obrigado por fazer parte da nossa comunidade!',
        'messages' => [
            'like_post' => 'curtiu seu post: :title',
            'like_comment' => 'curtiu seu comentário',
            'mention' => 'mencionou você em um comentário',
            'follow' => 'começou a seguir você',
            'default' => 'interagiu com você',
        ],
    ],
    'support' => [
        'subject' => '[Suporte] :subject',
        'greeting' => 'Olá, Equipe Drafto!',
        'received' => 'Você recebeu uma nova mensagem de suporte de :name (:email).',
        'subject_line' => 'Assunto: :subject',
        'message_line' => 'Mensagem:',
        'respond' => 'Por favor, responda ao usuário o mais breve possível.',
        'action' => 'Ver Dashboard de Suporte',
        'thanks' => 'Obrigado por usar nossa plataforma!',
    ],
    'report' => [
        'feedback' => [
            'subject' => 'Atualização sobre sua denúncia - :app',
            'greeting' => 'Olá, :name!',
            'body' => 'Sua denúncia sobre um conteúdo de :type foi revisada por nossa equipe.',
            'status' => 'Status atual: **:status**',
            'admin_feedback' => 'Mensagem da moderação: ":feedback"',
            'thanks' => 'Obrigado por nos ajudar a manter a comunidade segura.',
            'action' => 'Ver Diretrizes',
            'database_message' => 'Sua denúncia foi revisada.',
        ],
        'banned' => [
            'subject' => 'Sua conta foi suspensa - :app',
            'greeting' => 'Olá, :name.',
            'body' => 'Lamentamos informar que sua conta foi suspensa temporariamente devido à violação das nossas diretrizes.',
            'reason' => '**Motivo da suspensão:** :reason',
            'until' => 'Sua conta permanecerá bloqueada até: **:date**',
            'error_contact' => 'Se você acredita que isso foi um erro, entre em contato com o suporte.',
            'action' => 'Revisar Termos de Uso',
        ],
    ],
    'auth' => [
        'reset_password' => [
            'subject' => 'Recuperação de senha na Drafto',
            'title' => 'Recuperação de senha',
            'body' => 'Recebemos uma solicitação para redefinir a senha da sua conta.<br>Se foi você, clique no botão abaixo para escolher uma nova senha.',
            'action' => 'Redefinir senha',
            'expire' => 'Este link de redefinição de senha expirará em :count minutos.<br>Se você não solicitou isso, nenhuma ação adicional é necessária.',
            'footer' => 'Proteja sua conta. Não compartilhe este link com ninguém.',
        ],
        'verify_email' => [
            'subject' => 'Confirme seu e-mail na Drafto',
            'title' => 'Verifique seu e-mail',
            'greeting' => 'Olá, <strong>:name</strong>!',
            'body' => 'Para começar a escrever e publicar na Drafto, confirme que este e-mail pertence a você clicando no botão abaixo.',
            'action' => 'Confirmar e-mail',
            'ignore' => 'Se você não criou uma conta na Drafto, ignore este e-mail.<br>Este link expira em 60 minutos.',
            'footer' => 'Escreva com clareza. Publique com identidade.',
        ],
    ],
    'newsletter' => [
        'subject' => 'Novidades na Drafto: :category',
        'subject_important' => 'Informativo: :app',
        'important_title' => 'Comunicado Importante',
        'news_title' => 'Novidades para você',
        'category_label' => 'CATEGORIA: :category',
        'read_more' => 'Ler mais',
        'greeting_reader' => 'Olá, Leitor!',
        'default_body' => 'Temos novidades importantes esperando por você na plataforma. Passe por lá para conferir os conteúdos exclusivos da semana!',
        'action' => 'Acessar Drafto',
        'unsubscribe' => 'Você recebeu este e-mail porque está inscrito na nossa newsletter.<br><a href=":url" class="link">Cancelar inscrição</a> • © :year Drafto',
        'verification' => [
            'subject' => 'Confirme sua inscrição no Radar Drafto',
            'greeting' => 'Olá!',
            'body1' => 'Obrigado por se interessar pelo <strong>Radar Drafto</strong>. Estamos quase lá!',
            'body2' => 'Para confirmar sua inscrição e começar a receber as melhores histórias e novidades da plataforma, por favor clique no botão abaixo:',
            'action' => 'Confirmar Inscrição',
            'ignore' => 'Se você não solicitou esta inscrição, pode ignorar este e-mail com segurança.',
            'footer' => 'Drafto - A sua estante digital de grandes histórias.',
        ],
    ],
    'common' => [
        'platform_footer' => '© :year Drafto. Plataforma para escritores.',
    ],
];
