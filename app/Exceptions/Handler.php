<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\Permission\Exceptions\UnauthorizedException;
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($this->shouldReport($exception)) {
            \App\Services\HataKaydi::istisna($exception); // Sistem Performansi > Hatalar
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */

    public function render($request, Exception $exception)
    {
        if ($exception instanceof UnauthorizedException) {
            return response()->view('isletmeadmin.yetkisizerisim');
            
        }
        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {

        // API/AJAX istegi (Accept: json olmasa da): yonlendirme degil 401.
        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $guard = array_get($exception->guards(),0);
         // Listede olmayan guard (api, web...) 'Undefined variable: login' ile 500 veriyordu.
         // ('login' adinda rota yok; varsayilan panel girisi.)
         $login = 'isletmeadmin.login';
         switch ($guard) {

            case 'isletmeyonetim':
                $login = 'isletmeadmin.login';
                break;
            case 'sistemyonetim':
                $login = 'superadmin.login';
                break;
             case 'satisortakligi':
                $login = 'satisortakligi.login';
                break;
        }
        return redirect()->guest(route($login));
    }
}
