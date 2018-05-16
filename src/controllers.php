<?php
declare(strict_types=1);

use Ridibooks\Cms\Controller;
use Ridibooks\Cms\Service\Auth\AuthMiddleware;
use Silex\ControllerCollection;

// Login service
/** @var ControllerCollection $auth_controller */
$auth_controller = $app['controllers_factory'];

$auth = new Controller\AuthController();
$auth_controller->get('/login', [$auth, 'loginPage'])->bind('login');
$auth_controller->get('/logout', [$auth, 'logout'])->bind('logout');

// Possible values for ${auth_type} are 'password', and 'test'
$auth_controller->get('/auth/{auth_type}/authorize', [$auth, 'authorize'])->bind('default_authorize');

// Possible value for ${provider} is only 'azure' now
$auth_controller->get('/auth/oauth2/{provider}/authorize', [$auth, 'authorizeWithOAuth2'])->bind('oauth2_authorize');

$auth_controller->get('/auth/oauth2/callback', [$auth, 'callbackFromOAuth2'])->bind('oauth2_callback');

$app->mount('/', $auth_controller);

// Common service
/** @var ControllerCollection $common_controller */
$common_controller = $app['controllers_factory'];
$common_controller->before(AuthMiddleware::authRequired());

$common = new Controller\CommonController();
$common_controller->get('/', [$common, 'index']);
$common_controller->get('/welcome', [$common, 'getWelcomePage'])->bind('home');
$common_controller->get('/me', [$common, 'getMyInfo'])->bind('me');
$common_controller->post('/me', [$common, 'updateMyInfo']);
$common_controller->get('/comm/user_list.ajax', [$common, 'userList']); // TODO: Remove this

$app->mount('/', $common_controller);
