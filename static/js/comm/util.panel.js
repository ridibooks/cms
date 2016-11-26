define(["jquery", "store"], function($, store) {
  "use strict";
  var module = {};

  function Panel(element) {
    this.element = $(element);
    this.collapse = $(element).find(".panel-collapse");
    this.store_collapse_key = "collapse#" + this.collapse.prop("id");
  }

  Panel.prototype = {
    /**
     * store에서 collapse여부를 읽어와 collapse 상태를 초기화 해준다.
     */
    initCollapse: function() {
      // 불필요한 collapse()호출을 피하기 위해 기존값과 비교후 다를 경우에만 collapse()를 호출해준다.
      var aria_expanded = this.collapse.attr("aria-expanded") === "true";
      var store_collapse = store.get(this.store_collapse_key) === "true";
      if (aria_expanded !== store_collapse) {
        this.collapse.collapse(store_collapse ? "hide" : "show");
      }
    },
    setHandler: function() {
      var _this = this;

      var handler = function() {
        var aria_expanded = _this.collapse.attr("aria-expanded") === "true" ? "true" : "false";
        store.set(_this.store_collapse_key, aria_expanded);
      };
      _this.element.on("show.bs.collapse", handler);
      _this.element.on("hide.bs.collapse", handler);
    }
  };

  module.init = function(element) {
    var panel = new Panel(element);
    panel.initCollapse();
    panel.setHandler();
  };

  return module;
});