<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;
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
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ValidationException){

            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if ($exception instanceof ModelNotFoundException)
        {
            $modelName = strtolower(class_basename($exception->getModel()));

            return $this->errorResponse($modelName.' 모델의 해당 아이디가 존재하지 않습니다',404);
        }

        if ($exception instanceof AuthenticationException)
        {
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof AuthorizationException)
        {
            return $this->errorResponse($exception->getMessage(), 403);
        }

        if ($exception instanceof NotFoundHttpException)
        {
            return $this->errorResponse('해당 페이지가 존재하지 않습니다', 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException)
        {
            return $this->errorResponse('해당 메소드는 사용할 수 없습니다', 405);
        }

        if ($exception instanceof HttpException)
        {
            return $this->errorResponse($exception->getMessage(),$exception->getStatusCode());
        }

        if ($exception instanceof QueryException)
        {
//            dd($exception);
            $errorCode = $exception->errorInfo[1];

            if ($errorCode == 1451)
            {
                return $this->errorResponse('해당 리소스를 삭제할 수 없습니다,다른 리소스에서 사용중입니다', 409);
            }
        }

        if (config('app.debug'))
        {
            return parent::render($request, $exception);

        }
        return $this->errorResponse('예상치 못한 오류가 발생했습니다. 잠시 후 다시 시도해주세요', 500);


    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();

//        return response()->json($errors, 422);
        return $this->errorResponse($errors,422);

    }
}
