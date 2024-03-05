<?php

namespace App\Schedule;

use Illuminate\Console\Scheduling\ManagesFrequencies;


class CronBuilder
{

    use ManagesFrequencies;

    /**
     * The cron expression representing the frequency.
     *
     * @var string
     */
    protected $expression = '* * * * *';


    public static function make(): self
    {
        return new self();
    }

    /**
     * Get the resulting cron expression
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

}
