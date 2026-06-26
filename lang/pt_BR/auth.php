<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Autenticação (Traduções Padrão)
    |--------------------------------------------------------------------------
    */
    'failed' => 'As credenciais informadas não correspondem aos nossos registros.',
    'password' => 'A senha informada está incorreta.',
    'throttle' => 'Muitas tentativas de login. Por favor, tente novamente em :seconds segundos.',

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */
    'login' => [
        'title' => 'Entrar na conta',
        'subtitle' => 'Bem-vindo de volta à Drafto.',
        'email_label' => 'E-mail',
        'password_label' => 'Senha',
        'remember_me' => 'Lembrar de mim',
        'forgot_password' => 'Esqueceu sua senha?',
        'submit' => 'Entrar agora',
        'no_account' => 'Ainda não tem uma conta?',
        'register_link' => 'Criar conta gratuitamente',
        'or' => 'ou',
        'magic_link' => 'Entrar com link mágico',
    ],

    /*
    |--------------------------------------------------------------------------
    | Login por Link Mágico (sem senha)
    |--------------------------------------------------------------------------
    */
    'magic_link' => [
        'title' => 'Entrar sem senha',
        'subtitle' => 'Informe seu e-mail e enviaremos um link de acesso instantâneo.',
        'email_label' => 'E-mail',
        'send_link' => 'Enviar link de acesso',
        'remember' => 'Manter-me conectado neste dispositivo',
        'back_to_login' => 'Voltar para o login',
        'sent_title' => 'Verifique seu e-mail',
        'sent_desc' => 'Se houver uma conta para :email, enviamos um link de acesso.',
        'sent_help' => 'O link expira em alguns minutos. Não recebeu? Verifique a pasta de spam ou tente novamente.',
        'sent_toast' => 'Pronto! Verifique sua caixa de entrada.',
        'invalid' => 'Este link de acesso é inválido ou expirou. Solicite um novo.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Registro
    |--------------------------------------------------------------------------
    */
    'register' => [
        'title' => 'Criar nova conta',
        'subtitle' => 'Junte-se à comunidade de mentes brilhantes.',
        'name_label' => 'Nome completo',
        'email_label' => 'E-mail',
        'password_label' => 'Senha',
        'password_confirmation_label' => 'Confirmar senha',
        'submit' => 'Criar minha conta',
        'already_registered' => 'Já possui uma conta?',
        'login_link' => 'Fazer login',
        'terms' => 'Ao se registrar, você concorda com nossos Termos de Uso e Política de Privacidade.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Recuperação de Senha
    |--------------------------------------------------------------------------
    */
    'password_reset' => [
        'title' => 'Recuperar senha',
        'subtitle' => 'Informe seu e-mail para receber o link de redefinição.',
        'email_label' => 'E-mail',
        'send_link' => 'Enviar link de recuperação',
        'back_to_login' => 'Voltar para o login',
        'reset_title' => 'Redefinir sua senha',
        'reset_subtitle' => 'Crie uma nova senha segura para sua conta.',
        'submit' => 'Salvar nova senha',
    ],

    /*
    |--------------------------------------------------------------------------
    | Verificação de E-mail
    |--------------------------------------------------------------------------
    */
    'verification' => [
        'title' => 'Verifique seu e-mail',
        'subtitle' => 'Quase lá! Precisamos que você confirme seu endereço de e-mail.',
        'sent' => 'Um novo link de verificação foi enviado para o seu endereço de e-mail.',
        'check_email' => 'Antes de prosseguir, verifique seu e-mail em busca de um link de verificação.',
        'not_received' => 'Se você não recebeu o e-mail',
        'resend_button' => 'clique aqui para solicitar outro',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status e Alertas
    |--------------------------------------------------------------------------
    */
    'status' => [
        'account_created' => 'Conta criada com sucesso! Verifique seu e-mail para ativar seu perfil.',
        'logged_in' => 'Login realizado com sucesso.',
        'logged_out' => 'Sessão encerrada.',
        'verification_success' => 'E-mail verificado com sucesso!',
        'password_updated' => 'Sua senha foi atualizada.',
    ],
];
