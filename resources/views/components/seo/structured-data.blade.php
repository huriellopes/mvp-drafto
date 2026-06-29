{{-- Site-level Schema.org (Organization + WebSite/SearchAction). Server-rendered
     no <head> de todas as páginas públicas — imune à hidratação lazy do Livewire. --}}
@php
    $home = route('home');

    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => config('app.name'),
        'url' => $home,
        'logo' => asset('images/favicon/android-chrome-512x512.png'),
    ];

    $website = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => config('app.name'),
        'url' => $home,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => route('posts.explore') . '?search={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp
<script type="application/ld+json">@json($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
<script type="application/ld+json">@json($website, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
