jQuery(function ($) {
    $('.pagination-go-to input').on('keyup', function(e){
        if(e.keyCode === 13) {
            let newPage = parseInt($(this).val());
            let currentPage = parseInt($(this).data('current'));
            let totalPages = parseInt($('#hail-page-pagination').data('totalpages'));
            if(newPage !== currentPage && newPage <= totalPages && newPage > 0) {
                let pageLength = parseInt($('#hail-page-pagination').data('pagelength'));
                //New page start
                let start = (newPage - 1) * pageLength;
                //redirect to page
                window.location.href = window.location.pathname + '?start=' + start;
            }
        }
    });

    $('.pagination-go-to input').on('focus', function (e) {
        $(this)
            .one('mouseup', function () {
                $(this).select();
                return false;
            })
            .select();
    });
});