<?php

namespace EorPlatform\LaravelPandaDoc;

use EorPlatform\LaravelPandaDoc\Contracts\PandaDocDocumentInterface;

class PandaDocRegistrar
{
    protected string $pandaDocDocumentModelClass;

    public function __construct()
    {
        $this->pandaDocDocumentModelClass = config('pandadoc.models.document');
    }

    public function setPandaDocModelClass($modelClass)
    {
        $this->pandaDocDocumentModelClass = $modelClass;
        config()->set('pandadoc.models.document', $modelClass);
        app()->bind(PandaDocDocumentInterface::class, $modelClass);

        return $this;
    }


    public function getPandaDocModelClass()
    {
        return $this->pandaDocDocumentModelClass;
    }
}
