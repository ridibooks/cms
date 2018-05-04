<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests\Service\Auth\Authenticator;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Auth\LoginService;
use Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator;
use Ridibooks\Cms\Service\Auth\Exception\InvalidCredentialException;
use Ridibooks\Cms\Service\Auth\Exception\NoCredentialException;
use Ridibooks\Cms\Service\Auth\OAuth2\Exception\InvalidStateException;
use Ridibooks\Cms\Tests\MockOAuth2Client;
use Ridibooks\Cms\Tests\MockSession;
use Symfony\Component\HttpFoundation\Request;

class OAuth2AuthenticatorTest extends TestCase
{
    /** @var MockSession $session */
    private $session;

    /** @var OAuth2Authenticator $authenticator */
    private $authenticator;

    public function setUp()
    {
        $session = new MockSession([
            OAuth2Authenticator::KEY_PROVIDER => 'some_provider'
        ]);

        $authenticator = new OAuth2Authenticator($session, [
            'some_provider' => new MockOAuth2Client(true)
        ]);

        $this->session = $session;
        $this->authenticator = $authenticator;
    }

    public function testGetAuthorizationUrl()
    {
        $actual = $this->authenticator->getAuthorizationUrl('some_scope');

        // New state is stored to session after getAuthorizationUrl called
        $random_state = $this->session->get(OAuth2Authenticator::KEY_STATE);

        // an authorization url created by MockOauth2Client
        $expected = 'authorization url with scope \'some_scope\', and state \'' . $random_state . '\'';

        $this->assertEquals($expected, $actual);
    }

    public function testCreateCredentialWithAuthorizationCode()
    {
        $code = 'test_code';
        $state = 'random_state';
        $request = Request::create('/oauth2/callback?code=' . $code . '&state=' . $state);

        // The state stored in session should be matched with a state param passed by callback url
        $this->session->set(OAuth2Authenticator::KEY_STATE, $state);

        $actual_access_token = $this->authenticator->createCredential($request);
        // an access token created by MockOauth2Client
        $expected_access_token = 'access_token from code \'' . $code . '\'';
        $this->assertEquals($expected_access_token, $actual_access_token);

        $actual_refresh_token = $this->session->get(OAuth2Authenticator::KEY_REFRESH_TOKEN);
        // a refresh token created by MockOauth2Client
        $expected_refresh_token = 'refresh_token from code \'' . $code . '\'';
        $this->assertEquals($expected_refresh_token, $actual_refresh_token);
    }

    public function testCreateCredentialWithAuthorizationCodeWithWrongState()
    {
        $code = 'test_code';
        $state = 'random_state';
        $request = Request::create('/oauth2/callback?code=' . $code . '&state=' . $state);

        // Set a state not matched with previous one.
        $this->session->set(OAuth2Authenticator::KEY_STATE, 'wrong_state');

        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('state is not matched');
        $this->authenticator->createCredential($request);
    }

    public function testCreateCredentialWithRefreshToken()
    {
        $refresh_token = 'some_refresh_token';
        $request = Request::create('/some/resource');

        $this->session->set(OAuth2Authenticator::KEY_ACCESS_TOKEN, null);
        $this->session->set(OAuth2Authenticator::KEY_REFRESH_TOKEN, $refresh_token);

        $actual_access_token = $this->authenticator->createCredential($request);
        // an access token created by MockOauth2Client
        $expected_access_token = 'access_token from refresh_token \'' . $refresh_token . '\'';
        $this->assertEquals($expected_access_token, $actual_access_token);

        $actual_refresh_token = $this->session->get(OAuth2Authenticator::KEY_REFRESH_TOKEN);
        // an refresh token created by MockOauth2Client
        $expected_refresh_token = 'refresh_token from refresh_token \'' . $refresh_token . '\'';
        $this->assertEquals($expected_refresh_token, $actual_refresh_token);
    }

    public function testCreateCredentialWithNoTokens()
    {
        $request = Request::create('/some/resource');

        $this->session->set(OAuth2Authenticator::KEY_ACCESS_TOKEN, null);
        $this->session->set(OAuth2Authenticator::KEY_REFRESH_TOKEN, null);

        $this->expectException(NoCredentialException::class);
        $this->expectExceptionMessage('no token exists');
        $this->authenticator->createCredential($request);
    }

