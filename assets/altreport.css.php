<?php 
header('content-type:text/css');
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
$altreport_infos = unserialize(get_option('altreport_infos'));
?>


#report_bug {
    background-color : <?Php echo $altreport_infos['couleur_bouton'] ?>;
    color : <?Php echo $altreport_infos['couleur_text'] ?>;
    border-color: <?Php echo $altreport_infos['couleur_bouton'] ?>;
}

#report_bug:hover {
    background-color : <?Php echo $altreport_infos['couleur_text'] ?>;
    color : <?Php echo $altreport_infos['couleur_bouton'] ?>;
}

.report_dady:hover,
.report_dady:focus {
    color: <?Php echo $altreport_infos['couleur_bouton'] ?>;
}

.report_modale-title {
    color : <?Php echo $altreport_infos['couleur_bouton'] ?>;
}

.report_modale a:hover,
.report_modale a:focus {
    color : <?Php echo $altreport_infos['couleur_bouton'] ?>;
}

.report_display input[type="submit"],
.alt_report_bug  input[type="submit"] {
    color : <?Php echo $altreport_infos['couleur_bouton'] ?>;
    background-color : <?Php echo $altreport_infos['couleur_text'] ?>;
    border-color : <?Php echo $altreport_infos['couleur_bouton'] ?>;
}

#altreport_update {
    color : <?Php echo $altreport_infos['couleur_text'] ?>;
}
