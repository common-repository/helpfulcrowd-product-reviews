jQuery(document).ready(function () {
    var host = 'https://app.helpfulcrowd.com';

    jQuery('.helpfulcrowd-setting-accordion-tab-header').on('click', function (e) {
        e.preventDefault();
        jQuery(e.target).find('.svg').toggleClass('svg-toogle');
        jQuery(e.target).next().toggle();
    });
});
