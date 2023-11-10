<?php

namespace EorPlatform\LaravelPandaDoc;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class PandaDocWebhookSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signature = $request->get($config->signatureHeaderName);

        if (! $signature) {
            return false;
        }

        $signingSecret = $config->signingSecret;

        if (empty($signingSecret)) {
            throw InvalidConfig::signingSecretNotSet();
        }

        $computedSignature = hash_hmac('sha256', $request->getContent(), $signingSecret);

        Log::info($computedSignature);

        return hash_equals($signature, $computedSignature);

    }
}
