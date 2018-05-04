jQuery(function ($) {
    $(document).ready(function () {
        //Infinite Scroll for Hail Page
        var ias = $.ias({
            container: '#hail-container',
            item: '.hail-preview',
            pagination: '#hail-pagination',
            next: '#hail-pagination a.next'
        });
        // Add a loader image which is displayed during loading
        ias.extension(new IASSpinnerExtension({
            src: 'resources/vendor/firebrand/silverstripe-hail/client/dist/images/flappy-bird3.gif',
        }));
    });
});