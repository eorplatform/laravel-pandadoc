<?php

namespace EorPlatform\LaravelPandaDoc;

use Exception;
use GuzzleHttp\Client;

class PandaDoc
{
    /**
     * @var string
     */
    protected string $endpoint;

    /**
     * @var string
     */
    protected string $apiKey;

    /**
     * @var Client
     */
    protected Client $client;


    /**
     * Token array
     *
     * @var array
     */
    protected array $tokens;


    /**
     * Recipients array
     *
     * @var array
     */
    protected array $recipients;


    public function __construct()
    {
        $this->apiKey = config('pandadoc.api_key');
        $this->endpoint = config('pandadoc.endpoint');

        $this->client = new Client([
            'base_uri' => $this->endpoint,
            'headers' => [
                'Authorization' => 'API-Key ' . $this->apiKey,
                'Accept' => 'application/json',
            ],
        ]);
    }


    /**
     * Send the document
     *
     * @param string $documentId
     * @param string $subject
     * @param string $message
     * @param bool $silent
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $documentId, string $subject, string $message, bool $silent = false)
    {
        try {
            $response = $this->client->request(
                'POST',
                str_replace(config('pandadoc.api_endpoints.documents.send'), '{id}', $documentId),
                [
                    'json' => [
                        'subject' => $subject,
                        'message' => $message,
                        'silent' => $silent,
                    ],
                ]
            );

            $body = $this->parseBody($response->getBody());
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $body['status'];
    }


    /**
     * List the documents
     *
     * @param array $queryParams
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function listDocuments(array $queryParams = []): array
    {
        try {
            $response = $this->client->request(
                'GET',
                config('pandadoc.api_endpoints.documents.list'),
                [
                    'query' => $queryParams,
                ]
            );
            $body = $this->parseBody($response->getBody());
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $body;
    }



    /**
     * Return the document status
     *
     * @param $documentId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function getDocumentStatus($documentId): mixed
    {
        try {
            $response = $this->client->request(
                'GET',
                str_replace(config('pandadoc.api_endpoints.documents.get_status'), '{id}', $documentId)
            );

            $body = $this->parseBody($response->getBody());

        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $body['status'];
    }


    /**
     * Delete a document with provided document id
     *
     * @param $documentId
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function deleteDocument($documentId): void
    {
        try {

            $response = $this->client->request(
                'DELETE',
                str_replace(config('pandadoc.api_endpoints.documents.delete'), '{id}', $documentId)
            );


        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }


    /**
     * Create recipient array
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $role
     * @param int $signingOrder
     * @return $this
     */
    public function addRecipient(
        string $email,
        string $firstName,
        string $lastName,
        string $role,
        int $signingOrder,
    ): static {
        $this->recipients[] = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'signing_order' => $signingOrder,
        ];

        return $this;
    }


    /**
     * Add a token to the template
     *
     * @param string $role
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function addToken(string $role, string $field, string $value): static
    {
        $this->tokens[] = [
            'name' => $role . '.' . $field,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Create document from template ID
     *
     * @param $name
     * @param $templateID
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createDocumentFromTemplate($name, $templateID): array
    {
        $data = [
            'name' => $name,
            'template_uuid' => $templateID,
            'recipients' => $this->recipients,
            'tokens' => $this->tokens,
            'tags' => [
                'created_via_api',
            ],
        ];

        try {
            $response = $this->client->request(
                'POST',
                config('pandadoc.api_endpoints.documents.create'),
                [
                    'json' => $data,
                ]
            );

            $body = $this->parseBody($response->getBody());

        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return [
            'document_id' => $body['id'],
            'status' => $body['status'],
            'tokens' => $this->tokens,
            'recipients' => $this->recipients,
            'template_id' => $templateID,
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function validate(): void
    {
        if (empty($this->recipients)) {
            throw new Exception('Recipient array cannot be empty!');
        }

        if (empty($this->tokens)) {
            throw new Exception('Tokens array should not be empty!');
        }
    }

    /**
     * Parse the body from the request
     *
     * @param $body
     * @return array
     */
    private function parseBody($body): array
    {
        return json_decode($body, true);
    }

}
