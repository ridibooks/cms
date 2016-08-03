<?php

class ConfigDefault
{
	const ENV_REAL = 'REAL';
	const ENV_STAGING = 'STAGING';
	const ENV_DEVELOPMENT = 'DEV';

	public static $ENV_NAME;

	/** @deprecated */
	public static $DB_HOST = "127.0.0.1";
	public static $DB_USER = "service";
	public static $DB_PASSWD = "init100830";
	public static $DB_NAME = "bom";
	
	public static $DB_PARAMS = [];

	public static $DB_SLAVE_SERVERS_FOR_CHECK_REPLICATION = []; // ref. ReplicationStatusWatcher::__construct()

	public static $DOMAIN = "ridibooks.com";

	public static $STATIC_URL;
	public static $MISC_URL;
	public static $ACTIVE_URL;
	public static $ADMIN_DOMAIN;
	public static $DOWNLOAD_URL = 'http://download.ridibooks.com';

	public static $WEB_VIEWER_DOMAIN = 'view.ridibooks.com';

	public static $API_SERVER_URL = "https://api.ridibooks.com";
	public static $API_SERVER_URL_PROXY = "https://ridibooks.com/noti";
	public static $API_SERVER_URL_ADMIN = "http://192.168.0.31";

	public static $PAPERSHOP_API_SERVER_URL = "http://api.paper.ridibooks.com";

	public static $HTTP_HOST_LINK = '';
	public static $SSL_HOST_LINK = '';

	public static $ENABLE_SSL = true;

	public static $SESSION_USE_MEMCACHE = false;
	public static $SESSION_HOST = "";

	public static $COUCHBASE_ENABLE = false;
	public static $COUCHBASE_SERVER_HOSTS = [];

	public static $SEARCH_SERVER_HOSTS_FOR_ELASTICSEARCH = [];

	public static $ENABLE_RIDI_EXCEPTION_HANDLER = true;
	public static $ENABLE_DB_LOGGER = false;

	public static $ENABLE_SENTRY = false;
	public static $SENTRY_KEY = 'https://2925fb607bc54e2a9dfe940248d271ff:61d7c17677c54ff786e3dafeb6de3b9c@app.getsentry.com/4504';
	public static $SENTRY_KEY_JS = null;

	public static $UNDER_DEV = false;
	public static $ENABLE_DEV_ERROR_HANDLER = true;

	public static $GOOGLE_API_KEY_FOR_GCM = "AIzaSyDEaTAhv3DoZViw3r7_6OyWXz2hA7EkWZ8";

	public static $MAILGUN_API_KEY = "key-6-flgc5no2ewa-ruleal4h6sorfb0ab7";
	public static $MANDRILL_API_KEY = "TaR12aqgRCQKlsFiK_3_bg";

	public static $SMS_API_KEY = "NjgxLTEzNzc2NTM0NTcwNzUtMGY4OGRlN2ItNzgzOC00ZTcxLTg4ZGUtN2I3ODM4NGU3MTVl";
	public static $SMS_API_ID = "ridicorp";
	public static $SMS_API_VER = "1";

	public static $XPAY_IS_TEST_MODE = false;

	public static $ORM_IS_DEV_MODE = false;
	public static $LENGTH_LIMIT_ROWS_EXAMINED = -1;

	public static $REDIS_HOST = "192.168.0.31:6379";

	public static function init()
	{
		if (!isset(static::$DB_PARAMS['default'])) {
			static::$DB_PARAMS['default'] = [
				'host' => static::$DB_HOST,
				'user' => static::$DB_USER,
				'password' => static::$DB_PASSWD,
				'dbname' => static::$DB_NAME,
				'driver' => 'pdo_mysql',
				//'driverClass' => '\Ridibooks\Library\DB\CustomDriver',
				'wrapperClass' => '\Ridibooks\Library\DB\CustomPdoConnection',
				'charset' => 'utf8',
				'driverOptions' => [1002 => 'SET NAMES utf8']
			];
		}

		if (static::$ENABLE_SSL && (
				(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
				||
				(isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && strtolower($_SERVER["HTTP_X_FORWARDED_PROTO"]) == "https")
			)
		) {
			$protocol = 'https:';
		} else {
			$protocol = 'http:';
		}

		if (!isset(static::$STATIC_URL)) {
			static::$STATIC_URL = $protocol . '//static.' . static::$DOMAIN;
		}

		if (!isset(static::$MISC_URL)) {
			static::$MISC_URL = '//misc.' . static::$DOMAIN;
		}

		if (!isset(static::$ACTIVE_URL)) {
			static::$ACTIVE_URL = '//active.' . static::$DOMAIN;
		}

		if (!isset(static::$ADMIN_DOMAIN)) {
			static::$ADMIN_DOMAIN = 'admin.' . static::$DOMAIN;
		}

		if (isset($_SERVER['HTTP_HOST'])) {
			static::$HTTP_HOST_LINK = "http://" . $_SERVER['HTTP_HOST'];
			static::$SSL_HOST_LINK = (static::$ENABLE_SSL ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
		}
	}

	public static function getConnectionParams($name)
	{
		if (isset(static::$DB_PARAMS[$name])) {
			return static::$DB_PARAMS[$name];
		}

		return static::$DB_PARAMS['default'];
	}
}

if (is_file(__DIR__ . '/config.real.php')) {
	require_once __DIR__ . '/config.real.php';
} elseif (is_file(__DIR__ . '/config.local.php')) {
	require_once __DIR__ . '/config.local.php';
} else {
	class Config extends ConfigDefault
	{
	}
}


class Env
{
	public static $DATA_ROOT; //Root of book_data, book_cover, active
	public static $BOOK_DATA_BASE_DIR;
	public static $CP_ROOT;
	public static $CACHE_BASE_DIR;

	public static function init()
	{
		if (is_null(Env::$DATA_ROOT)) {
			Env::$DATA_ROOT = __DIR__ . "/../../../";
		}
		if (is_null(Env::$BOOK_DATA_BASE_DIR)) {
			Env::$BOOK_DATA_BASE_DIR = Env::$DATA_ROOT . '/book_data';
		}
		if (is_null(Env::$CACHE_BASE_DIR)) {
			Env::$CACHE_BASE_DIR = __DIR__ . '/../../../book_cover_cache';
		}

		if (is_null(Env::$CP_ROOT)) {
			Env::$CP_ROOT = '/mnt/ridifs/cp/';
		}
	}
}


Config::init();
Env::init();

// bootstrap
session_set_cookie_params(86400 * 15, '/', '.' . Config::$DOMAIN);
ini_set('session.gc_maxlifetime', 86400 * 15);
if (strlen(Config::$SESSION_USE_MEMCACHE)) {
	// memcache 3.0.9 이상부터 php7.0 지원
	ini_set('session.save_handler', 'memcache');
	ini_set('session.save_path', Config::$SESSION_HOST);
}


$autoloader = require_once __DIR__ . "/../vendor/autoload.php";
$autoloader->add('Ridibooks', __DIR__ . '/../../../admin/src');
$autoloader->add('Ridibooks', __DIR__ . '/../../admin/src');
$autoloader->add('Ridibooks', __DIR__ . '/../src');
