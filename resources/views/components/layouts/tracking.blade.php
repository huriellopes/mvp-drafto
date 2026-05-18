@php
    $globalPixelId = config('services.meta.pixel_id');
    $gaId = config('services.google.analytics_id');
    $adsId = config('services.google.ads_id');
    $gscId = config('services.google.search_console_id');

    $context = [
        'platform' => 'Drafto',
        'content_type' => 'Platform',
        'author_username' => 'Huriel Lopes',
        'category' => 'N/A',
        'content_name' => 'N/A',
    ];

    if (isset($post) && $post instanceof \App\Models\Post) {
        $context['content_type'] = 'Post';
        $context['author_username'] = "@{$post->author->profile->username}";
        $context['content_name'] = $post->title;
        $context['category'] = $post->category?->name ?? 'Geral';
    } elseif (isset($user) && $user instanceof \App\Models\User) {
        $context['content_type'] = 'Profile';
        $context['author_username'] = "@{$user->profile->username}";
        $context['content_name'] = $user->name;
    }
@endphp

@if(app()->isProduction())
    {{-- Google Search Console (Opcional se já estiver via DNS) --}}
    @if($gscId)
        <meta name="google-site-verification" content="{{ $gscId }}" />
    @endif

    {{-- Google Analytics (GA4) --}}
    @if($gaId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $gaId }}', {
                'author_username': '{{ $context['author_username'] }}',
                'content_type': '{{ $context['content_type'] }}',
                'category': '{{ $context['category'] }}',
                'send_page_view': true
            });
        </script>
    @endif

    {{-- Google Ads --}}
    @if($adsId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $adsId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $adsId }}');
        </script>
    @endif

    {{-- Meta Pixel Global --}}
    @if($globalPixelId)
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');

            fbq('init', '{{ $globalPixelId }}');
            fbq('track', 'PageView', @json($context));
        </script>

        <noscript>
            <img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id={{ $globalPixelId }}&ev=PageView&noscript=1"
            />
        </noscript>
    @endif

    {{-- Sênior: Sistema de Rastreamento Global de Eventos (Event Delegation) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pageContext = @json($context);

            document.addEventListener('click', function(e) {
                const target = e.target.closest('[data-tracking]');
                if (!target) return;

                let eventName = target.getAttribute('data-tracking');

                // Sênior: Padronização forçada dfto_...
                if (!eventName.startsWith('dfto_')) {
                    eventName = 'dfto_' + eventName;
                }

                let customParams = {};

                try {
                    const rawParams = target.getAttribute('data-tracking-params');
                    if (rawParams) customParams = JSON.parse(rawParams);
                } catch (err) {
                    console.warn('Drafto Tracking: Falha ao processar parâmetros do evento', err);
                }

                const finalParams = { ...pageContext, ...customParams };

                // 1. Google Analytics
                if (typeof gtag !== 'undefined') {
                    gtag('event', eventName, finalParams);
                }

                // 2. Meta Pixel
                if (typeof fbq !== 'undefined') {
                    fbq('trackCustom', eventName, finalParams);
                }

                console.log(`[Tracking] Evento: ${eventName}`, finalParams);
            });
        });
    </script>
    @endif

