<?php

namespace App\Copilot;

class Copilot
{
    /**
     * Determine if Copilot is enabled.
     */
    public static function enabled(): bool
    {
        return 
            static::hasSummaryFeatures() ||
            static::hasQuestionFeatures() ||
            static::hasTaggingFeatures();
    }
    
    /**
     * Determine if Copilot is disabled.
     */
    public static function disabled(): bool
    {
        return ! static::enabled();
    }

    /**
     * Determine if Copilot is supporting summary generation features.
     */
    public static function hasSummaryFeatures(): bool
    {
        return (bool)config('copilot.features.summary', false);
    }

    /**
     * Determine if Copilot is supporting question and answer features.
     */
    public static function hasQuestionFeatures(): bool
    {
        return (bool)config('copilot.features.question', false);
    }

    /**
     * Determine if Copilot is supporting tagging features.
     */
    public static function hasTaggingFeatures(): bool
    {
        return (bool)config('copilot.features.tagging', false);
    }
}