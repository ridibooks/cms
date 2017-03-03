/**
 * Created by Sunghoon on 1/28/15.
 */

define([], function () {
  function updateQueryStringParameter(params) {
    var queryParams = {}, queryString = location.search.substring(1), re = /([^&=]+)=([^&]*)/g, m;

    while (m = re.exec(queryString)) {
      queryParams[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
    }
    for (var key in params) {
      if(params.hasOwnProperty(key)){
        if (params[key]) {
          queryParams[key] = params[key];
        } else {
          delete queryParams[key];
        }
      }
    }
    return queryParams;
  }

  function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
      results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
  }

  function parseQueryString(queryString) {
    var match;
    var pl = /\+/g;  // Regex for replacing addition symbol with a space
    var search = /([^&=]+)=?([^&]*)/g;
    var decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); };
    var query = queryString;
    var urlParams = {};

    if (query.substring(0, 1) === '&' || query.substring(0, 1) === '?') {
      query = query.substring(1);
    }

    while (match = search.exec(query)) {
      urlParams[decode(match[1])] = decode(match[2]);
    }

    return urlParams;
  }

  return {
    updateQueryStringParameter: updateQueryStringParameter,
    getParameterByName: getParameterByName,
    parseQueryString: parseQueryString
  };
});
