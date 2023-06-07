<?php

namespace App\Pipelines\Queue\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ReportJobStatus
{
    /**
     * Process the queued job.
     *
     * @param  \Closure(object): void  $next
     */
    public function handle(object $job, Closure $next): void
    {
        try {
            
            $job->run->markAsRunning();
            
            $next($job);
            
            $job->run->markAsCompleted();

        } catch (Throwable $throwable) {

            $job->fail($throwable);

        }
    }
}
