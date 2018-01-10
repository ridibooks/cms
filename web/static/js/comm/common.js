/**공통 javascript utils*/
define(["jquery"], function ($) {
  "use strict";
  var module = {};

  /**
   * 해당 값의 empty 여부 반환한다.
   * @param value
   * @returns {boolean}
   */
  module.isEmpty = function (value) {
    return value === null || value === undefined || value === [] || value === '' || $.trim(value) === '';
  };

  /**
   * 해당 element의 hidden 여부 반환한다.
   * @param element
   * @returns {boolean}
   */
  module.isHidden = function (element) {
    return element.is(":hidden");
  };

  /**
   * 해당 element의 disabled 여부 반환한다.
   * @param element
   * @returns {boolean}
   */
  module.isDisable = function (element) {
    return element.is(":disabled");
  };

  /**
   * line breaking tag를 br로 변환
   * @param string
   * @returns {*}
   */
  module.nl2br = function (string) {
    return string.replace(/(\r\n|\n\r|\r|\n)/g, "<br/>");
  };

  function setJqueryValues(object) {
    if (object.length == 1) {
      return object.val();
    } else {
      var values = [];
      object.each(function () {
        values.push($(this).val());
      });
      return values;
    }
  }

  /**
   * Object function (constructor)을 array로 변환시켜준다.
   * @param object
   * @return {{}}
   */
  module.toJson = function (object) {
    var jsonObject = {};

    for (var key in object) {
      if (object.hasOwnProperty(key) && typeof object[key] !== 'function') {

        if (object[key] instanceof $) {
          jsonObject[key] = setJqueryValues(object[key]);
        } else {
          jsonObject[key] = object[key];
        }
      }
    }

    return jsonObject;
  };

  /**
   * Object function (constructor)을 array로 변환시켜준다.
   * @param object
   * @returns {FormData}
   */
  module.toFormData = function (object) {
    var formData = new FormData();

    for (var key in object) {
      if (object.hasOwnProperty(key) && typeof object[key] !== 'function') {
        if (object[key] instanceof $) {
          if (object[key].length == 1) {
            if (object[key].val() !== undefined) {
              formData.append(key, object[key].val());
            }
          } else {
            object[key].each(function () {
              formData.append(key + "[]", $(this).val());
            });
          }
        } else if (object[key] instanceof FileList) {
          $(object[key]).each(function () {
            formData.append(key + "[]", this);
          });
        } else {
          if (object[key] !== undefined) {
            formData.append(key, object[key]);
          }
        }
      }
    }

    return formData;
  };

  /**
   * 파일 URL을 받아와 파일 이름만 추출한다.
   * @param src
   * @returns {*}
   */
  module.getFileName = function (src) {
    var fileName = src.replace(/\\/g, "/");
    fileName = fileName.substring(fileName.lastIndexOf("/") + 1);
    return fileName.replace(/[?#].+$/, "");
  };

  /**
   * 해당 element에 progress bar 추가한다.
   * @param element
   */
  module.setProgressBar = function (element) {
    var html = "<div class='progress' id='progress_" + element.attr("id") + "'><div class='progress-bar progress-bar-striped active' style='width:100%'>로딩중...</div></div>";
    element.after(html);
  };

  /**
   * 해당 테이블 tbody에 progress bar 추가한다.
   * @param element
   * @param colspan
   */
  module.setProgressTable = function (element, colspan) {
    var _html = "<td class='progress' id='progress_" + element.attr("id") + "' colspan=" + colspan + "><div class='progress-bar progress-bar-striped active' style='width:100%'>로딩중...</div></td>";
    element.html(_html);
  };

  /**
   * 해당 element에 추가되어있는 progress bar 제거한다.
   * @param element
   */
  module.removeProgressBar = function (element) {
    $("#progress_" + element.attr("id")).remove();
  };

  /**
   * Non-break space 제거한다.
   * @param value
   * @returns {XML|string|void|*}
   */
  module.removeNonbreakSpace = function (value) {
    return value.replace(/\xa0/gi, " ");
  };

  module.showAlertIfIe = function () {
    // Internet Explorer 브라우저로 접속시 경고 출력
    // IE 2~10 버전
    if (navigator.appVersion.indexOf("MSIE") !== -1) {
      $("#js_ie_alert").show();
    }

    // IE 11 버전
    var user_agent = navigator.userAgent;
    if (user_agent.indexOf("Trident") !== -1 && user_agent.indexOf("rv:11") !== -1) {
      $("#js_ie_alert").show();
    }
  };

  $.fn.extend({
    disable: function () {
      return this.each(function () {
        $(this).prop("disabled", true);
      });
    },
    enable: function () {
      return this.each(function () {
        if ($(this).hasAuth()) {
          $(this).prop("disabled", false);
        }
      });
    },
    hasAuth: function () { //권한이 있는지 검사한다.
      return $(this).data("auth");
    },
    isDisabled: function() {
      return $(this).is(":disabled");
    }
  });

  return module;
});
