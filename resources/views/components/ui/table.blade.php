<div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-100 bg-zinc-50/50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
            <tr>{{ $header }}</tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
            {{ $slot }}
            </tbody>
        </table>
    </div>
    @if(isset($footer))
        <div class="border-t border-zinc-100 bg-zinc-50/30 px-6 py-4">
            {{ $footer }}
        </div>
    @endif
</div>
