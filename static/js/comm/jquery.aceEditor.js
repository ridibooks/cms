/**
 * ACE editor를 사용하기 위한 jquery extension
 *   http://ace.c9.io
 * Created by younggyu.lee on 2015-06-01.
 */
define(['jquery', 'ace/ace'], function ($, aceEditor) {
  "use strict";

  /**
   * AceEditorElement constructor
   * @param element
   * @constructor
   */
  function AceEditorElement(element) {
    //ace editor가 생성될 div id
    this.id = "ace_editor_" + (element.attr("id") ? element.attr("id") : Math.floor((Math.random() * 1000) + 1));
    this.inner_text = element.val();
    //원본 element
    this.original_element = element;
    //생성된 div html
    this.div_html = "<div id='" + this.id + "'></div>";
  }

  AceEditorElement.prototype = {
    makeEditorDiv: function () { //기존의 element를 숨기고, ace editor가 적용될 div를 생성한다.
      this.original_element.after(this.div_html);
      this.original_element.hide();
    },
    /**
     * https://github.com/ajaxorg/ace/wiki/Configuring-Ace
     * data-max-lines = 최대 라인수
     * data-min-lines = 최소 라인수
     * data-wrap =
     */
    initAceEditor: function () {
      var _this = this;
      var _element = this.original_element;
      var max_lines = parseInt(_element.data('max-lines'), 10) || 15;
      var min_lines = parseInt(_element.data('min-lines'), 10) || 15;
      var wrap = _element.data('wrap') || 'free';

      var _defaultOption = {
        theme: "ace/theme/clouds",
        mode: "ace/mode/html",
        showPrintMargin: false,
        maxLines: max_lines,
        minLines: min_lines,
        wrap: wrap
      };

      var editor = aceEditor.edit(this.id);
      editor.$blockScrolling = Infinity;
      editor.setOptions(_defaultOption);
      editor.setValue(this.inner_text, -1);
      editor.getSession().on("change", function () { //editor 내의 text가 변할 때 마다 원본 element의 값도 같이 변한다.
        _this.original_element.val(editor.getSession().getValue());
      });

    },
    makeEditor: function () {
      this.makeEditorDiv();
      this.initAceEditor();
    }
  };

  $.fn.extend({
    setAceEditor: function () {
      this.each(function(){
        new AceEditorElement($(this)).makeEditor();
      });
    },
    getAceEditor: function () {
      var _ace = new AceEditorElement($(this));
      return aceEditor.edit(_ace.id);
    }
  });
});
