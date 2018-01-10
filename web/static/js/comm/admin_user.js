/**리디 담당자 목록*/
define(["jquery", "comm/common", "select2"], function ($, common) {
  "use strict";
  var module = {},
    data = {results: []};

  /**
   *어드민 유저 목록 가져온다.
   * @private
   */
  function _getAdminUserArray(element, option) {
    if (data.results.length === 0) {
      $.ajax({
        url: "/comm/user_list.ajax",
        dataType: 'json',
        cache: false,
        beforeSend: function () {
          common.setProgressBar(element);
        },
        error: function (xhr) {
          console.log(xhr);
        },
        success: function (returnData) {
          try {
            if (returnData.success) {
              var admin_list = returnData.data;
              if (admin_list.length !== 0) {
                for (var i in admin_list) {
                  if (admin_list.hasOwnProperty(i)) {
                    data.results.push({id: admin_list[i].id, text: admin_list[i].name});
                  }
                }
              }

              common.removeProgressBar(element);
              element.select2(option);
            }
          } catch (err) {
            console.log(err);
          }
        },
        failed: function (xhr) {
          alert(xhr.responseText);
        }
      });
    } else {
      common.removeProgressBar(element);
      element.select2(option);
    }
  }

  /**
   * select2 적용
   * @param element
   */
  module.setSelect2 = function (element) {
    var option = {
      multiple: true,
      tokenSeparators: [",", " ", "\t", "\n", "\r\n"],
      placeholder: "리디 담당자를 입력하세요.",
      data: data
    };

    _getAdminUserArray(element, option);
  };

  return module;
});
