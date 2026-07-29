<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Exceptions\DriverException;
use Intervention\Image\Exceptions\ImageDecoderException;
use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

final class GenerateProfileQrCodeAction
{
    private const int CANVAS_WIDTH = 640;

    private const int PADDING = 56;

    private const int QR_SIZE = 460;

    private const int LOGO_WIDTH = 220;

    public function svg(User $user): string
    {
        return $this->svgFromUrl($user->getShareUrl());
    }

    public function exec(User $user): string
    {
        return $this->pngFromUrl($user->getShareUrl(), '@' . $user->profile->username);
    }

    /**
     * Gera o SVG do QR Code para qualquer URL de compartilhamento.
     */
    public function svgFromUrl(string $url): string
    {
        return $this->writer(new SvgImageBackEnd)->writeString($url);
    }

    /**
     * Gera o PNG (com logo e legenda) do QR Code para qualquer URL de compartilhamento.
     *
     * @throws DriverException
     * @throws ImageDecoderException
     * @throws InvalidArgumentException
     */
    public function pngFromUrl(string $url, string $label): string
    {
        return $this->compose($url, $label);
    }

    /**
     * @throws DriverException
     * @throws ImageDecoderException
     * @throws InvalidArgumentException
     */
    private function compose(string $shareUrl, string $label): string
    {
        $manager = new ImageManager(new Driver);

        $qrPng = $this->writer(new ImagickImageBackEnd)->writeString($shareUrl);

        $qr = $manager->decodeBinary($qrPng)->resize(self::QR_SIZE, self::QR_SIZE);

        $logo = $manager->decode(public_path('images/logo.png'))->scale(width: self::LOGO_WIDTH);

        // Alturas para o layout vertical centralizado.
        $logoTop = self::PADDING;
        $qrTop = $logoTop + $logo->height() + 40;
        $labelTop = $qrTop + self::QR_SIZE + 48;
        $canvasHeight = $labelTop + 56 + self::PADDING;

        $canvas = $manager->createImage(self::CANVAS_WIDTH, $canvasHeight)->fill('ffffff');

        // insert($image, $x, $y, $alignment) — x/y são offsets a partir do alinhamento.
        $canvas->insert($logo, 0, $logoTop, 'top');
        $canvas->insert($qr, 0, $qrTop, 'top');

        $canvas->text($label, intdiv(self::CANVAS_WIDTH, 2), $labelTop, function (FontFactory $font): void {
            $font->filename(resource_path('fonts/DejaVuSans-Bold.ttf'));
            $font->size(38);
            $font->color('18181b');
            $font->align('center');
        });

        return (string) $canvas->encode(new PngEncoder);
    }

    private function writer(ImageBackEndInterface $backEnd): Writer
    {
        return new Writer(new ImageRenderer(new RendererStyle(self::QR_SIZE), $backEnd));
    }
}
