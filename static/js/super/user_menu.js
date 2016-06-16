/**유저 메뉴 관리*/
define(['jquery', 'comm/common', 'select2'], function ($, common) {
  var module = {},
    data = {results: []};

  function _getUserMenuArray(element, option) {
    if (data.results.length == 0) {
      $.ajax({
        url: '/super/menu_action.ajax',
        data: {command: 'showMenuArray'},
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
              var menu_list = returnData.data;
              if (menu_list.length != 0) {
                for (var i in menu_list) {
                  if(menu_list.hasOwnProperty(i)){
                    var menu_url_array = menu_list[i].menu_url.split('#');
                    var menu_title = menu_list[i].menu_title + (menu_url_array[1] ? '#' + menu_url_array[1] : '');
                    data.results.push({id: menu_list[i].id, text: menu_title});
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
      placeholder: '권한 메뉴를 입력하세요.',
      data: data
    };

    _getUserMenuArray(element, option);
  };

  return module;
});
