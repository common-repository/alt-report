/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


jQuery(document).ready(function () {
    jQuery(".toogle").on("click", function () {
        var value = jQuery(this).attr("id");

        jQuery(".toogle").removeClass('active');
        jQuery(this).addClass('active');

        jQuery(".onglet_content").hide();
        jQuery("." + value).show();

        return false;
    });

    jQuery("#iconlist .dashicons").on("click", function () {
        jQuery('#iconlist').hide();

        var iconlist = jQuery('#iconlist').attr("iconlist");

        jQuery(this).removeClass('dashicons');
        var value = jQuery(this).attr('class');
        jQuery('#' + iconlist).val(value);

        jQuery(this).addClass('dashicons');

    });
    jQuery(".picto").on("click", function () {
        var value = jQuery(this).attr("id");
        jQuery("#iconlist").attr("iconlist", value);
        jQuery('#iconlist').show();
    });

});