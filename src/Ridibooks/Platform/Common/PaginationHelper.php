<?php
namespace Ridibooks\Platform\Common;

use Ridibooks\Library\UrlHelper;
use Symfony\Component\HttpFoundation\Request;

class PaginationHelper
{
	/**
	 * @param Request $request
	 * @param int $total_count
	 * @param int $cur_page
	 * @param int $rows_per_page 페이지당 항목수
	 * @param int $button_count 하단에 표시할 버튼 수
	 * @return array
	 */
	public static function getArgs($request, $total_count, $cur_page, $rows_per_page, $button_count = 10)
	{
		$cur_page = max(1, $cur_page);
		$total_page = intval(ceil((double) $total_count / $rows_per_page));
		$cur_page = min($cur_page, $total_page);
		$show_next_last = $cur_page < $total_page;

		$start_page = (ceil((double)$cur_page / $button_count) - 1) * $button_count + 1;
		$start_page = $start_page < 1 ? 1 : $start_page;

		$end_page = $start_page + $button_count - 1;
		$end_page = $end_page < 1 ? 1 : $end_page;
		$end_page = $end_page > $total_page ? $total_page : $end_page;

		$link = parse_url($request->server->get('REQUEST_URI'), PHP_URL_PATH);
		$query_string = UrlHelper::buildQuery(array_filter($request->query->all()), ['page' => null]);
		if (empty($query_string)) {
			$link .= '?';
		} else {
			$link .= $query_string . '&';
		}
		$next_page = $end_page == $total_page ? $end_page : $end_page + 1;
		$prev_page = $start_page == 1 ? 1 : $start_page - 1;

		$limit_start = ($cur_page - 1) * $rows_per_page;

		if ($limit_start < 0) {
			$limit_start = 0;
		}

		return [
			'cur_page' => $cur_page,
			'total_count' => $total_count,
			'button_count' => $button_count,
			'total_page' => $total_page,
			'start_page' => $start_page,
			'end_page' => $end_page,
			'link' => $link,
			'query_string' => $query_string,
			'show_next_last' => $show_next_last,
			'next_page' => $next_page,
			'prev_page' => $prev_page,
			'start' => $limit_start,
			'limit' => $rows_per_page
		];
	}
}
