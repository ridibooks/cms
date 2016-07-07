<?php

namespace Ridibooks\Platform\Common;

use Symfony\Component\HttpFoundation\Response;

class CsvResponse extends Response
{
	protected $data;
	protected $callback;
	protected $filename;

	/**
	 * Constructor.
	 *
	 * @param mixed $data The response data
	 * @param string $filename
	 * @param integer $status The response status code
	 * @param array $headers An array of response headers
	 */
	public function __construct($data = null, $filename = null, $status = 200, $headers = array())
	{
		parent::__construct('', $status, $headers);

		if (null === $data) {
			$data = new \ArrayObject();
		}
		if (null === $filename) {
			$this->filename = "data_" . date('Ymd');
		} else {
			$this->filename = $filename;
		}

		$this->setData($data);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function create($data = null, $filename = null, $status = 200, $headers = array())
	{
		return new static($data, $filename, $status, $headers);
	}

	/**
	 * Sets the data to be sent as json.
	 *
	 * @param mixed $data
	 *
	 * @return CsvResponse
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setData($data = array())
	{
		foreach ($data as $k => $v) {
			if (is_object($v)) {
				$v = get_object_vars($v);
			} elseif (is_scalar($v)) {
				$v = array($v);
			}
			foreach ($v as $k2 => $v2) {
				$v[$k2] = iconv('utf-8', 'euc-kr', $v2);
			}
			$data[$k] = $v;
		}

		$this->data = $this->changeQuotesAndNewLineSpliter($data);

		return $this->setCSVHeader();
	}

	public function changeQuotesAndNewLineSpliter($input)
	{
		$ret = '';
		foreach ($input as $row) {
			foreach ($row as $cell) {
				$cell = str_replace('"', '\"', $cell); //to deal with content's double quotes
				$cell = '"' . $cell . '"'; //boxing contents with double quotes to manage content's comma
				$cell .= ',';
				$ret .= $cell;
			}
			$ret = rtrim($ret, ',');
			$ret .= "\r\n";
		}
		$ret = rtrim($ret); //remove trail "\r" "\n"
		return $ret;
	}

	/**
	 * Updates the content and headers according to the json data and callback.
	 *
	 * @return CsvResponse
	 */
	protected function setCSVHeader()
	{
		$this->headers->set('Content-Type', 'application/csv; charset=euc-kr');
		$this->headers->set('Content-Disposition', "attachment; filename=" . $this->filename . ".csv");

		return $this->setContent($this->data);
	}
}
