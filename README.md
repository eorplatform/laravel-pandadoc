# Laravel PandaDoc

This package is aimed for using the PandaDoc API with the Laravel. It provides a database table where you can store the responses from the documents and create a e-signature workflow for your app using the PandaDoc API.

## Install

Install using composer

```bash
composer require eorplatform/laravel-pandadoc
```

After you install the package, simply run the following command

```bash
php artisan laravel-pandadoc:install
```

Using the code above, that command will:

* publish the config file
* publish the migrations
* ask if migrations should be run now


## Usage scenario

Imagine you want to integrate the e-signature workflow for your app and you are using PandaDoc as your e-signature provider. With this package you can easily communicate with the API, using the PandaDoc templates and create the functionality on your website as you wish.

For example you can initiate signing of the contract like this: 

```php
$pandaApi = PandaDoc::addRecipient(
    'johndoe@example.com', // email
    'John', // First name
    'Doe', // Last name
    'CEO', // Role
    1, // his signing order)
    ->addRecipient(
    'peterfoe@example.com',
    'Peter',
    'Foe',
    'CTO',
    2)
    ->addToken('Client', 'AnnexNumber', 'A') // Your dynamic variable inside the PandaDoc templates
    ->addToken('Document', 'EffectiveDate', now()->format('F jS, Y'))
    ->createDocumentFromTemplate('My new MSA Annex', 'panda_doc_template_id'); // First param is name and the other is your PandaDoc template ID
    

// You can then store the response in your database related model
$pandaDocument = 
            MyModel::pandaDocDocument()->create([
                'name' => 'My new MSA Annex',
                'document_id' => $pandaApi['document_id'],
                'template_id' => $pandaApi['template_id'],
                'tokens' => $pandaApi['tokens'],
                'recipients' => $pandaApi['recipients'],
                'invite_expire_at' => now()->addDays(config('panda-doc.invitation_expire_after_days'))
            ]);

// We are also using the spatie/status package so you can set the status like that
$pandaDocument->setStatus($pandaApi['status']);
```


## Utilizing webhooks from PandaDoc

Under the hood, this package relies on and installs the spatie/laravel-webhook-client for you. The only thing you need to do is to run:

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-config"
```

Which will basically publish the config file for the webhooks package.

### Configuring the webhooks

This is the contents of the file that will be published at `config/webhook-client.php`:

```php
<?php

return [
    'configs' => [
        [
            /*
             * This package supports multiple webhook receiving endpoints. If you only have
             * one endpoint receiving webhooks, you can use 'default'.
             */
            'name' => 'default',

            /*
             * We expect that every webhook call will be signed using a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => env('WEBHOOK_CLIENT_SECRET'),

            /*
             * The name of the header containing the signature.
             */
            'signature_header_name' => 'Signature',

            /*
             *  This class will verify that the content of the signature header is valid.
             *
             * It should implement \Spatie\WebhookClient\SignatureValidator\SignatureValidator
             */
            'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,

            /*
             * This class determines if the webhook call should be stored and processed.
             */
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

            /*
             * This class determines the response on a valid webhook call.
             */
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,

            /*
             * The classname of the model to be used to store webhook calls. The class should
             * be equal or extend Spatie\WebhookClient\Models\WebhookCall.
             */
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            /*
             * In this array, you can pass the headers that should be stored on
             * the webhook call model when a webhook comes in.
             *
             * To store all headers, set this value to `*`.
             */
            'store_headers' => [

            ],

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\Jobs\ProcessWebhookJob.
             */
            'process_webhook_job' => '',
        ],
    ],

    /*
     * The number of days after which models should be deleted.
     *
     * Set to null if no models should be deleted.
     */
    'delete_after_days' => 30,
];
```


Change the following in the first (or just add another item in array) with:

```php

/*
             * This package supports multiple webhook receiving endpoints. If you only have
             * one endpoint receiving webhooks, you can use 'default'.
             */
            'name' => 'pandadoc',
            
            /*
             * We expect that every webhook call will be signed using a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => 'Your signing secret from PandaDoc',
            
             /*
             * The name of the header containing the signature.
             */
            'signature_header_name' => 'signature',
            
            /*
             *  This class will verify that the content of the signature header is valid.
             *
             * It should implement \Spatie\WebhookClient\SignatureValidator\SignatureValidator
             */
            'signature_validator' => \EorPlatform\LaravelPandaDoc\PandaDocWebhookSignatureValidator::class,
            
            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\Jobs\ProcessWebhookJob.
             */
            'process_webhook_job' => \EorPlatform\LaravelPandaDoc\Jobs\PandaDocWebhookProcess::class,

```
Everything else should remain the same.

Webhook job under the hood looks like: 

```php

class PandaDocWebhookProcess
{
    public function handle(): void
    {
        // $this->webhookCall // contains an instance of `WebhookCall`
        // get the body and parse
        $payload = $this->webhookCall->payload;

        if ( !empty( $payload[0] ) ) {

            $body = $payload[0];

            if ( isset( $body['event'] ) && $body['event'] === 'document_state_changed' ) {

                $model = app(PandaDocRegistrar::class)->getPandaDocModelClass();

                $doc = $model::findById($body['data']['id']);

                if ( !$doc ) {
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
```

And it checks the status from PandaDoc API, set the current status inside the DB (if you have stored it) and then dispatches the event


## Events

You can utilize your listeners by using the provided `PandaDocumentStatusUpdated` event. For example: 

```php
 PandaDocumentStatusUpdated::class => [
            DoWhatever::class,
            DoSomethingElse::class
        ],
```
