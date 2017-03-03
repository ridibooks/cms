include "Errors.thrift"
include "AdminMenu.thrift"

namespace php Ridibooks.Cms.Thrift.AdminTag

/**
 * AdminTag 엔티티
 */
struct AdminTag {
    1: optional i32 id,
    2: optional string name,
    3: optional bool is_use,
    4: optional string creator,
    5: optional string reg_date,
}

/**
 * AdminTagCollection 엔티티
 */
typedef list<AdminTag> AdminTagCollection

service AdminTagService {
    list<string> getAdminIdsFromTags(
        1: list<i32> tag_ids,
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    AdminMenu.AdminMenuCollection getAdminTagMenus(
        1: i32 tag_id
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),

    list<string> getMappedAdminMenuHashes(
    ) throws (
        1: Errors.UserException userException,
        2: Errors.SystemException systemException
    ),
}
