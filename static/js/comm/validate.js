/**
 * javascript for validation
 */
define(["jquery", "comm/common", 'select2'], function ($, common) {
  "use strict";
  var module = {};

  /**
   * @param element
   * @constructor
   */
  function FeedBack(element) {
    this.id = element.attr("id");
    this.element = element;
    this.div = element.closest("div");
    this.condition = null;
  }

  FeedBack.prototype = {
    removeFeedbackSpan: function () {
      $("#feedback_span_" + this.id).remove();
    },
    addFeedbackSpan: function (type) {
      var feedback = {
        success: 'glyphicon glyphicon-ok',
        error: 'glyphicon glyphicon-remove'
      };

      this.removeFeedbackSpan();
      var html = '<span id="feedback_span_' + this.id + '" class="form-control-feedback ' + feedback[type] + '"></span>';
      this.element.after(html);
    },
    removeHelpBlock: function () {
      $("#feedback_span_help_" + this.id).remove();
    },
    addHelpBlock: function (text) { // 적합성 검사 실패 시 안내 문구 블럭 추가한다.
      this.removeHelpBlock();
      var html = "<span id='feedback_span_help_" + this.id + "' class='help-block'>" + text + "</span>";
      this.element.after(html);
    },
    initDiv: function () {
      this.div.addClass("has-feedback");
      this.div.removeClass("has-error").removeClass("has-success");
    },
    success: function () { //성공 feedback
      this.initDiv();
      this.div.addClass("has-success");
      this.addFeedbackSpan('success');
      this.removeHelpBlock();
    },
    error: function (text) { //실패 feedback
      this.initDiv();
      this.div.addClass("has-error");
      this.addFeedbackSpan('error');
      this.addHelpBlock(text);
    },
    isCheckable: function () { //해당 element가 hidden, disabled, undefined가 아닌지 여부 반환
      return (common.isHidden(this.element) === false) &&
        (common.isDisable(this.element) === false) &&
        ($.isEmptyObject(this.element) === false) &&
        (this.element.length !== 0);
    },
    setHandler: function (handler) {
      this.element.on("change keyup", handler);
    }
  };

  var checkEmptyHandler = function (feedBack) {
    return function () {
      if (feedBack.isCheckable() || isSelect2(feedBack.element)) {
        if (common.isEmpty(feedBack.element.val())) {
          feedBack.error("필수 입력 항목입니다.");
        } else {
          feedBack.success();
        }
      }
    };
  };

  /**
   * 해당 element에 select2가 적용되어 있는지 여부 반환
   * @param element
   * @returns {boolean}
   */
  var isSelect2 = function (element) {
    return $('#s2id_' + element.attr('id')).length > 0;
  };

  /**
   * 해당 element 값이 비어있는지 체크한다.
   */
  module.checkEmpty = function () {
    var validationResult = true;
    var first_error_element = null;

    for (var i = 0; i < arguments.length; i++) {
      var feedBack = new FeedBack(arguments[i]);

      if (feedBack.isCheckable() || isSelect2(feedBack.element)) {
        if (common.isEmpty(feedBack.element.val())) {
          feedBack.error("필수 입력 항목입니다.");

          if (first_error_element === null) {
            first_error_element = feedBack.element;
          }

          validationResult = false;
        } else {
          feedBack.success();
        }
      }

      feedBack.setHandler(checkEmptyHandler(feedBack));
    }

    if (!validationResult) {
      if (isSelect2(first_error_element)) {
        first_error_element.select2('focus');
      } else {
        first_error_element.focus();
      }
      throw "empty element exist";
    }
  };

  return module;
});
