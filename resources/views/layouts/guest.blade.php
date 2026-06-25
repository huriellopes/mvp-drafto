@props([
    'themeMode' => 'system',
    'primaryColor' => '#18181b',
    'accentColor' => '#3f3f46',
    'secondaryColor' => null,
    'textColor' => null,
    'backgroundColor' => null,
    'buttonStyle' => 'rounded-md',
    'fontFamily' => 'sans',
    'title' => null,
    'seo' => null
])

<x-layouts.guest
    :theme-mode="$themeMode"
    :primary-color="$primaryColor"
    :accent-color="$accentColor"
    :secondary-color="$secondaryColor"
    :text-color="$textColor"
    :background-color="$backgroundColor"
    :button-style="$buttonStyle"
    :font-family="$fontFamily"
    :title="$title"
    :seo="$seo"
>
    {{ $slot }}
</x-layouts.guest>
