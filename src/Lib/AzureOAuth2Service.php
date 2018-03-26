<?php

namespace Ridibooks\Cms\Lib;

use GuzzleHttp\Client;

class AzureOAuth2Service
{
    private $tenent;
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $resource;
    private $api_version;

    /** @var Client */
    private $http;

    public function __construct(array $azure_config, array $guzzle_config = [])
    {
        $this->tenent = $azure_config['tenent'];
        $this->client_id = $azure_config['client_id'];
        $this->client_secret = $azure_config['client_secret'];
        $this->redirect_uri = $azure_config['redirect_uri'];
        $this->resource = $azure_config['resource'];
        $this->api_version = $azure_config['api_version'];

        $guzzle_config = array_merge(['verify' => false], $guzzle_config);
        $this->http = new Client($guzzle_config);
    }

    public function getAuthorizeEndPoint(): string
    {
        return "https://login.windows.net/$this->tenent/oauth2/authorize?response_type=code" .
            "&client_id=" . urlencode($this->client_id) .
            "&resource=" . urlencode($this->resource) .
            "&redirect_uri=" . urlencode($this->redirect_uri);
    }

    public function getLogoutEndpoint(string $redirect_url): string
    {
        return "https://login.windows.net/$this->tenent/oauth2/logout?"
            . "post_logout_redirect_uri=" . urlencode($redirect_url);
    }

    private function requestToken(string $code): \stdClass
    {
        $endpoint = "https://login.microsoftonline.com/$this->tenent/oauth2/token";
        $response = $this->http->post($endpoint, [
            'http_errors' => false,
            'form_params' => [
                'grant_type' => 'authorization_code' ,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri,
                'code' => $code,
            ],
        ]);

        return json_decode($response->getBody());
    }

    public function requestResource(string $tokenType, string $accessToken): \stdClass
    {
        $endpoint = "$this->resource/$this->tenent/me/?api-version=$this->api_version";
        $response = $this->http->get($endpoint, [
            'http_errors' => false,
            'headers' => [
                'Authorization' => "$tokenType $accessToken",
                'Accept' => 'application/json;odata=minimalmetadata',
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody());
    }

    /**
     * @throws Exception
     */
    public function getTokens(string $code): array
    {
        $token_resource = self::requestToken($code);
        return self::parseTokenReource($token_resource);
    }

    public function introspectToken(string $access_token): array
    {
        $azure_resource = self::requestResource('bearer', $access_token);
        if ($error = $azure_resource->{'odata.error'}) {
            return [
                'error' => $error->code,
                'message' => $error->message->value,
            ];
        }

        return [
            'user_id' => $azure_resource->mailNickname,
            'user_name' => $azure_resource->displayName,
        ];
    }

    /**
     * @throws Exception
     */
    public function refreshToken(string $refresh_token): array
    {
        $endpoint = "https://login.microsoftonline.com/$this->tenent/oauth2/token";
        $response = $this->http->post($endpoint, [
            'http_errors' => false,
            'form_params' => [
                'grant_type' => 'refresh_token' ,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'resource' => $this->resource,
                'refresh_token' => $refresh_token,
            ],
        ]);

        $token_resource = json_decode($response->getBody());

        return self::parseTokenReource($token_resource);
    }

    /**
     * @throws Exception
     */
    private function parseTokenReource($token_resource): array
    {
        $token_type = $token_resource->token_type;
        $access_token = $token_resource->access_token;
        if (!$token_type || !$access_token) {
            throw new \Exception("[requestToken]\n $token_resource->error: $token_resource->error_description");
        }

        return [
            "access" => $access_token,
            "refresh" => $token_resource->refresh_token,
            "expires_on" => $token_resource->expires_on,
        ];
    }
}
