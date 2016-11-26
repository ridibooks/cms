/**
 * bootstrap fileinput 관련 javascript util
 */
define(["jquery", "bootstrap.fileinput"], function ($) {
  "use strict";

  function FileInputDom(element, option) {
    var _defaultOption = {
      msgInvalidFileExtension: "잘못된 타입의 파일입니다 '{name}'. 오직 '{extensions}' 파일만 업로드 가능합니다.."
    };
    this.options = $.extend(_defaultOption, option || {});
    this.fileInput = element;
    return this;
  }

  FileInputDom.prototype = {
    getAcceptFileExtensionArray: function () {
      var acceptFileExtensionArray = [];

      if (this.options.allowedFileExtensions !== undefined) {
        this.options.allowedFileExtensions.forEach(function (allowedFileExt) {
          acceptFileExtensionArray.push("." + allowedFileExt);
        });
      }
      return acceptFileExtensionArray.join();
    },
    setFileInput: function () {
      this.fileInput.prop("accept", this.getAcceptFileExtensionArray());
      this.fileInput.fileinput(this.options);
    }
  };

  $.fn.extend({
    formFileUpload: function (option) {
      new FileInputDom(this, option).setFileInput();
      return this;
    }
  });

});
