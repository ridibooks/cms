/**
 * Created by ridinfra on 2015-09-24.
 */
define(['jquery', 'bootstrap.datatables'], function($) {
  "use strict";
  var module = {};

  module.initDatatable = function(element, options) {
    $(element).DataTable(options);

    if ($(element).data('selected-row-class')) {
      $(element).on('click', 'tbody>tr', function() {
        $(this).toggleClass($(element).data('selected-row-class'));
      });
    }

    var api = getCustomApi(element);
    api.refreshColumnFilter();
  };

  module.getCustomApi = function(element) {
    return getCustomApi(element);
  };

  function getCustomApi(element) {
    var api = $(element).dataTable().api();
    api.getSelectedRows = function() {
      return api.rows('.' + $(element).data('selected-row-class')).data();
    };
    api.refreshColumnFilter = function() {
      api.columns().indexes().flatten().each(function (i) {
        var column = api.column(i);

        if ($(column.header()).data('filter') !== undefined) {
          var select = $('<select>')
            .addClass('form-control')
            .append($('<option>').val('').html($(column.header()).attr('title'))) // .val('')가 없으면 전체표시가 안된다.
            .css('width', '100%')// select이 가로로 길어져 overflow가 되는 상황 방지
            .appendTo($(column.header()).empty())
            .on('change', function () {
              column.search($(this).val(), false, false).draw();
            });

          column.data().unique().sort().each(function (d) {
            select.append($('<option>').val(d).text(d));
          });
        } else {
          $(column.header()).html($(column.header()).attr('title'));
        }
      });
    };

    return api;
  }

  return module;
});
