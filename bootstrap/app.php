<?php

use App\Helpers\ResponseObject;
use App\Http\Middleware\HasPermissionMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // 'authorized' => HasPermissionMiddleware::class,
            // 'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $request = request();

        if ($request->is('password/reset') || $request->is('admin/*') || $request->is('admin')) {
            return ;
        }
        $response = new ResponseObject();

        $exceptions->render(function (BadRequestException $exception) use ($response) {
            $response->errors = ['message' => $exception->getMessage()];
            $response->exception = get_class($exception);
            $response->statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json($response, $response->statusCode);
        });

        $exceptions->render(function (ModelNotFoundException $exception) use ($response) {
            $modelName = substr($exception->getModel(), (strrpos($exception->getModel(), '\\') + 1));
            $response->errors = ['message' => 'You try to get model (' . $modelName . ') by wrong id.'];
            $response->exception = get_class($exception);
            $response->statusCode = Response::HTTP_NOT_FOUND;
            return response()->json($response, $response->statusCode);
        });

        $exceptions->render(function (AuthenticationException $exception) use ($response) {
            $response->errors = ['message' => 'This action is unauthenticated'];
            $response->exception = get_class($exception);
            $response->statusCode = Response::HTTP_UNAUTHORIZED;
            return response()->json($response, $response->statusCode);
        });

        $exceptions->render(function (AuthorizationException $exception) use ($response) {
            $response->errors = ['message' => 'This action is unauthorized'];
            $response->exception = get_class($exception);
            $response->statusCode = Response::HTTP_FORBIDDEN;
            return response()->json($response, $response->statusCode);
        });

        $exceptions->render(function (ValidationException $exception) use ($response) {
            $errorMessages = '';
            foreach ($exception->validator->errors()->getMessages() as $parameter => $messages) {
                $errorMessages = $messages[0];
                break;
            }
            $response->errors = $errorMessages;
            $response->exception = get_class($exception);
            $response->statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            return response()->json($response, $response->statusCode);
        });

        $exceptions->render(function (AccessDeniedHttpException $exception) use ($response) {
            $response->errors = ['message' => $exception->getMessage()];
            $response->exception = get_class($exception);
            $response->statusCode = Response::HTTP_FORBIDDEN;
            return response()->json($response, $response->statusCode);
        });

        $exceptions->render(function (HttpException $exception) use ($response) {
            $response->errors = ['message' => $exception->getMessage()];
            $response->exception = get_class($exception);
            $response->statusCode = Response::HTTP_NOT_FOUND;
            return response()->json($response, $response->statusCode);
        });

        $exceptions->render(function (RequestException $exception) use ($response) {
            $response->errors = ['message' => $exception->getMessage()];
            $response->exception = get_class($exception);
            $response->statusCode = Response::HTTP_NOT_FOUND;
            return response()->json($response, $response->statusCode);
        });

        $exceptions->render(function (Throwable $exception) use ($response) {
            if (App::environment('local', 'staging')) {
                $response->errors = [
                    'message'   => $exception->getMessage()
                ];
                $response->exception = [
                    'exception' => get_class($exception),
                    'file'      => $exception->getFile(),
                    'line'      => $exception->getLine(),
                    'trace'     => $exception->getTrace()
                ];
                $response->statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                $response->errors = 'We encounter some issues please contact our support team.';
                $response->exception = get_class($exception);
                $response->statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }
            return response()->json($response, $response->statusCode);
        });
    })->create();
