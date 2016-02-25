<?php
require_once __DIR__ . '/../../../include/config.php';
require_once __DIR__ . '/router.php';

ini_set('max_execution_time', 300);
ini_set('max_input_time', 60);

mb_internal_encoding('UTF-8');
mb_regex_encoding("UTF-8");

session_set_cookie_params(60 * 60 * 24 * 30, '/', Config::$ADMIN_DOMAIN);
session_start();
