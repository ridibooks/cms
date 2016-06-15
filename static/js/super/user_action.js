/**어드민 유저 상세 관리*/
/**
 * @deprecated html form 을 사용하도록
 */
define(["jquery", "comm/common", "comm/validate"], function ($, common, validate) {
  var module = {};

  /**
   * 유저 정보 관리 Dto function
   * @constructor
   */
  function AdminUserDto() {
    this.id = $("#js_user_id"); //id
    this.name = $("#name"); //이름
    this.team = $("#team"); //팀
    this.is_use = $("#is_use"); //사용여부
    this.passwd = $("#passwd"); //비밀번호
    this.last_id = $("#last_user_id"); //마지막으로 등록된 ID
    this.command = null; //command
  }

  /**
   * 유저 권한 관리 Dto function
   * @constructor
   */
  function UserAuthDto() {
    this.id = $("#js_user_id"); //id
    this.tag_id_array = $("#js_tag_id_array"); //유저 태그
    this.menu_id_array = $("#js_menu_id_array"); //유저 메뉴
    this.partner_cp_id_array = $("#js_partner_cp_id_array"); //제휴 CP ID
    this.operator_cp_id_array = $("#js_operator_cp_id_array"); //제휴 CP ID
    this.production_cp_id_array = $("#js_production_cp_id_array"); //제작 CP ID
    this.command = null; //command
  }

  function _doAction(dto, onSuccess) {
    $.ajax({
      url: "/super/user_action.ajax",
      type: 'post',
      data: common.toJson(dto),
      dataType: 'json',
      error: function (xhr) {
        console.log(xhr.responseText);
      },
      success: function (returnData) {
        alert(returnData.msg);
        if (onSuccess) {
          onSuccess(returnData.msg);
        }
      },
      failed: function (xhr) {
        alert(xhr.responseText);
      }
    });
  }


  /**
   * 유저 정보 등록/수정 한다.
   */
  module.insertUserInfo = function () {
    try {
      var userDto = new AdminUserDto();

      if (common.isEmpty(userDto.last_id.val())) { //신규 등록일 경우
        userDto.command = "insertUserInfo";
        validate.checkEmpty(userDto.id, userDto.passwd, userDto.name, userDto.team);
      } else { //수정일 경우
        userDto.command = "updateUserInfo";
        validate.checkEmpty(userDto.id, userDto.name, userDto.team);
      }

      _doAction(userDto);

    } catch (e) {
      console.log(e);
    }

  };

  /**
   * 유저 권한 정보 등록/수정 한다.
   */
  module.insertUserAuth = function () {
    var userAuthDto = new UserAuthDto();
    userAuthDto.command = "insertUserAuth";

    _doAction(userAuthDto);
  };

  module.deleteAdmin = function (onSuccess) {
    var userDto = new AdminUserDto();
    userDto.command = "delete";

    _doAction(userDto, onSuccess);
  };

  return module;
});
