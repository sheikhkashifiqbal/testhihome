<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Swift_TransportException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception) {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception) {
        if ($request->acceptsHtml() && $request->ajax() == false) {
            return parent::render($request, $exception);
        }

        if ($exception instanceof Swift_TransportException) {
            $code    = 500;
            $message = $exception->getMessage() ?: Response::$statusTexts[$code];
            return $this->errorResponse(
                $message,
                [],
                $code,
                $exception->getFile() . ' line:' . $exception->getLine(),
                $exception->getTraceAsString(),
                false,
                ['type' => 'ErrorMail']
            );
        }

        if ($exception instanceof HttpException) {
            $code    = $exception->getStatusCode();
            $message = $exception->getMessage() ?: Response::$statusTexts[$code];
            return $this->errorResponse(
                $message,
                [],
                $code,
                $exception->getFile() . ' line:' . $exception->getLine(),
                $exception->getTraceAsString()
            );
        }

        if ($exception instanceof ModelNotFoundException) {
            $model = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse(
                "Does not exist any instance of {$model} with the given id",
                [],
                Response::HTTP_NOT_FOUND,
                $exception->getFile() . ' line:' . $exception->getLine(),
                $exception->getTraceAsString()
            );
        }

        if ($exception instanceof AuthorizationException) {
            return $this->errorResponse(
                $exception->getMessage(),
                [],
                Response::HTTP_FORBIDDEN,
                $exception->getFile() . ' line:' . $exception->getLine(),
                $exception->getTraceAsString()
            );
        }

        if ($exception instanceof UnauthorizedException) {
            return $this->errorResponse(
                $exception->getMessage(),
                [],
                Response::HTTP_FORBIDDEN,
                $exception->getFile() . ' line:' . $exception->getLine(),
                $exception->getTraceAsString()
            );
        }

        if ($exception instanceof AuthenticationException) {
            return $this->errorResponse(
                trans('api.'.$exception->getMessage()),
                [],
                Response::HTTP_UNAUTHORIZED,
                $exception->getFile() . ' line:' . $exception->getLine(),
                $exception->getTraceAsString()
            );
        }

        if ($exception instanceof ValidationException) {
            $errors = $exception->validator->errors()->getMessages();
            return $this->errorResponse(
                '',
                $errors,
                Response::HTTP_OK,
                $exception->getFile() . ' line:' . $exception->getLine(),
                $exception->getTraceAsString(),
                true
            );
        }//Response::HTTP_UNPROCESSABLE_ENTITY

        if ($exception instanceof HttpResponseException) {
            $errors = json_decode($exception->getResponse()->getContent(), true);
            //            dd($errors);
            return $this->errorResponse(
                '',
                $errors,
                200,
                $exception->getFile() . ' line:' . $exception->getLine(),
                $exception->getTraceAsString(),
                true
            );
        }

        if ($exception instanceof ClientException) {
            $message = $exception->getResponse()->getBody();
            $code    = $exception->getStatusCode();
            return $this->errorResponse(
                $message,
                [],
                $code,
                $exception->getFile() . ' line:' . $exception->getLine(),
                $exception->getTraceAsString()
            );
        }

        if ($exception instanceof ConnectException) {
            $message = $exception->getMessage();
            $code    = $exception->getCode();
            return $this->errorResponse(
                $message,
                [],
                $code,
                $exception->getFile() . ' line:' . $exception->getLine(),
                $exception->getTraceAsString()
            );
        }

        if ($exception instanceof \LogicException) {
            $message = $exception->getMessage();
            $code    = $exception->getCode();

            return $this->errorResponse(
                $message,
                [],
                $code,
                $exception->getFile() . ' line:' . $exception->getLine(),
                $exception->getTraceAsString()
            );
        }

        return parent::render($request, $exception);
    }
}
