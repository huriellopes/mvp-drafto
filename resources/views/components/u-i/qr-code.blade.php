<div style="width: {{ $size }}px; height: {{ $size }}px;" class="qrcode-wrapper">
    {!! $qrCodeData !!}
</div>

<style>
    .qrcode-wrapper svg {
        width: 100%;
        height: 100%;
    }
    .qrcode-wrapper svg path {
        fill: {{ $color }} !important;
    }
    .qrcode-wrapper svg rect {
        fill: {{ $bgcolor }} !important;
    }
</style>
