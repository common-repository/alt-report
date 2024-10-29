<?php
/*
 * Page called by an external site to run alt report
 */
//todo jquery
//todo vérification si site autorisé
if(!isset($_GET['jquery'])){
echo"<script type='text/javascript' src='https://code.jquery.com/jquery-3.1.1.min.js'></script>";
}
echo "<script type='text/javascript' src=" . ALTREPORT_LOAD_ASSETS . "html2canvas.js?ver=" . ALTREPORT_VERSION . "></script>";
echo "<link rel='stylesheet'  href='" . get_site_url() . "/wp-includes/css/dashicons.min.css?ver=4.7.1' type='text/css' media='all' />";
echo"<link rel='stylesheet'  href=" . ALTREPORT_LOAD_ASSETS . "altreport.css.php?ver=" . ALTREPORT_VERSION . "' type='text/css' media='all' />";
?>
<script>
    var php_vars = new Array();
    php_vars['siteurl'] = '<?Php echo get_option('siteurl') ?>';
</script>        
<?php
echo "<script type='text/javascript' src=" . ALTREPORT_LOAD_ASSETS . "altreport.js?ver=" . ALTREPORT_VERSION . "></script>";

altreport_footer();
