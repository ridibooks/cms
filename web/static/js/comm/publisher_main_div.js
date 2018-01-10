/**퍼블리셔 메인 분류*/
define(["jquery", "comm/common", "select2"], function ($, common) {
  "use strict";
  var module = {},
    data = {results: []};


  //CP 메인 분류 목록 가져온다.
  function _getPublisherMainDivArray(element, option) {
    if (data.results.length === 0) {
      $.ajax({
        url: "/admin/comm/publisher_main_div.ajax",
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
              var main_div_list = returnData.data;
              if (main_div_list.length !== 0) {
                for (var i in main_div_list) {
                  if (main_div_list.hasOwnProperty(i)) {
                    data.results.push({id: main_div_list[i], text: main_div_list[i]});
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

  module.setSelect2 = function (element) {
    var option = {
      multiple: true,
      tokenSeparators: [",", " ", "\t", "\n", "\r\n"],
      placeholder: "CP 장르를 입력하세요",
      data: data
    };

    _getPublisherMainDivArray(element, option);
  };

  return module;
});
