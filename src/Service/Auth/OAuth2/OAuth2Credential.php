<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\OAuth2;

class OAuth2Credential
{
    public $access_token;
    public $refresh_token;

    public function __construct($access_token, $refresh_token)
    {
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
    }
}
