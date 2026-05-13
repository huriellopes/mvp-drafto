<?php

declare(strict_types=1);

namespace App\View\Components\UI;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\View\Component;

class QrCode extends Component
{
    public string $qrCodeData;

    public function __construct(
        public string $data,
        public int $size = 200,
        public string $color = '#000000',
        public string $bgcolor = '#ffffff',
    ) {
        $options = new QROptions([
            'version' => 5,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_L,
            'addQuietzone' => false,
            'svgViewBox' => '0 0 100 100',
        ]);

        $this->qrCodeData = (new QRCode($options))->render($data);
    }

    public function render()
    {
        return view('components.u-i.qr-code');
    }
}
