import $ from 'jquery';
import 'bootstrap';
import 'select2';

// FOUT 현상 때문에 html 에 직접 inline
//import 'style!css!bootstrap/dist/css/bootstrap.min.css';
//import 'style!css!select2/dist/css/select2.min.css';

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
