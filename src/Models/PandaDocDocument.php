<?php

namespace EorPlatform\LaravelPandaDoc\Models;

use EorPlatform\LaravelPandaDoc\Contracts\PandaDocDocumentInterface;
use EorPlatform\LaravelPandaDoc\Exceptions\PandaDocDocumentDoesNotExists;
use EorPlatform\LaravelPandaDoc\PandaDocRegistrar;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\ModelStatus\HasStatuses;

class PandaDocDocument extends Model implements PandaDocDocumentInterface
{
    use HasUlids;
    use HasStatuses;

    /**
     * Fillable params
     *
     * @var array
     */
    protected $fillable = [
        'document_id',
        'template_id',
        'tokens',
        'recipients',
        'invite_expire_at',
        'name',
        'is_queued',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'tokens' => 'array',
        'recipients' => 'array',
        'invite_expire_at' => 'datetime',
        'is_queued' => 'boolean',
        'completed_at' => 'datetime',
    ];


    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Find a document by its id
     *
     * @return PandaDocDocumentInterface|PandaDocDocument
     *
     * @throws PandaDocDocumentDoesNotExists
     */
    public static function findById(int|string $id): PandaDocDocumentInterface
    {
        $document = static::getDocument(['document_id' => $id]);

        if (! $document) {
            throw PandaDocDocumentDoesNotExists::withId($id);
        }

        return $document;
    }

    /**
     * @param int|string $name
     * @return PandaDocDocumentInterface|PandaDocDocument
     * @throws PandaDocDocumentDoesNotExists
     */
    public static function findByName(int|string $name): PandaDocDocumentInterface
    {
        $document = static::getDocument(['name' => $name]);

        if (! $document) {
            throw PandaDocDocumentDoesNotExists::create($name);
        }

        return $document;
    }

    /**
     * @param array $params
     * @param bool $onlyOne
     * @return Collection
     */
    protected static function getDocuments(array $params = [], bool $onlyOne = false): Collection
    {
        return app(PandaDocRegistrar::class)
            ->setPandaDocModelClass(static::class)
            ->getDocuments($params, $onlyOne);
    }

    /**
     * @param array $params
     * @return PandaDocDocumentInterface|null
     */
    protected static function getDocument(array $params = []): ?PandaDocDocumentInterface
    {
        /** @var PandaDocDocumentInterface|null */
        return static::getDocuments($params, true)->first();
    }
}
