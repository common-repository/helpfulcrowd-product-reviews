jQuery(document).ready(function () {
    var host = 'https://app.helpfulcrowd.com';

    jQuery.get(host + '/res/widgets/' + helpfulcrowdStoreId + '.json', function (data) {
        if (data) {
			console.log(data);
            jQuery.getScript(data.js.url, function () {
                jQuery.each(helpfulcrowdWidgets, function (i, v) {
                    hc_process_static_page(helpfulcrowdStoreId, data.css.theme, v);
                });
            });
            if (jQuery('head link[type="text/css"][rel="stylesheet"][href="' + data.css.url + '"]').length <= 0)
                jQuery('head').append(jQuery('<link rel="stylesheet" type="text/css" />').attr('href', data.css.url));
            if (data.custom_css.enabled)
                jQuery('head').append(jQuery('<style id="hc-custom-css">' + window.atob(data.custom_css.code) + '</style>'));
			
			jQuery('head').append(jQuery('<style id="hc-settings-css">' + window.atob(data.settings_css.code) + '</style>'));
        }
    });
});
