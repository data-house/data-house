<?php

namespace App;

use App\Models\Preference;
use App\Models\UserPreference;
use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;

trait HasPreferences
{

    /**
     * Get the user's preferences.
     *
     */
    public function userPreferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    public function hasPreference(Preference $preference): bool
    {
        return !is_null($this->getPreference($preference));
    }
    
    public function getPreference(Preference $preference): mixed
    {
        return $this->userPreferences->firstWhere('setting', $preference);
    }

    public function setPreference(Preference $preference, $value)
    {
        $this->userPreferences()->updateOrCreate(
            ['setting' => $preference],
            ['value' => $value]);
    }

    
}
