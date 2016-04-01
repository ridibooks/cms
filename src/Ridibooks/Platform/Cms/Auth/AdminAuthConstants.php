<?php
/**
 * Created by PhpStorm.
 * User: ridi
 * Date: 2016-04-01
 * Time: 오후 3:06
 */

namespace Ridibooks\Platform\Cms\Auth;


use Ridibooks\Platform\Common\Base\AdminBaseConst;

class AdminAuthConstants extends AdminBaseConst
{

	const EDIT_SET_BOOK = 'EDIT_세트도서';
	/*
	 * Valid PHP >= 7
	const LIST_FILE_EDITABLE = [
		'EDIT_FORMAT_VERSION',
		'EDIT_파일타입',
		'EDIT_PDF유무',
		'EDIT_나이제한',
		'EDIT_일본만화',
		'EDIT_만화뷰어',
		'EDIT_미리보기비율',
		'EDIT_첨부파일목록',
		'EDIT_표지이미지',
		'EDIT_첨부이미지',
		'EDIT_내부테스트파일_모든파일삭제',
		'EDIT_내부테스트파일_개별파일삭제',
		'EDIT_내부테스트파일_첨부이미지삭제',
		'EDIT_내부테스트파일_깨진파일삭제',
		'EDIT_고객이받을파일_모든파일삭제',
		'EDIT_고객이받을파일_개별파일삭제',
		'EDIT_고객이받을파일_첨부이미지삭제',
		'EDIT_고객이받을파일_깨진파일삭제',
		'EDIT_EPUB변환',
		'EDIT_파일변경불가'
	];
	*/
	const EDIT_EPUB_CONVERTER = 'EDIT_EPUB변환';
}
