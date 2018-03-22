<?php

namespace Ridibooks\Cms\Lib;

use GuzzleHttp\Client;

class AzureOAuth2Service
{
    public static function getAuthorizeEndPoint($azure_config)
    {
        $tenent = $azure_config['tenent'];
        $client_id = $azure_config['client_id'];
        $redirect_uri = $azure_config['redirect_uri'];
        $resource = $azure_config['resource'];

        return "https://login.windows.net/$tenent/oauth2/authorize?response_type=code" .
            "&client_id=" . urlencode($client_id) .
            "&resource=" . urlencode($resource) .
            "&redirect_uri=" . urlencode($redirect_uri);
    }

    public static function getLogoutEndpoint($azure_config, $redirect_url)
    {
        $tenent = $azure_config['tenent'];
        return "https://login.windows.net/$tenent/oauth2/logout?"
            . "post_logout_redirect_uri=" . urlencode($redirect_url);
    }

    public static function requestToken($code, $azure_config)
    {
        $endpoint = "https://login.microsoftonline.com/{$azure_config['tenent']}/oauth2/token";
        $client = new Client(['verify' => false]);
        $response = $client->post($endpoint, [
            'http_errors' => false,
            'form_params' => [
                'grant_type' => 'authorization_code' ,
                'client_id' => $azure_config['client_id'],
                'client_secret' => $azure_config['client_secret'],
                'redirect_uri' => $azure_config['redirect_uri'],
                'code' => $code,
            ],
        ]);

        return json_decode($response->getBody());
    }

    public static function requestResource($tokenType, $accessToken, $azure_config)
    {
        $tenent = $azure_config['tenent'];
        $resource = $azure_config['resource'];
        $api_version = $azure_config['api_version'];

        $endpoint = "$resource/$tenent/me/?api-version=$api_version";
        $client = new Client(['verify' => false]);
        $response = $client->get($endpoint, [
            'http_errors' => false,
            'headers' => [
                'Authorization' => "$tokenType $accessToken",
                'Accept' => 'application/json;odata=minimalmetadata',
                'odata' => 'minimalmetadata',
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody());
    }

    public static function getTokens(string $code, array $azure_config): array
    {
        $token_resource = self::requestToken($code, $azure_config);
        return self::verifyTokenResponse($token_resource);
    }

    public static function introspectToken(string $access_token, array $azure_config): array
    {
        $azure_resource = self::requestResource('bearer', $access_token, $azure_config);
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

    public static function refreshToken(string $refresh_token, $azure_config): array
    {
        $endpoint = "https://login.microsoftonline.com/{$azure_config['tenent']}/oauth2/token";
        $client = new Client(['verify' => false]);
        $response = $client->post($endpoint, [
            'http_errors' => false,
            'form_params' => [
                'grant_type' => 'refresh_token' ,
                'client_id' => $azure_config['client_id'],
                'client_secret' => $azure_config['client_secret'],
                'resource' => $azure_config['resource'],
                'refresh_token' => $refresh_token,
            ],
        ]);

        $token_resource = json_decode($response->getBody());

        return self::verifyTokenResponse($token_resource);
    }

    private static function verifyTokenResponse($token_resource)
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
