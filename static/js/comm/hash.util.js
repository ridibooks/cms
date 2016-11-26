/**hash 관련 util javascript module*/
define(["jquery"], function ($) {
  var module = {};

  /**
   * 현재 hash string에 hash값을 추가한다.
   * @param key
   * @param value
   */
  module.addHash = function (key, value) {
    var hash = _getHashObject();
    hash[key] = value;

    _setHash(hash);
  };

  /**
   * 현재 hash string에서 해당하는 key의 hash값을 제거한다.
   * @param key
   */
  module.removeHash = function (key) {
    var hash = _getHashObject();
    delete hash[key];

    _setHash(hash);
  };

  /**
   * 현재 hash string에서 해당하는 key의 hash값을 구해온다.
   * @param key
   * @returns {*}
   */
  module.getHashValue = function (key) {
    var hash = _getHashObject();
    return hash[key];
  };

  /**
   * array나 object로부터 hash string을 지정한다.
   * @param hash
   * @private
   */
  function _setHash(hash) {
    window.location.hash = '#' + $.param(hash);
  }

  /**
   * 해당 페이지의 hash string을 읽어와 object로 변환하여 리턴한다.
   * @returns {{}}
   * @private
   */
  function _getHashObject() {
    if (!window.location.hash)
      return {};
    var pairs = window.location.hash.slice(1).split('&');

    var result = {};
    for (var i = 0; i < pairs.length; i++) {
      var pair = pairs[i].split('=');
      result[pair[0]] = decodeURIComponent(pair[1] || '');
    }

    return result;
  }

  return module;
});
