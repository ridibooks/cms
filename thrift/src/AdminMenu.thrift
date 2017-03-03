include "Errors.thrift"

namespace php Ridibooks.CmsServer.Thrift.AdminMenu

/**
 * AdminMenu 엔티티
 */
struct AdminMenu {
    1: optional i32 id,
    2: optional string menu_title,
    3: optional string menu_url,
    4: optional i32 menu_deep,
    5: optional i32 menu_order,
    6: optional bool is_use,
    7: optional bool is_show,
    8: optional string reg_date,
    9: optional bool is_newtab,
}

struct AdminMenuAjax {
    1: optional i32 id,
    2: optional i32 menu_id,
    3: optional string ajax_url,
}

/**
 * AdminMenuCollection 엔티티
 */
typedef list<AdminMenu> AdminMenuCollection
typedef list<AdminMenuAjax> AdminMenuAjaxCollection

/**
 * AdminMenu 서비스
 */
service AdminMenuService {
    AdminMenuCollection getMenuList(
        1: bool isUse,
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    AdminMenuCollection getAllMenuList(
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    AdminMenuAjaxCollection getAllMenuAjax(
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    AdminMenuCollection getMenus(
        1: list<i32> menuIds,
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    list<i32> getAdminIdsByMenuId(
        1: i32 menuId,
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),
}
