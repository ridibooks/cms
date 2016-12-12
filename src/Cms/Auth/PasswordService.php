<?php

namespace Ridibooks\Platform\Cms\Auth;

class PasswordService
{
	const HASH_SALT = "http://php.net/manual/en/function.crypt.php";
	//hash 결과중 <sha256> 과 <sha256 + salt>를 구분하기위해 추가 / Version3
	const PASSWORD_VERSION_KEY = 'V3!';

	public static function needsRehash($hashed_password)
	{
		return password_needs_rehash($hashed_password, PASSWORD_DEFAULT);
	}

	public static function getPasswordAsHashed($plain_password)
	{
		return password_hash($plain_password, PASSWORD_DEFAULT);
	}

	private static function _v3Hash($plain_password)
	{
		return self::PASSWORD_VERSION_KEY . hash('sha256', $plain_password . self::HASH_SALT);
	}

	public static function isPasswordMatchToHashed($plain_password, $hashed_password)
	{
		if (password_verify($plain_password, $hashed_password)) {
			return true;
		}

		//v3
		if (self::hash_equals($hashed_password, self::_v3Hash($plain_password))) {
			return true;
		}
		//v2
		if (self::hash_equals($hashed_password, hash('sha256', $plain_password))) {
			return true;
		}
		//v1
		if (self::hash_equals($hashed_password, crypt($plain_password, $hashed_password))) {
			return true;
		}
		return false;
	}

	/**
	 * deprecated on php7
	 * @param $a
	 * @param $b
	 * @return bool
	 */
	private static function hash_equals($a, $b)
	{
		return $a == $b;
	}
}
