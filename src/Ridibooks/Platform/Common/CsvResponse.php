<?php

namespace Ridibooks\Platform\Common;

use Symfony\Component\HttpFoundation\Response;

class CsvResponse extends Response
{
	/**
	 * Constructor.
	 *
	 * @param mixed $data The response data
	 * @param string $filename
	 * @param integer $status The response status code
	 * @param array $headers An array of response headers
	 */
	public function __construct($data = null, $filename = null, $status = 200, $headers = [])
	{
		parent::__construct('', $status, $headers);

		if (null === $data) {
			$data = new \ArrayObject();
		}

		if (null === $filename) {
			$filename = "data_" . date('Ymd');
		}

		$this->setCSVHeader($filename);
		$this->setData($data);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function create($data = null, $filename = null, $status = 200, $headers = [])
	{
		return new static($data, $filename, $status, $headers);
	}

	public function setData($data = [])
	{
		foreach ($data as $k => $v) {
			if (is_object($v)) {
				$v = get_object_vars($v);
			} elseif (is_scalar($v)) {
				$v = [$v];
			}
			foreach ($v as $k2 => $v2) {
				$v[$k2] = iconv('utf-8', 'euc-kr', $v2);
			}
			$data[$k] = $v;
		}

		$this->setContent($this->changeQuotesAndNewLineSpliter($data));
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
	protected function setCSVHeader($filename)
	{
		$this->headers->set('Content-Type', 'application/csv; charset=euc-kr');
		$this->headers->set('Content-Disposition', "attachment; filename=" . $filename . ".csv");
	}
}
