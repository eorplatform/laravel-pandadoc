<?php

namespace EorPlatform\LaravelPandaDoc\Events;

use EorPlatform\LaravelPandaDoc\Contracts\PandaDocDocumentInterface;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PandaDocDocumentStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PandaDocDocumentInterface $document;

    public function __construct(PandaDocDocumentInterface $pandaDocDocument)
    {
        $this->document = $pandaDocDocument;
    }
}
