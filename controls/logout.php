<?php

use Symfony\Component\HttpFoundation\RedirectResponse;

$_SESSION['session_admin_id'] = null;
$_SESSION['session_user_auth'] = null;
$_SESSION['session_user_menu'] = null;
$_SESSION['session_user_tag'] = null;
$_SESSION['session_user_tagid'] = null;

//Warning: session_destroy(): Session object destruction failed
@session_destroy();

return RedirectResponse::create('/');
