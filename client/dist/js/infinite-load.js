jQuery(function ($) {
    $(document).ready(function () {
        //Infinite Scroll for Hail Page
        var ias = $.ias({
            container: '#hail-page-previews',
            item: '.hail-preview',
            pagination: '#hail-page-pagination',
            next: '#hail-page-pagination a.next'
        });
        // Add a loader image which is displayed during loading
        ias.extension(new IASSpinnerExtension({
            src: 'resources/vendor/firebrandhq/silverstripe-hail/client/dist/images/flappy-bird3.gif',
        }));
    });
});