/**CP 목록*/
define(["jquery", "comm/common", "select2"], function ($, common) {
  var data = { results: [] };

  /**
   * CP 전체 목록 가져온다.
   * @private
   */
  function _getCpArray(element, option) {
    if (data.results.length === 0) {
      $.ajax({
        url: "/admin/comm/cp_list.ajax",
        dataType: 'json',
        beforeSend: function () {
          common.setProgressBar(element);
        },
        error: function (xhr) {
          console.log(xhr.responseText);
        },
        success: function (returnData) {
          try {
            if (returnData.success) {
              var cp_list = returnData.data;
              if (cp_list.length !== 0) {
                for (var cp in cp_list) {
                  var pubId = cp_list[cp].id;
                  data.results.push({ id: pubId, text: cp_list[cp].name + ' (' + pubId + ')' });
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

  $.fn.extend({
    setCpUserSelectbox: function (option) { //해당 element에 CP 전체 목록 select2 적용한다.
      var _defaultOption = {
        placeholder: "CP를 선택하여 주세요.",
        data: data
      };
      _getCpArray($(this), $.extend(_defaultOption, option || {}));
      return this;
    },
    setCpUserMultipleSelect2: function (option) { //해당 element에 CP 전체 목록 select2 multiselect 적용한다.
      var _defaultOption = {
        multiple: true,
        tokenSeparators: [",", " ", "\t", "\n", "\r\n"],
        placeholder: "담당할 CP를 입력하세요.",
        data: data
      };
      _getCpArray($(this), $.extend(_defaultOption, option || {}));
      return this;
    }
  });

});
