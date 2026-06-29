<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Actions\Profile\GenerateProfileQrCodeAction;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

final class ProfileQrCodeController extends Controller
{
    public function download(string $username): Response
    {
        $profile = Profile::query()
            ->where('username', $username)
            ->with('user')
            ->firstOrFail();

        $user = $profile->user;

        abort_unless($user instanceof User, 404);

        $png = resolve(GenerateProfileQrCodeAction::class)
            ->exec(
                user: $user,
            );

        $fileName = "drafto-qrcode-{$profile->username}.png";

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
