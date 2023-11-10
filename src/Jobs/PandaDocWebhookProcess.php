<?php

namespace EorPlatform\LaravelPandaDoc\Jobs;

use EorPlatform\LaravelPandaDoc\Events\PandaDocDocumentStatusUpdated;
use EorPlatform\LaravelPandaDoc\PandaDocRegistrar;

class PandaDocWebhookProcess
{
    public function handle(): void
    {
        // $this->webhookCall // contains an instance of `WebhookCall`
        // get the body and parse
        $payload = $this->webhookCall->payload;

        if (! empty($payload[0])) {

            $body = $payload[0];

            if (isset($body['event']) && $body['event'] === 'document_state_changed') {

                $model = app(PandaDocRegistrar::class)->getPandaDocModelClass();

                $doc = $model::findById($body['data']['id']);

                if (! $doc) {
                    return; // die silently because maybe is request from another environment
                }

                $doc->forceSetStatus($body['data']['status']);

                $newDoc = $model::find($doc->id);

                // Fire the event that the PandaDOcDocument status has been updated
                PandaDocDocumentStatusUpdated::dispatch($newDoc);
            }
        }
    }
}
