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

{{--
    Gerenciador de Consentimento (LGPD)
    Sempre presente no <head> para que a API exista antes de qualquer rastreador.
    Nada de terceiros é carregado até o usuário consentir explicitamente.
--}}
<script>
    (function () {
        var COOKIE = 'drafto_consent';
        var MAXAGE = 60 * 60 * 24 * 180; // 180 dias

        function read() {
            var match = document.cookie.match(/(?:^|;\s*)drafto_consent=([^;]+)/);

            if (!match) {
                return null;
            }

            try {
                return JSON.parse(decodeURIComponent(match[1]));
            } catch (e) {
                return null;
            }
        }

        window.DraftoConsent = {
            get: read,
            decided: function () {
                return read() !== null;
            },
            allows: function (category) {
                var consent = read();

                return !!(consent && consent[category] === true);
            },
            set: function (state) {
                var value = {
                    v: 1,
                    necessary: true,
                    analytics: !!state.analytics,
                    marketing: !!state.marketing,
                };

                document.cookie = COOKIE + '=' + encodeURIComponent(JSON.stringify(value)) +
                    ';path=/;max-age=' + MAXAGE + ';samesite=lax';

                window.dispatchEvent(new CustomEvent('drafto:consent-updated', { detail: value }));
            },
        };

        window.draftoOpenConsent = function () {
            window.dispatchEvent(new CustomEvent('drafto:open-consent'));
        };
    })();
</script>

@if(app()->isProduction())
    {{-- Google Search Console (meta de verificação — não usa cookies) --}}
    @if($gscId)
        <meta name="google-site-verification" content="{{ $gscId }}" />
    @endif

    {{-- Rastreadores de terceiros: carregados via JS SOMENTE após consentimento --}}
    <script>
        (function () {
            var gaId = @json($gaId);
            var adsId = @json($adsId);
            var pixelId = @json($globalPixelId);
            var context = @json($context);
            var analyticsLoaded = false;
            var marketingLoaded = false;

            function loadGtagBase(id) {
                if (window.__gtagBaseLoaded) {
                    return;
                }

                window.__gtagBaseLoaded = true;

                var script = document.createElement('script');
                script.async = true;
                script.src = 'https://www.googletagmanager.com/gtag/js?id=' + id;
                document.head.appendChild(script);

                window.dataLayer = window.dataLayer || [];
                window.gtag = function () { window.dataLayer.push(arguments); };
                window.gtag('js', new Date());
            }

            function loadAnalytics() {
                if (analyticsLoaded || !gaId) {
                    return;
                }

                analyticsLoaded = true;
                loadGtagBase(gaId);
                window.gtag('config', gaId, {
                    author_username: context.author_username,
                    content_type: context.content_type,
                    category: context.category,
                    send_page_view: true,
                });
            }

            function loadMarketing() {
                if (marketingLoaded) {
                    return;
                }

                marketingLoaded = true;

                if (adsId) {
                    loadGtagBase(adsId);
                    window.gtag('config', adsId);
                }

                if (pixelId) {
                    !function (f, b, e, v, n, t, s) {
                        if (f.fbq) return; n = f.fbq = function () { n.callMethod ?
                        n.callMethod.apply(n, arguments) : n.queue.push(arguments) };
                        if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
                        n.queue = []; t = b.createElement(e); t.async = !0;
                        t.src = v; s = b.getElementsByTagName(e)[0];
                        s.parentNode.insertBefore(t, s)
                    }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

                    fbq('init', pixelId);
                    fbq('track', 'PageView', context);
                }
            }

            function apply() {
                if (window.DraftoConsent.allows('analytics')) {
                    loadAnalytics();
                }

                if (window.DraftoConsent.allows('marketing')) {
                    loadMarketing();
                }
            }

            // Carrega imediatamente se o consentimento já foi dado em visita anterior.
            apply();

            // Reage quando o usuário decide no banner.
            window.addEventListener('drafto:consent-updated', apply);

            // Rastreamento de eventos (data-tracking): só dispara se os scripts já carregaram.
            document.addEventListener('DOMContentLoaded', function () {
                var pageContext = context;

                document.addEventListener('click', function (e) {
                    var target = e.target.closest('[data-tracking]');

                    if (!target) {
                        return;
                    }

                    var eventName = target.getAttribute('data-tracking');

                    if (!eventName.startsWith('dfto_')) {
                        eventName = 'dfto_' + eventName;
                    }

                    var customParams = {};

                    try {
                        var rawParams = target.getAttribute('data-tracking-params');
                        if (rawParams) customParams = JSON.parse(rawParams);
                    } catch (err) {
                        console.warn('Drafto Tracking: Falha ao processar parâmetros do evento', err);
                    }

                    var finalParams = Object.assign({}, pageContext, customParams);

                    if (typeof gtag !== 'undefined') {
                        gtag('event', eventName, finalParams);
                    }

                    if (typeof fbq !== 'undefined') {
                        fbq('trackCustom', eventName, finalParams);
                    }
                });
            });
        })();
    </script>
@endif
