<?php

namespace Modules\School\Exceptions;

use Exception;

class SchoolModuleException extends Exception
{
    /**
     * Create a "not found" exception.
     */
    public static function notFound(string $entity, int|string $id): self
    {
        return new self("{$entity} with ID {$id} not found.", 404);
    }

    /**
     * Create an "unauthorized" exception.
     */
    public static function unauthorized(string $action = ''): self
    {
        $message = 'You are not authorized to perform this action.';
        if ($action !== '' && $action !== '0') {
            $message = "You are not authorized to {$action}.";
        }

        return new self($message, 403);
    }

    /**
     * Create a "validation" exception.
     */
    public static function validation(string $message): self
    {
        return new self($message, 422);
    }

    /**
     * Create a "business rule violation" exception.
     */
    public static function businessRule(string $message): self
    {
        return new self($message, 409);
    }

    /**
     * Create a "data integrity" exception.
     */
    public static function dataIntegrity(string $message): self
    {
        return new self($message, 500);
    }
}
