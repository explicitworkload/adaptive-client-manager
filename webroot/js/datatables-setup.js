/*
 * Theme defaults for DataTables 3, reproducing the layout of the original
 * DataTables 1.9 tables: full-width search bar on top, "Showing ..." line,
 * table, then "Show N entries" and Previous/Next buttons.
 * Load after the DataTables script (webroot/vendor/datatables-VERSION/dataTables.min.js).
 */
(function ($) {
    if (!$.fn.dataTable) {
        return;
    }
    $.extend(true, $.fn.dataTable.defaults, {
        dom: '<"top"f>irt<"bottom"lp><"clear">',
        pagingType: 'simple',
        // Header cells marked class="no_sort" (action columns) can't be sorted.
        columnDefs: [{ targets: 'no_sort', orderable: false }],
        language: {
            search: 'Search (Auto Filter):',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            infoFiltered: '(filtered from _MAX_ total entries)',
            paginate: { previous: 'Previous', next: 'Next' }
        }
    });
})(jQuery);