    public function testValidateCredentialSuccess()
    {
        $this->authenticator->validateCredential('some_good_token');

        $this->assertTrue(true); // Assert that an exception is not thrown
    }

    public function testValidateCredentialFailed()
    {
        $this->authenticator = new OAuth2Authenticator($this->session, [
            'some_provider' => new MockOAuth2Client(false)
        ]);

        $this->expectException(InvalidCredentialException::class);
        $this->authenticator->validateCredential('some_wrong_token');
    }

    public function testGetUserId()
    {
        $access_token = 'some_access_token';
        // an access token created by MockOauth2Client
        $expected = 'resource owner from access_token \'' . $access_token . '\'';
        $actual = $this->authenticator->getUserId($access_token);

        $this->assertEquals($expected, $actual);

        $backward_compatibility = $this->session->get(LoginService::ADMIN_ID_COOKIE_NAME); // TODO: Remove this
        $this->assertEquals($backward_compatibility, $actual);
    }

    public function testSignInWith()
    {
        $code = 'test_code';
        $state = 'random_state';

        // The state stored in session should be matched with a state param passed by callback url
        $this->session->set(OAuth2Authenticator::KEY_STATE, $state);
        $request = Request::create('/oauth2/callback?code=' . $code . '&state=' . $state);

        $actual_user_id = $this->authenticator->signIn($request);

        $actual_access_token = $this->session->get(OAuth2Authenticator::KEY_ACCESS_TOKEN);
        // an access token created by MockOauth2Client
        $expected_access_token = 'access_token from code \'' . $code . '\'';
        $this->assertEquals($expected_access_token, $actual_access_token);

        $actual_refresh_token = $this->session->get(OAuth2Authenticator::KEY_REFRESH_TOKEN);
        // a refresh token created by MockOauth2Client
        $expected_refresh_token = 'refresh_token from code \'' . $code . '\'';
        $this->assertEquals($expected_refresh_token, $actual_refresh_token);

        $expected_user_id = 'resource owner from access_token \'' . $actual_access_token . '\'';
        // a resource owner created by MockOauth2Client
        $this->assertEquals($expected_user_id, $actual_user_id);
    }

    public function testSignOut()
    {
        $code = 'test_code';
        $state = 'random_state';

        // The state stored in session should be matched with a state param passed by callback url
        $this->session->set(OAuth2Authenticator::KEY_STATE, $state);
        $request = Request::create('/oauth2/callback?code=' . $code . '&state=' . $state);
        $this->authenticator->signIn($request);

        $this->authenticator->signOut();

        $this->assertNull($this->session->get(OAuth2Authenticator::KEY_AUTH_TYPE));
        $this->assertNull($this->session->get(OAuth2Authenticator::KEY_PROVIDER));
        $this->assertNull($this->session->get(OAuth2Authenticator::KEY_STATE));
        $this->assertNull($this->session->get(OAuth2Authenticator::KEY_RETURN_URL));
        $this->assertNull($this->session->get(OAuth2Authenticator::KEY_ACCESS_TOKEN));
        $this->assertNull($this->session->get(OAuth2Authenticator::KEY_REFRESH_TOKEN));
    }

    public function testGetProvider()
    {
        $this->session->set(OAuth2Authenticator::KEY_PROVIDER, 'some_provider1');
        $actual = $this->authenticator->getProvider();

        $this->assertEquals('some_provider1', $actual);
    }

    public function testSetProvider()
    {
        $this->authenticator->setProvider('some_provider2');
        $actual = $this->session->get(OAuth2Authenticator::KEY_PROVIDER);

        $this->assertEquals('some_provider2', $actual);
    }

    public function testGetReturnUrl()
    {
        $this->session->set(OAuth2Authenticator::KEY_RETURN_URL, '/some/return/1');
        $actual = $this->authenticator->getReturnUrl();

        $this->assertEquals('/some/return/1', $actual);
    }

    public function testSetReturnUrl()
    {
        $this->authenticator->setReturnUrl('/some/return/2');
        $actual = $this->session->get(OAuth2Authenticator::KEY_RETURN_URL);

        $this->assertEquals('/some/return/2', $actual);
    }
}
