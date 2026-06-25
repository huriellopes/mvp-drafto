<?php

declare(strict_types=1);

return [

    'common' => [
        'platform_footer' => '© :year Drafto. Write with clarity. Publish with identity.',
    ],

    'auth' => [
        'reset_password' => [
            'title' => 'Forgot your password?',
            'body' => 'We received a request to reset your account password. Click the button below to create a new password.',
            'action' => 'Reset my password',
            'expire' => 'This link is temporary and will expire in :count minutes. If you did not request it, you can safely ignore this email.',
            'footer' => 'For your security, never share access links or passwords with anyone.',
        ],

        'magic_link' => [
            'subject' => 'Your Drafto access link',
            'title' => 'Sign in with one click',
            'body' => 'Use the button below to access your Drafto account. No password required.',
            'action' => 'Access my account',
            'expire' => 'This link is valid for :count minutes and can only be used once. If you did not request it, please ignore this email.',
            'footer' => 'For your security, never share this link with anyone.',
        ],
    ],

];
