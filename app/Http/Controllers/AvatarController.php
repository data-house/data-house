<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\RateLimiter;
use Intervention\Image\Laravel\Facades\Image;

class AvatarController extends Controller
{
    
    public function __invoke(Request $request, string $avatar)
    {
        if (App::isProduction()) { 
            $this->ratelimit($request, $avatar);
        }

        $letter = mb_strtoupper($avatar);

        $font_path = public_path('standard_fonts/LiberationSans-Regular.ttf');

        $font_size = 42;

        $image = Image::create(64, 64)
            ->fill('#16a34a')
            ->text($letter, 32, 32, function ($font) use ($font_path, $font_size) {
                $font->file($font_path);
                $font->size($font_size);
                $font->color('#ecfccb');
                $font->align('center');
                $font->valign('middle');
            })
            ->encodeByExtension('png');

        return response($image, 200)
            ->header('Content-Type', $image->mediaType())
            ->header('Cache-Control', 'public, max-age=2592000, s-maxage=2592000, immutable');
    }

    protected function ratelimit(Request $request, $path): void
    {
        $allowed = RateLimiter::attempt(
            key: 'avatar:' . $request->ip() . ':' . $path,
            maxAttempts: 5,
            callback: fn() => true
        );
 
        abort_if(!$allowed, 429);
    }
}
