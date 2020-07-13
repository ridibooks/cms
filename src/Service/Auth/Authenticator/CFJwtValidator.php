<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Ridibooks\Cms\Service\Auth\Exception\InvalidCredentialException;

class CFJwtValidator
{
    const PUBLIC_KEY_PATH = "/cdn-cgi/access/certs";

    public function decodeJwt($jwt, $keys)
    {
        foreach ($keys as $key) {
            try {
                return JWT::decode($jwt, $key, ['RS256']);
            } catch (\Exception $e) {
                error_log('Decode has failed. Try with other key');
                #pass
            }
        }

        throw new InvalidCredentialException(var_export($keys, true));
    }

    public function getPublicKeys(string $base_url)
    {
        $url = $base_url . self::PUBLIC_KEY_PATH;
        $client = new Client();
        $response = $client->get($url);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception($response->getReasonPhrase());
        }
        $contents = (string)$response->getBody();

        $json = json_decode($contents, true);

        $keys[] = $json["public_cert"]["cert"];
        if (!empty($json["public_certs"])) {
            foreach ($json["public_certs"] as $cert) {
                $keys[] = $cert["cert"];
            }
        }

        return array_unique($keys);
    }
}
