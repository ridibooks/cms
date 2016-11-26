(function () {
    var common_func = function ($) {
        $(function () {
            $('*[attrajax]').attrAjax();
            $('*[datatable]').dataTable({"bPaginate": false});
            $('iframe').iframeAutoHeight();
            $('*[ignore-enter-key]').on("keyup keypress", function (e) {
                var code = e.keyCode || e.which;
                if (code == 13) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    };

    if (typeof define === 'function' && define.amd) {
        require(['jquery', 'jquery.attrajax', 'datatables.net', 'iframeAutoHeight', 'jquery.tmpl'], common_func);
    }
    else {
        common_func(jQuery);
    }
})();
