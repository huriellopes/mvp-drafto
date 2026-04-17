@props([
    'themeMode' => 'system',
    'primaryColor' => '#18181b',
    'accentColor' => '#3f3f46',
    'title' => null,
    'seo' => null
])

<x-layouts.guest
    :theme-mode="$themeMode"
    :primary-color="$primaryColor"
    :accent-color="$accentColor"
    :title="$title"
    :seo="$seo"
>
    {{ $slot }}
</x-layouts.guest>
