<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiException extends Exception
{
    protected $statusCode;
    protected $errors;

    public function __construct(string $message = '', int $statusCode = 500, array $errors = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function render(Request $request): JsonResponse
    {
        $response = [
            'message' => $this->getMessage(),
            'status' => $this->statusCode,
        ];

        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($this),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
            ];
        }

        return response()->json($response, $this->statusCode);
    }
} 