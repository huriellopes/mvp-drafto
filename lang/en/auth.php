<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication (Default Translations)
    |--------------------------------------------------------------------------
    */
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */
    'login' => [
        'title' => 'Sign in to your account',
        'subtitle' => 'Welcome back to Drafto.',
        'email_label' => 'Email',
        'password_label' => 'Password',
        'remember_me' => 'Remember me',
        'forgot_password' => 'Forgot your password?',
        'submit' => 'Sign in now',
        'no_account' => 'Don\'t have an account yet?',
        'register_link' => 'Create a free account',
    ],

    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */
    'register' => [
        'title' => 'Create new account',
        'subtitle' => 'Join the community of brilliant minds.',
        'name_label' => 'Full name',
        'email_label' => 'Email',
        'password_label' => 'Password',
        'password_confirmation_label' => 'Confirm password',
        'submit' => 'Create my account',
        'already_registered' => 'Already have an account?',
        'login_link' => 'Sign in',
        'terms' => 'By registering, you agree to our Terms of Use and Privacy Policy.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Reset
    |--------------------------------------------------------------------------
    */
    'password_reset' => [
        'title' => 'Recover password',
        'subtitle' => 'Enter your email to receive the reset link.',
        'email_label' => 'Email',
        'send_link' => 'Send recovery link',
        'back_to_login' => 'Back to login',
        'reset_title' => 'Reset your password',
        'reset_subtitle' => 'Create a new secure password for your account.',
        'submit' => 'Save new password',
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    */
    'verification' => [
        'title' => 'Verify your email',
        'subtitle' => 'Almost there! We need you to confirm your email address.',
        'sent' => 'A new verification link has been sent to your email address.',
        'check_email' => 'Before proceeding, please check your email for a verification link.',
        'not_received' => 'If you did not receive the email',
        'resend_button' => 'click here to request another',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status and Alerts
    |--------------------------------------------------------------------------
    */
    'status' => [
        'account_created' => 'Account created successfully! Check your email to activate your profile.',
        'logged_in' => 'Logged in successfully.',
        'logged_out' => 'Logged out successfully.',
        'verification_success' => 'Email verified successfully!',
        'password_updated' => 'Your password has been updated.',
    ],
];
