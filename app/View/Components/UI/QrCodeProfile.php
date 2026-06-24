<?php

declare(strict_types=1);

namespace App\View\Components\UI;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\View\Component;

class QrCodeProfile extends Component
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
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::L,
            'addQuietzone' => false,
            'svgViewBox' => '0 0 100 100',
            // v6 retorna data-URI base64 por padrão; o blade embute o SVG inline ({!! !!}),
            // então mantemos o markup cru e sem header XML.
            'outputBase64' => false,
            'svgAddXmlHeader' => false,
        ]);

        $this->qrCodeData = (new QRCode($options))->render($data);
    }

    public function render()
    {
        return view('components.u-i.qr-code');
    }
}
