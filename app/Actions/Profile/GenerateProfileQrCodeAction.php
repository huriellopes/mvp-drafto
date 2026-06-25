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
        return $this->writer(new SvgImageBackEnd())->writeString($user->getShareUrl());
    }

    public function exec(User $user): string
    {
        return $this->compose($user->getShareUrl(), $user->profile->username);
    }

    /**
     * @throws DriverException
     * @throws ImageDecoderException
     * @throws InvalidArgumentException
     */
    private function compose(string $shareUrl, string $username): string
    {
        $manager = new ImageManager(new Driver());

        $qrPng = $this->writer(new ImagickImageBackEnd())->writeString($shareUrl);

        $qr = $manager->decodeBinary($qrPng)->resize(self::QR_SIZE, self::QR_SIZE);

        $logo = $manager->decode(public_path('images/logo.png'))->scale(width: self::LOGO_WIDTH);

        // Alturas para o layout vertical centralizado.
        $logoTop = self::PADDING;
        $qrTop = $logoTop + $logo->height() + 40;
        $usernameTop = $qrTop + self::QR_SIZE + 48;
        $canvasHeight = $usernameTop + 56 + self::PADDING;

        $canvas = $manager->createImage(self::CANVAS_WIDTH, $canvasHeight)->fill('ffffff');

        // insert($image, $x, $y, $alignment) — x/y são offsets a partir do alinhamento.
        $canvas->insert($logo, 0, $logoTop, 'top');
        $canvas->insert($qr, 0, $qrTop, 'top');

        $canvas->text('@' . $username, intdiv(self::CANVAS_WIDTH, 2), $usernameTop, function (FontFactory $font): void {
            $font->filename(resource_path('fonts/DejaVuSans-Bold.ttf'));
            $font->size(38);
            $font->color('18181b');
            $font->align('center');
        });

        return (string) $canvas->encode(new PngEncoder());
    }

    private function writer(ImageBackEndInterface $backEnd): Writer
    {
        return new Writer(new ImageRenderer(new RendererStyle(self::QR_SIZE), $backEnd));
    }
}
