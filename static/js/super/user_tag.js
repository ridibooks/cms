/**유저 tag 관리*/
define(['jquery', 'comm/common', 'select2'], function ($, common) {
  var module = {},
    data = {results: []};

  function _getUserTagArray(element, option) {
    if (data.results.length == 0) {
      $.ajax({
        url: '/super/tag_action.ajax',
        data: {command: 'showTagArray'},
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
              var tag_list = returnData.data;
              if (tag_list.length != 0) {
                for (var i in tag_list) {
                  if(tag_list.hasOwnProperty(i)){
                    data.results.push({id: tag_list[i].id, text: tag_list[i].name});
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
      tokenSeparators: [',', ' ', '\t', '\n', '\r\n'],
      placeholder: '권한 Tag 를 입력하세요.',
      data: data
    };

    _getUserTagArray(element, option);
  };

  return module;
});
