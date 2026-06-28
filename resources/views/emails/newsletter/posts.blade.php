<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        body { font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f4f4f5; color: #18181b; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f4f5; padding: 48px 0; }
        .main { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e4e4e7; border-radius: 32px; overflow: hidden; border-spacing: 0; }
        .header { padding: 48px 48px 32px; text-align: left; }
        .logo { height: 52px; width: 52px; background-color: #18181b; border-radius: 16px; display: inline-block; line-height: 52px; font-weight: bold; font-size: 24px; color: #ffffff; text-align: center; }
        .content { padding: 0 48px 48px; text-align: left; }
        .title { font-size: 28px; font-weight: 800; letter-spacing: -0.04em; margin: 0 0 12px; color: #18181b; line-height: 1.2; }
        .text { font-size: 16px; line-height: 1.6; color: #52525b; margin-bottom: 24px; }

        /* Posts */
        .post-item { padding: 24px 0; border-top: 1px solid #f4f4f5; }
        .post-title { font-size: 19px; font-weight: 700; color: #18181b; text-decoration: none; display: block; margin-bottom: 8px; }
        .post-excerpt { font-size: 14px; color: #71717a; line-height: 1.5; margin-bottom: 12px; }
        .post-link { font-size: 14px; font-weight: 600; color: #18181b; text-decoration: underline; }

        .button-container { padding-top: 32px; text-align: center; }
        .button { display: inline-block; background-color: #18181b; color: #ffffff !important; padding: 16px 40px; font-size: 15px; font-weight: 700; text-decoration: none; border-radius: 18px; }

        .footer-area { background-color: #fafafa; padding: 32px 48px; border-top: 1px solid #f4f4f5; text-align: center; }
        .footer-text { font-size: 12px; color: #a1a1aa; line-height: 1.8; }
        .link { color: #18181b; text-decoration: underline; }
    </style>
</head>
<body>
<div class="wrapper">
    <table class="main" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header">
                <img src="{{ asset('images/logo.png') }}" width="132" alt="{{ config('app.name') }}" style="height: auto; display: block; border: 0; outline: none; text-decoration: none;">
            </td>
        </tr>
        <tr>
            <td class="content">
                @if($customMessage)
                    <h1 class="title">{{ __('notifications.newsletter.important_title') }}</h1>
                    <div class="text">
                        {!! nl2br(e($customMessage)) !!}
                    </div>
                @elseif(count($posts) > 0)
                    <h1 class="title">{{ __('notifications.newsletter.news_title') }}</h1>
                    <p style="font-size: 13px; color: #a1a1aa; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; margin-bottom: 32px;">
                        {{ __('notifications.newsletter.category_label', ['category' => $categoryName]) }}
                    </p>

                    @foreach($posts as $post)
                        <div class="post-item">
                            <a href="{{ route('posts.show', $post['slug']) }}" class="post-title">{{ $post['title'] }}</a>
                            <p class="post-excerpt">{{ Str::limit($post['excerpt'] ?? '', 120) }}</p>
                            <a href="{{ route('posts.show', $post['slug']) }}" class="post-link">{{ __('notifications.newsletter.read_more') }}</a>
                        </div>
                    @endforeach
                @else
                    <h1 class="title">{{ __('notifications.newsletter.greeting_reader') }}</h1>
                    <p class="text">{{ __('notifications.newsletter.default_body') }}</p>
                @endif

                <div class="button-container">
                    <a href="{{ config('app.url') }}" class="button">{{ __('notifications.newsletter.action') }}</a>
                </div>
            </td>
        </tr>
        <tr>
            <td class="footer-area">
                <p class="footer-text">
                    {!! __('notifications.newsletter.unsubscribe', ['url' => $unsubscribeUrl, 'year' => date('Y')]) !!}
                </p>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
