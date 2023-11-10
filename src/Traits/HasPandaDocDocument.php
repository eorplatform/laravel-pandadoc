<?php

namespace EorPlatform\LaravelPandaDoc\Traits;

use EorPlatform\LaravelPandaDoc\PandaDocRegistrar;
use Illuminate\Database\Eloquent\Relations\morphOne;

trait HasPandaDocDocument
{
    private ?string $pandaDocDocumentModelClass = null;

    public function getPandaDocModelsClass(): string
    {
        if (! $this->pandaDocDocumentModelClass) {
            $this->pandaDocDocumentModelClass = app(PandaDocRegistrar::class)->getPandaDocModelClass();
        }

        return $this->pandaDocDocumentModelClass;
    }

    /**
     * Has PandaDoc document relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphOne
     */
    public function pandaDocDocument(): morphOne
    {
        return $this->morphOne($this->getPandaDocModelsClass(), 'documentable');
    }
}
