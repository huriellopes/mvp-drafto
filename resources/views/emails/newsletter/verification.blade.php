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
        <div style="margin-bottom: 24px;">
            <img src="{{ asset('images/logo.png') }}" width="120" alt="{{ config('app.name') }}" style="height: auto; display: block; border: 0; outline: none; text-decoration: none;">
        </div>
        <h2 style="color: #111827;">{{ __('mail.newsletter.verification.greeting') }}</h2>
        <p>{!! __('mail.newsletter.verification.body1') !!}</p>
        <p>{{ __('mail.newsletter.verification.body2') }}</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $verificationUrl }}" class="button" style="color: #ffffff;">{{ __('mail.newsletter.verification.action') }}</a>
        </div>
        
        <p style="font-size: 14px; color: #6B7280;">{{ __('mail.newsletter.verification.ignore') }}</p>
        
        <hr style="border: 0; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        <p style="font-size: 12px; color: #9CA3AF; text-align: center;">{{ __('mail.newsletter.verification.footer') }}</p>
    </div>
</body>
</html>
