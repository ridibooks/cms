/**datepicker 관련 util javascript module*/
define(["jquery", "comm/common", "bootstrap.datepicker", "bootstrap.datetimepicker"], function ($, common) {
  var module = {},
    defaultOption = {
      format: 'yyyymmdd',
      autoclose: true
    };

  function setDatetimepicker(element, options) {
    element.datetimepicker(options);
    if (element.val()) {
      element.data('DateTimePicker').date(element.val());
    }
  }

  /**
   * 기본 datepicker
   */
  module.setDatepicker = function () {
    for (var i = 0; i < arguments.length; i++) {
      setDatetimepicker(arguments[i], {format: "YYYYMMDD"});
    }
  };

  /**
   * 년-월-일 시:분:초 입력 가능한 datepicker
   */
  module.setTimepicker = function () {
    for (var i = 0; i < arguments.length; i++) {
      setDatetimepicker(arguments[i], {format: "YYYY-MM-DD HH:mm:ss", useCurrent: 'day'});
    }
  };

  /**
   * 계약 시작일과 종료일 설정한다.
   * @param startElement
   * @param endElement
   * @param datepicker_option
   */
  module.setPeriod = function (startElement, endElement, datepicker_option) {
    var option = datepicker_option || defaultOption;
    startElement.datepicker(option);
    endElement.datepicker(option);

    var startDate = startElement.data("datepicker");
    var endDate = endElement.data("datepicker");

    /**시작일이 종료일보다 늦거나, 종료일이 비어있을 경우 종료일 focus*/
    startElement.on("changeDate", function () {
      if (startDate.getDate() > endDate.getDate() || common.isEmpty(endElement.val())) {
        endElement.focus();
      }
    });

    /**시작일이 종료일보다 늦거나, 시작일이 비어있을 경우 시작일 focus*/
    endElement.on("changeDate", function () {
      if (startDate.getDate() > endDate.getDate() || common.isEmpty(startElement.val())) {
        startElement.focus();
      }
    });
  };

  /**
   * @param start_element
   * @param end_element
   * @param datetimepicker_option
   */
  module.setDatetimePeriod = function(start_element, end_element, datetimepicker_option) {
    var option = datetimepicker_option || {format: 'YYYY-MM-DD HH:mm:ss'};
    setDatetimepicker(start_element, option);
    setDatetimepicker(end_element, $.extend(option, {
      useCurrent: false //Important! See issue https://github.com/Eonasdan/bootstrap-datetimepicker/issues/1075
    }));

    start_element.on("dp.change", function (e) {
      if (start_element.data("DateTimePicker").date()) {
        end_element.data("DateTimePicker").minDate(e.date);
      }
    }).trigger($.Event('dp.change', {date: start_element.data('DateTimePicker').date()}));
    end_element.on("dp.change", function (e) {
      if (end_element.data("DateTimePicker").date()) {
        start_element.data("DateTimePicker").maxDate(e.date);
      }
    }).trigger($.Event('dp.change', {date: end_element.data('DateTimePicker').date()}));
  };

  return module;

});
