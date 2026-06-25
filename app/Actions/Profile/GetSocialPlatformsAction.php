<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\DTOs\SocialPlatformData;
use App\Enums\SocialPlatformEnum;
use Illuminate\Support\Collection;

final class GetSocialPlatformsAction
{
    /**
     * @return Collection<int, SocialPlatformData>
     */
    public function exec(): Collection
    {
        return collect(SocialPlatformEnum::cases())
            ->map(fn (SocialPlatformEnum $platform): SocialPlatformData => SocialPlatformData::fromEnum($platform));
    }
}
