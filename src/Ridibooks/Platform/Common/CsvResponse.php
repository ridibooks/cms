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
				$cell = str_replace('"', '""', $cell); //to deal with content's double quotes
				$cell = '"' . $cell . '"'; //boxing contents with double quotes to manage content's comma
				$new_row[] = $cell;
			}
			$new_data[] = implode(",", $new_row);
		}

		return implode("\r\n", $new_data);
	}

	private function setCSVHeader($filename)
	{
		$this->headers->set('Content-Type', 'application/csv; charset=euc-kr');
		$this->headers->set('Content-Disposition', "attachment; filename=\"$filename.csv\"");
		$this->headers->set('Cache-Control', 'max-age=0');
	}

	/**
	 * @param $file_name
	 */
	public static function setExcelHeader($file_name)
	{
		header("Content-Type: application/vnd.ms-excel;charset=utf-8");
		header("Content-Disposition: attachment; filename=\"$file_name.xls\"");
		header('Cache-Control: max-age=0');
	}
}
