/**
 * bootstrap3 용 이미지 버튼 util class
 */
define(["jquery"], function ($) {
  var module = {};
  var classes = {
    label: {
      true: 'btn-primary',
      false: 'btn-default'
    },
    icon: {
      true: 'glyphicon-check',
      false: 'glyphicon-unchecked'
    }
  };

  function applyChange($elem) {
    var isChecked = $elem.prop('checked');
    var $label = $elem.closest('label,button');
    var $icon = $label.find('i');
    // 처음부터 설계가 잘못되어 button tag 대신 label tag가 사용되어 prop대신 attr을 사용해야 함
    $label.attr('disabled', $elem.prop('disabled') || $elem.prop('readonly'));
    $label.toggleClass(classes.label.true, isChecked);
    $label.toggleClass(classes.label.false, !isChecked);
    $label.toggleClass('active', isChecked);
    $icon.toggleClass(classes.icon.true, isChecked);
    $icon.toggleClass(classes.icon.false, !isChecked);
  }

  $.fn.extend({
    imageButton: function () {
      this.each(function () {
        var $label = $(this).closest('label,button');
        $label.find('input').before(
          $('<i/>', {
            'class': 'glyphicon'
          })
        );

        $(this).change(function () {
          $(this).closest('.btn-group').find('input').each(function () {
            applyChange($(this));
          });
        }).change();
      });
    }
  });


  /**
   * TODO util 분리 혹은 productDetail.file.action으로 이동
   * @param element
   * @constructor
   */
  function RemoveButton(element) {
    this.removeButton = $(element).find(".btn-nested-remove");
  }

  function _initRemoveButton(button) {
    var removeButton = $("<span>")
      .addClass("btn btn-danger btn-xs").addClass("btn-nested-remove")
      .append($("<i>").attr("class", "glyphicon glyphicon-remove"));
    $(button).append(removeButton);
  }

  function _addNestedRemoveButton(element, handler) {
    var button = new RemoveButton(element);
    button.removeButton.on("click", handler);
  }

  function _removeNestedRemoveButton(element) {
    var button = new RemoveButton(element);
    button.removeButton.remove();
  }

  module.toggleNestedRemoveButton = function (container, handler) {
    var buttons = container.find(".btn");

    container.toggleClass('removing');
    buttons.each(function () {
      if (container.hasClass('removing')) {
        _initRemoveButton(this);
        _addNestedRemoveButton(this, handler);
      } else {
        _removeNestedRemoveButton(this);
      }
    });
  };

  return module;
});
