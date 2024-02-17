<?php

namespace App\Data;

use App\Models\GeographicRegion as ModelsGeographicRegion;
use PrinsFrank\Standards\Country\CountryAlpha3;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use PrinsFrank\Standards\Region\GeographicRegion;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;

class CountryData extends Data
{
    #[Computed]
    public string $icon;


    public function __construct(
      public CountryAlpha3 $code,
      public string $name,
      public GeographicRegion $region,
    ) {
      $this->icon = match ($this->region) {
            GeographicRegion::World => 'heroicon-m-globe-alt',
            GeographicRegion::Africa => 'heroicon-m-globe-europe-africa',
            GeographicRegion::Americas => 'heroicon-m-globe-americas',
            GeographicRegion::Asia => 'heroicon-m-globe-asia-australia',
            GeographicRegion::Europe => 'heroicon-m-globe-europe-africa',
            GeographicRegion::Oceania => 'heroicon-m-globe-asia-australia',
            default => 'heroicon-m-globe-alt',
        };
    }

    public static function fromCountryCode(CountryAlpha3 $code)
    {
      return new self(
        code: $code,
        name: $code->getNameInLanguage(LanguageAlpha2::English),
        region: ModelsGeographicRegion::getRegionFrom($code)
      );
    }
}
