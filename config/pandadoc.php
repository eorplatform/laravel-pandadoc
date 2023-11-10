<?php

return [

    /*
     * Your PandaDoc API Key
     */
    'api_key' => env('PANDA_DOC_API_KEY', ''),


    /*
     * PandaDoc API endpoint
     */
    'endpoint' => env('PANDA_DOC_API_ENDPOINT', 'https://api.pandadoc.com/public/v1'),

    /*
     * Define the incoming webhooks
     * on which the statuses are coming to your app
     * For complete usage look into readme
     */
    'webhooks' => [
        /*
         * Define your internal incoming url which will be responsive for handling url
         * - You do not need to do any of the coding and controllers since under the hood
         * this package uses spatie/laravel-webhook-client package
         * @url https://github.com/spatie/laravel-webhook-client
         */
        'url' => 'webhooks/handle/pandadoc'
    ],

    /*
     * All current PandaDoc API endpoints used by this package
     */
    'api_endpoints' => [
        'documents' => [
            'create' => '/documents',
            'send' => '/documents/{id}/send',
            'list' => '/documents',
            'get_status' => '/documents/{id}',
            'delete' => '/documents/{id}'
        ]
    ],


    /*
    * Invitation expiration days
    */
    'invitation_expire_after_days' => env('PANDA_DOC_INVITATION_EXPIRATION_DAYS', 15),

    /*
     * Table name for storing PandaDoc documents
     * Yu can change it however you like
     */
    'table_name' => 'panda_doc_documents',

    'models' => [
        /*
         * When using the "HasPandaDocDocument" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your panda doc documents.
         *
         * The model you want to use as a Role model needs to implement the
         * `EorPlatform\LaravelPandaDoc\Contracts\PandaDocDocumentInterface` contract.
         */

        'document' => EorPlatform\LaravelPandaDoc\Models\PandaDocDocument::class,
    ],


    /*
     * Panda doc document statuses
     *
     */
    'statuses' => [
        'document.uploaded', // The document has just been created or uploaded. It is in processing and will be in document.draft state momentarily.
        'document.draft', // The document is in a draft state. All aspects of the document can be edited in this state. Our API does not support edits after the document has been created, but it can still be edited manually on
        'document.sent', // The document has been "sealed" and optionally sent. No further document edits can occur except for document recipient(s) filling out or signing the document.
        'document.viewed', // Document recipient(s) have viewed the sent document.
        'document.waiting_approval', // The document has an automatic approval workflow and has not yet been approved.
        'document.rejected', // The document has an automatic approval workflow and was rejected.
        'document.approved', // The document has an automatic approval workflow and was approved.
        'document.waiting_pay', // The document has a Stripe payment option and is awaiting payment.
        'document.paid', // The document has a Stripe payment option and was paid.
        'document.completed', // The document has been completed by all recipients.
        'document.voided', // The document expired and is no longer available for completion or signature.
        'document.declined' // The document was manually marked as "Declined"
    ]

];
