<?php

namespace Ridibooks\Cms\Lib;

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

    public static function requestAccessToken($code, $azure_config)
    {
        $tenent = $azure_config['tenent'];
        $client_id = $azure_config['client_id'];
        $redirect_uri = $azure_config['redirect_uri'];
        $client_secret = $azure_config['client_secret'];

        $stsUrl = "https://login.microsoftonline.com/$tenent/oauth2/token";
        $authenticationRequestBody = "grant_type=authorization_code" .
            "&client_id=" . urlencode($client_id) .
            "&redirect_uri=" . urlencode($redirect_uri) .
            "&client_secret=" . urlencode($client_secret) .
            "&code=" . $code;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $stsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $authenticationRequestBody);
        // By default, HTTPS does not work with curl.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output);
    }

    public static function requestResource($tokenType, $accessToken, $azure_config)
    {
        $tenent = $azure_config['tenent'];
        $resource = $azure_config['resource'];
        $api_version = $azure_config['api_version'];

        $feedURL = "$resource/$tenent/me/?api-version=$api_version";
        $header = [
            "Authorization:$tokenType $accessToken",
            'Accept:application/json;odata=minimalmetadata',
            'Content-Type:application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $feedURL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // By default https does not work for CURL.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output);
    }

    public static function getAccessToken(string $code, array $azure_config) : array
    {
        $tokenOutput = self::requestAccessToken($code, $azure_config);
        $token_type = $tokenOutput->token_type;
        $access_token = $tokenOutput->access_token;
        if (!$token_type || !$access_token) {
            throw new \Exception("[requestAccessToken]\n $tokenOutput->error: $tokenOutput->error_description");
        }

        return [
            'token' => $tokenOutput->access_token,
            'refresh' => $tokenOutput->refresh_token,
        ];
    }

    public static function getTokenResource(string $access_token, array $azure_config) : array
    {
        $resource = AzureOAuth2Service::inspectTokenResource($access_token, $azure_config);
        if (isset($resource['error']) || isset($resource['message'])) {
            throw new \Exception("[requestResource]\n {$resource['error']}: {$resource['message']}");
        }
        return $resource;
    }

    public static function inspectTokenResource(string $access_token, array $azure_config) : array
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

}
