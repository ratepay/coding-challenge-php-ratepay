<?php

namespace App\Traits;

use App\Exceptions\ApiException;

trait ThrowsApiExceptions
{
    /**
     * Throw a 400 Bad Request exception
     */
    protected function throwBadRequest(string $message = 'Bad request', array $errors = []): void
    {
        throw new ApiException($message, 400, $errors);
    }

    /**
     * Throw a 401 Unauthorized exception
     */
    protected function throwUnauthorized(string $message = 'Unauthorized'): void
    {
        throw new ApiException($message, 401);
    }

    /**
     * Throw a 403 Forbidden exception
     */
    protected function throwForbidden(string $message = 'Forbidden'): void
    {
        throw new ApiException($message, 403);
    }

    /**
     * Throw a 404 Not Found exception
     */
    protected function throwNotFound(string $message = 'Resource not found'): void
    {
        throw new ApiException($message, 404);
    }

    /**
     * Throw a 409 Conflict exception
     */
    protected function throwConflict(string $message = 'Conflict', array $errors = []): void
    {
        throw new ApiException($message, 409, $errors);
    }

    /**
     * Throw a 422 Unprocessable Entity exception
     */
    protected function throwValidationError(string $message = 'Validation failed', array $errors = []): void
    {
        throw new ApiException($message, 422, $errors);
    }

    /**
     * Throw a 500 Internal Server Error exception
     */
    protected function throwInternalError(string $message = 'Internal server error'): void
    {
        throw new ApiException($message, 500);
    }

    /**
     * Throw a custom API exception
     */
    protected function throwApiException(string $message, int $statusCode, array $errors = []): void
    {
        throw new ApiException($message, $statusCode, $errors);
    }
} 