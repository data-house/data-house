<?php

namespace App\Copilot\Facades;

use App\Copilot\CopilotManager;
use App\Copilot\Support\Testing\Fakes\CopilotManagerFake;
use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Copilot\CopilotManager
 * @see \App\Copilot\Support\Testing\Fakes\CopilotManagerFake
 */
class Copilot extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return App\Copilot\Support\Testing\Fakes\FakeEngine
     */
    public static function fake(?string $driver = null)
    {
        $driver = $driver ?: static::$app['config']->get('copilot.driver');

        $fakeManager = tap(new CopilotManagerFake(static::getFacadeApplication()), function ($fake): void {
            static::swap($fake);
        });

        return $fakeManager->driver($driver);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return CopilotManager::class;
    }
}