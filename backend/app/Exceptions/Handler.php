<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        ValidationException::class => 'info',
        AuthenticationException::class => 'warning',
        ModelNotFoundException::class => 'warning',
        NotFoundHttpException::class => 'warning',
        MethodNotAllowedHttpException::class => 'warning',
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        ValidationException::class,
        AuthenticationException::class,
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Log all exceptions with context
            $this->logException($e);
        });

        // Handle API requests specifically
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Handle API exceptions and return consistent JSON responses
     */
    protected function handleApiException(Throwable $e, Request $request): JsonResponse
    {
        $statusCode = $this->getStatusCode($e);
        $message = $this->getMessage($e);
        $errors = $this->getErrors($e);

        // Log the exception with request context
        $this->logApiException($e, $request, $statusCode);

        $response = [
            'message' => $message,
            'status' => $statusCode,
        ];

        // Add errors if validation failed
        if ($errors) {
            $response['errors'] = $errors;
        }

        // Add debug information in development
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Get appropriate HTTP status code for the exception
     */
    protected function getStatusCode(Throwable $e): int
    {
        if ($e instanceof ValidationException) {
            return 422;
        }

        if ($e instanceof AuthenticationException) {
            return 401;
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return 404;
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return 405;
        }

        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        if ($e instanceof QueryException) {
            return 500;
        }

        // Default to 500 for any unhandled exceptions
        return 500;
    }

    /**
     * Get user-friendly message for the exception
     */
    protected function getMessage(Throwable $e): string
    {
        if ($e instanceof ValidationException) {
            return 'The given data was invalid.';
        }

        if ($e instanceof AuthenticationException) {
            return 'Unauthenticated.';
        }

        // Check for ModelNotFoundException wrapped in NotFoundHttpException
        if ($e instanceof NotFoundHttpException && $e->getPrevious() instanceof ModelNotFoundException) {
            return 'Resource not found.';
        }

        if ($e instanceof ModelNotFoundException) {
            return 'Resource not found.';
        }

        if ($e instanceof NotFoundHttpException) {
            return 'Endpoint not found.';
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return 'Method not allowed.';
        }

        if ($e instanceof HttpException) {
            return $e->getMessage() ?: 'HTTP error occurred.';
        }

        if ($e instanceof QueryException) {
            return 'Database error occurred.';
        }

        // For production, don't expose internal error messages
        if (!config('app.debug')) {
            return 'An unexpected error occurred.';
        }

        return $e->getMessage() ?: 'An error occurred.';
    }

    /**
     * Get validation errors if applicable
     */
    protected function getErrors(Throwable $e): ?array
    {
        if ($e instanceof ValidationException) {
            return $e->errors();
        }

        return null;
    }

    /**
     * Log exceptions with context
     */
    protected function logException(Throwable $e): void
    {
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        Log::error('Application exception occurred', $context);
    }

    /**
     * Log API exceptions with request context
     */
    protected function logApiException(Throwable $e, Request $request, int $statusCode): void
    {
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'status_code' => $statusCode,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
        ];

        // Don't log sensitive data
        $context['headers'] = $this->sanitizeHeaders($request->headers->all());
        $context['input'] = $this->sanitizeInput($request->all());

        $logLevel = $this->getLogLevel($e);
        Log::log($logLevel, 'API exception occurred', $context);
    }

    /**
     * Get appropriate log level for the exception
     */
    protected function getLogLevel(Throwable $e): string
    {
        if ($e instanceof ValidationException) {
            return 'info';
        }

        if ($e instanceof AuthenticationException) {
            return 'warning';
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return 'warning';
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return 'warning';
        }

        // Log all other exceptions as errors
        return 'error';
    }

    /**
     * Sanitize headers to remove sensitive information
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-csrf-token'];
        
        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***HIDDEN***'];
            }
        }

        return $headers;
    }

    /**
     * Sanitize input to remove sensitive information
     */
    protected function sanitizeInput(array $input): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($input[$field])) {
                $input[$field] = '***HIDDEN***';
            }
        }

        return $input;
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'status' => 401,
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}
