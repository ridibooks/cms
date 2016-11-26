/**
 * Fork from http://stackoverflow.com/questions/15423870/find-next-table-when-click-in-a-div
 */

;
(function ($, window) {
    'use strict';

    function exportTableToCSV($table, filename) {
        // Temporary delimiter characters unlikely to be typed by keyboard
        // This is to avoid accidentally splitting the actual contents
        var $rows = $table.find('tr:has(td)'),
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character

            colDelim = "&nbsp;</td>\n\t<td>",
            rowDelim = "&nbsp;</td>\n</tr>\n<tr>\n\t<td>",

        // Grab text from table into CSV formatted string
            csv = '<table><tr>\n\t<td>' + $rows.map(function (i, row) {
                    var $row = $(row),
                        $cols = $row.find('td');

                    return $cols.map(function (j, col) {
                        var $col = $(col),
                            text = $col.text();

                        return text.replace('"', '""'); // escape double quotes

                    }).get().join(tmpColDelim);

                }).get().join(tmpRowDelim)
                    .split(tmpRowDelim).join(rowDelim)
                    .split(tmpColDelim).join(colDelim) + '</td>\n</tr></table>',

        // Data URI
            xlsData = 'data:application/xls;charset=utf-8,' + encodeURIComponent(csv);

        $(this)
            .attr({
                'download': filename,
                'href': xlsData,
                'target': '_blank'
            });
    }

    $.fn.atagToDownloadTable = function (target_table, filename) {
        $(this).on('click', function(){
            if (typeof filename == 'undefined')
                filename = 'export.xls';
            exportTableToCSV.apply(this, [$(target_table), filename]);
        });
    };
})(jQuery, window);
