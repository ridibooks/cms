<?php

namespace Ridibooks\Platform\Common;

use Symfony\Component\HttpFoundation\Response;

class CsvResponse extends Response
{
	public function __construct($data = [], $filename = null, $status = 200, $headers = [])
	{
		parent::__construct('', $status, $headers);

		if (null === $filename) {
			$filename = "data_" . date('Ymd');
		}

		self::setExcelHeader($filename);
		$this->setData($data);
	}

	public static function create($data = [], $filename = null, $status = 200, $headers = [])
	{
		return new static($data, $filename, $status, $headers);
	}

	private function setData($data)
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

		$this->setContent($this->escapeQuotesAddNewLine($data));
	}

	private function escapeQuotesAddNewLine($data)
	{
		$new_data = [];
		foreach ($data as $row) {
			$new_row = [];
			foreach ($row as $cell) {
				$formatted_cell = '"' . str_replace('"', '""', $cell) . '"';
				if (ctype_digit($cell) && intval($cell) > 10**8) {
					// 1E+xx 형태로 표시되는 문제를 해결하기 위해 아주 큰 숫자는 string으로 취급
					$formatted_cell = '=' . $formatted_cell;
				}
				$new_row[] = $formatted_cell;
			}
			$new_data[] = implode(",", $new_row);
		}

		return implode("\r\n", $new_data);
	}

	/**
	 * @param $file_name
	 */
	public static function setExcelHeader($file_name)
	{
		header("Content-Type: application/csv; charset=euc-kr");
		header("Content-Disposition: attachment; filename=\"$file_name.csv\"");
		header('Cache-Control: max-age=0');
	}
}
