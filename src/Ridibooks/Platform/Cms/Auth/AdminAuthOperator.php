<?php
/**
 * Created by PhpStorm.
 * User: ridi
 * Date: 2016-04-01
 * Time: 오후 3:03
 */

namespace Ridibooks\Platform\Cms\Auth;


class AdminAuthOperator
{

	public static function isSetEditable($auth_list)
	{
		return in_array(AdminAuthConstants::EDIT_SET_BOOK, $auth_list);
	}

	/***
	 * @param $original_auth_list
	 * @param $removing_auth_list string|array
	 * @return mixed
	 */
	public static function removeFromAuths($original_auth_list, $removing_auth_list)
	{
		if (!is_array($removing_auth_list)) {
			$removing_auth_list = [$removing_auth_list];
		}

		foreach ($removing_auth_list as $removing_auth) {
			if (($key = array_search($removing_auth, $original_auth_list)) !== false) {
				unset($original_auth_list[$key]);
			}
		}
		
		return $original_auth_list;
	}
}
