include "Errors.thrift"
include "AdminTag.thrift"
include "AdminMenu.thrift"

namespace php Ridibooks.Cms.Thrift.AdminUser

/**
 * AdminUser 엔티티
 */
struct AdminUser {
    1: optional string id,
    2: optional string name,
    3: optional string passwd,
    4: optional string team,
    5: optional bool is_use
    6: optional string reg_date
}

/**
 * AdminUserCollection 엔티티
 */
typedef list<AdminUser> AdminUserCollection

/**
 * AdminUser 서비스
 */
service AdminUserService {
    AdminUserCollection getAllAdminUserArray(
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    AdminUser getUser(
        1: string userId,
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    AdminTag.AdminTagCollection getAdminUserTag(
        1: string userId,
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    AdminMenu.AdminMenuCollection getAdminUserMenu(
        1: string userId,
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    list<i32> getAllMenuIds(
        1: string userId,
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    bool updateMyInfo(
        1: string userId,
        2: string name,
        3: string team,
        4: bool isUse,
        5: string passwd,
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    bool updatePassword(
        1: string userId,
        2: string plainPassword
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),
}
