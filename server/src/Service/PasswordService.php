<?php

namespace Ridibooks\Cms\Server\Service;

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

	/**
	 * @deprecated No longer used by internal code and not recommended.
	 */
	private static function v3Hash($plain_password)
	{
		return self::PASSWORD_VERSION_KEY . hash('sha256', $plain_password . self::HASH_SALT);
	}

	public static function isPasswordMatchToHashed($plain_password, $hashed_password)
	{
		if (password_verify($plain_password, $hashed_password)) {
			return true;
		}

		//v3 (deprecated)
		if ($hashed_password == self::v3Hash($plain_password)) {
			return true;
		}
		//v2 (deprecated)
		if ($hashed_password == hash('sha256', $plain_password)) {
			return true;
		}
		//v1 (deprecated)
		if ($hashed_password == crypt($plain_password, $hashed_password)) {
			return true;
		}
		return false;
	}
}
