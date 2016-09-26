<?php
namespace Ridibooks\Platform\Cms;

use Ridibooks\Library\SentryHelper;

class CouchbaseSessionHandler implements \SessionHandlerInterface
{
	/**
	 * @var \CouchbaseBucket
	 */
	private $bucket;

	private $dsn;
	private $bucket_name;
	private $ttl_seconds;

	/**
	 * CouchbaseSessionHandler constructor.
	 * @param string $dsn
	 * @param string $bucket_name
	 * @param int $ttl_seconds
	 */
	public function __construct($dsn, $bucket_name, $ttl_seconds = 0)
	{
		$this->dsn = $dsn;
		$this->bucket_name = $bucket_name;
		$this->ttl_seconds = intval($ttl_seconds);
	}

	/**
	 * Initialize session
	 * @link http://php.net/manual/en/sessionhandlerinterface.open.php
	 * @param string $save_path The path where to store/retrieve the session.
	 * @param string $name The session name.
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function open($save_path, $name)
	{
		try {
			$cluster = new \CouchbaseCluster($this->dsn);
			$this->bucket = $cluster->openBucket($this->bucket_name);
		} catch (\CouchbaseException $e) {
			SentryHelper::triggerSentryException($e);
			return false;
		}

		return true;
	}

	/**
	 * Close the session
	 * @link http://php.net/manual/en/sessionhandlerinterface.close.php
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function close()
	{
		unset($this->bucket);
		unset($this->ttl_seconds);
		return true;
	}

	/**
	 * Read session data
	 * @link http://php.net/manual/en/sessionhandlerinterface.read.php
	 * @param string $session_id The session id to read data for.
	 * @return string <p>
	 * Returns an encoded string of the read data.
	 * If nothing was read, it must return an empty string.
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function read($session_id)
	{
		try {
			$session_data = $this->bucket->get($session_id);
			return $session_data->value;
		} catch (\CouchbaseException $e) {
			SentryHelper::triggerSentryException($e);
			return false;
		}
	}

	/**
	 * Write session data
	 * @link http://php.net/manual/en/sessionhandlerinterface.write.php
	 * @param string $session_id The session id.
	 * @param string $session_data <p>
	 * The encoded session data. This data is the
	 * result of the PHP internally encoding
	 * the $_SESSION superglobal to a serialized
	 * string and passing it as this parameter.
	 * Please note sessions use an alternative serialization method.
	 * </p>
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function write($session_id, $session_data)
	{
		$options = [];
		if ($this->ttl_seconds) {
			$options['expiry'] = $this->ttl_seconds;
		}
		try {
			$this->bucket->upsert($session_id, $session_data, $options);
		} catch (\CouchbaseException $e) {
			SentryHelper::triggerSentryException($e);
			return false;
		}

		return true;
	}

	/**
	 * Destroy a session
	 * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
	 * @param string $session_id The session ID being destroyed.
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function destroy($session_id)
	{
		try {
			$this->bucket->remove([$session_id]);
		} catch (\CouchbaseException $e) {
			SentryHelper::triggerSentryException($e);
			return false;
		}

		return true;
	}

	/**
	 * Cleanup old sessions
	 * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
	 * @param int $maxlifetime <p>
	 * Sessions that have not updated for
	 * the last maxlifetime seconds will be removed.
	 * </p>
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function gc($maxlifetime)
	{
		// 구조상 GC가 의미 없음
		return true;
	}
}
