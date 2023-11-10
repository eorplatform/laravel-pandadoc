<?php

namespace EorPlatform\LaravelPandaDoc\Exceptions;

use InvalidArgumentException;

class PandaDocDocumentDoesNotExists extends InvalidArgumentException
{
    public static function create(string $documentName)
    {
        return new static("There is no document named `{$documentName}`.");
    }

    /**
     * @param  int|string  $permissionId
     * @return static
     */
    public static function withId($documentId)
    {
        return new static("There is no [document] with ID `{$documentId}``.");
    }
}
