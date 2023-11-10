<?php

namespace EorPlatform\LaravelPandaDoc\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface PandaDocDocumentInterface
{
    public function documentable(): MorphTo;

    public static function findByName(string $name): self;

    public static function findById(int|string $id): self;
}
