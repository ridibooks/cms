import $ from 'jquery';

import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

import 'select2';
import 'select2/dist/css/select2.min.css';

$(function() {
  'use strict';

  const $menu_select = $(".menu_select");
  $menu_select.select2({
    placeholder: "메뉴를 검색하세요"
  });

  $menu_select.on("select2:select", (e) => {
    window.location.href = e.params.data.id;
  });
});
