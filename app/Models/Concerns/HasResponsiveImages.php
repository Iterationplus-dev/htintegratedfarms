<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Shared Media Library setup: WebP at 400/800/1600 px plus a 1600 px JPEG fallback
 * (handoff §State Management). Conversions never upscale and are queued in production.
 */
trait HasResponsiveImages
{
    use InteractsWithMedia;

    /** @var list<int> */
    public const RESPONSIVE_WIDTHS = [400, 800, 1600];

    public function registerMediaConversions(?Media $media = null): void
    {
        foreach (self::RESPONSIVE_WIDTHS as $width) {
            $this->addMediaConversion("w{$width}")
                ->fit(Fit::Max, $width, 4000)
                ->format('webp')
                ->quality(78);
        }

        $this->addMediaConversion('fallback')
            ->fit(Fit::Max, 1600, 4000)
            ->format('jpg')
            ->quality(82);
    }
}
