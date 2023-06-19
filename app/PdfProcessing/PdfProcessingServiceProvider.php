<?php

namespace App\PdfProcessing;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;

class PdfProcessingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Stringable::macro('utf8', function(){

            /** @var \Illuminate\Support\Stringable $this  */

            if(is_null($this->value)){
                return new self($this->value);
            }
    
            return new self(iconv(mb_detect_encoding($this->value, mb_detect_order(), true), "UTF-8//IGNORE", $this->value));
        });
    }
}
