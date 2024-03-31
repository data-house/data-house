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

    /**
     * Check if a user has a preference
     *
     * @param \App\Models\Preference $preference
     * @param mixed $value If provided the value to check preference against
     * @return bool true if the user has a preference set. If $value is specified return true if user has a preference and its value is the same as requested
     */
    public function hasPreference(Preference $preference, mixed $value = null): bool
    {
        $userPreference = $this->getPreference($preference);

        if(is_null($value)){
            return !is_null($userPreference);
        }

        return !is_null($userPreference) && $userPreference->hasValue($value);
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
