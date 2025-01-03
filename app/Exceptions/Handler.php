<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            //
        });
        

        $this->renderable(function (HttpException $e, Request $request) {

            if($e->getStatusCode() === 419 || ($e->getPrevious() && $e->getPrevious() instanceof TokenMismatchException)){
                
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('status', __('Looks like your session expired. Please try refreshing the page and submitting your request again.'))
                    ->with('flash', [
                        'bannerStyle' => 'warning',
                        'banner' => __('Looks like your session expired. Please try refreshing the page and submitting your request again.'),
                    ]);
            }
        });
    }
}
