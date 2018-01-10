/**
 * Created by Sunghoon on 1/28/15.
 */

define(["jquery"], function ($) {
  var module = {};

  //module.priceFormatting = function (nStr) {
  function priceFormatting(nStr) {
    nStr += '';
    var x = nStr.split('.');
    var x1 = x[0];
    var x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
      x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
  }

  function percentFormatting(nStr) {
    return nStr + '%';
  }

  return {
    priceFormatting: priceFormatting,
    percentFormatting: percentFormatting
  };

});
