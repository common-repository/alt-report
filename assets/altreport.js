jQuery(document).ready(function () {
    if (!php_vars)
    {
        var php_vars = new Array();
        php_vars['siteurl'] = '..';
    }
    jQuery(document).on('click', '#altreport_update', function () {
        var href = jQuery(this).attr('href');
        jQuery("#alt_report").html('loading...');
        jQuery.post(href, function (data) {
            location.reload();
        });

        return false;
    });

    jQuery(".report_bug").click(function () {
        jQuery(".report_modale").hide();
        var value = jQuery(this).attr("id");
        jQuery(".report" + value).toggle();
        // jQuery("#alt_reports").toggleClass("open");
    });
    jQuery("#report_bug").click(function () {
        jQuery("#alt_report").toggleClass("open");
        jQuery("#super_modale").toggle();
    });

    jQuery("#super_modale").on("click", function (event) {
        jQuery("#super_modale").after("<div class='select_modale' style=' top:" + event.pageY + "px; left:" + event.pageX + "px;'><textarea></textarea> <div class='close'>X</div></div>");
    });

    jQuery(document).on('click', '.select_modale .close', function () {
        jQuery(this).parent().hide();
    });

    jQuery("#altreport_report").submit(function () {
        jQuery("#alt_report").html('loading...');
        jQuery.post(php_vars['siteurl'] + "/altreport_report/", jQuery(this).serialize(), function (id_post) {
            console.log(id_post);
            html2canvas(document.body, {
                onrendered: function (canvas) {
                    var imagedata = canvas.toDataURL('image/png');
                    var imgdata = imagedata.replace(/^data:image\/(png|jpg);base64,/, "");
                    jQuery.ajax({
                        url: php_vars['siteurl'] + "/altreport_image/?id_post=" + id_post,
                        data: {
                            imgdata: imgdata
                        },
                        type: 'post',
                        success: function (response) {
                            //quand l'enregistrement de l'image est ok on reload
                            location.reload();
                        }
                    });
                }});

            jQuery("#alt_report").hide();

        });
        return false;
    });

    jQuery(".altreport_report_update").submit(function () {
        var value_ticket = jQuery(this).attr("id");
        jQuery('.' + value_ticket).html("commentaire envoyer");
        jQuery.post(php_vars['siteurl'] + "/altreport_report/", jQuery(this).serialize(), function (value_ticket) {

        })
        return false;
    });
//active un layer transparent sur la page en fix dés l'ouverture de signaler
    document.getElementById("innerWidth").value = (window.innerWidth);
    document.getElementById("innerHeight").value = (window.innerHeight);
    var nVer = navigator.appVersion;
    var nAgt = navigator.userAgent;
    var browserName = navigator.appName;
    var fullVersion = '' + parseFloat(navigator.appVersion);
    var majorVersion = parseInt(navigator.appVersion, 10);
    var nameOffset, verOffset, ix;

// In Opera, the true version is after "Opera" or after "Version"
    if ((verOffset = nAgt.indexOf("Opera")) != -1) {
        browserName = "Opera";
        fullVersion = nAgt.substring(verOffset + 6);
        if ((verOffset = nAgt.indexOf("Version")) != -1)
            fullVersion = nAgt.substring(verOffset + 8);
    }
// In MSIE, the true version is after "MSIE" in userAgent
    else if ((verOffset = nAgt.indexOf("MSIE")) != -1) {
        browserName = "Microsoft Internet Explorer";
        fullVersion = nAgt.substring(verOffset + 5);
    }
// In Chrome, the true version is after "Chrome" 
    else if ((verOffset = nAgt.indexOf("Chrome")) != -1) {
        browserName = "Chrome";
        fullVersion = nAgt.substring(verOffset + 7);
    }
// In Safari, the true version is after "Safari" or after "Version" 
    else if ((verOffset = nAgt.indexOf("Safari")) != -1) {
        browserName = "Safari";
        fullVersion = nAgt.substring(verOffset + 7);
        if ((verOffset = nAgt.indexOf("Version")) != -1)
            fullVersion = nAgt.substring(verOffset + 8);
    }
// In Firefox, the true version is after "Firefox" 
    else if ((verOffset = nAgt.indexOf("Firefox")) != -1) {
        browserName = "Firefox";
        fullVersion = nAgt.substring(verOffset + 8);
    }
// In most other browsers, "name/version" is at the end of userAgent 
    else if ((nameOffset = nAgt.lastIndexOf(' ') + 1) <
            (verOffset = nAgt.lastIndexOf('/')))
    {
        browserName = nAgt.substring(nameOffset, verOffset);
        fullVersion = nAgt.substring(verOffset + 1);
        if (browserName.toLowerCase() == browserName.toUpperCase()) {
            browserName = navigator.appName;
        }
    }
// trim the fullVersion string at semicolon/space if present
    if ((ix = fullVersion.indexOf(";")) != -1)
        fullVersion = fullVersion.substring(0, ix);
    if ((ix = fullVersion.indexOf(" ")) != -1)
        fullVersion = fullVersion.substring(0, ix);

    majorVersion = parseInt('' + fullVersion, 10);
    if (isNaN(majorVersion)) {
        fullVersion = '' + parseFloat(navigator.appVersion);
        majorVersion = parseInt(navigator.appVersion, 10);
    }

    var HTTP_USER_AGENT = 'Browser name  = ' + browserName + '<br>'
            + 'Full version  = ' + fullVersion + '<br>'
            + 'Major version = ' + majorVersion + '<br>'
            + 'navigator.appName = ' + navigator.appName + '<br>'
            + 'navigator.userAgent = ' + navigator.userAgent + '<br>'
            + 'platform = ' + navigator.platform + '<br>';
    document.getElementById("HTTP_USER_AGENT").value = HTTP_USER_AGENT;

    var currentLocation = window.location;
    document.getElementById("REQUEST_URI").value = (currentLocation);


});
         