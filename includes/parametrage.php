<?php

/**
 * Parameters the plugin when activated
 */
$altreport_status = unserialize(get_option('altreport_status'));
$altreport_infos = unserialize(get_option('altreport_infos'));
$altreport_redmine = unserialize(get_option('altreport_redmine'));


if ($altreport_infos == '') {
    $array_infos['default_status'] = '';
    $array_infos['user_log'] = '|administrator|';
    $array_infos['couleur_bouton'] = '#000000';
    $array_infos['text_bouton'] = __("Signaler", 'alt_report');
    $array_infos['couleur_text'] = '#ffffff';
    $array_infos['rapport_message'] = __("Votre rapport a bien été signalé nous revenons vers vous dans les plus brefs délai", 'alt_report');

    $new_value = serialize($array_infos);
    self::set_option('altreport_infos', $new_value);
}
if ($altreport_status == '') {
    $altreport_status = array();

    $array_status['value'] = sanitize_text_field('new');
    $array_status['label'] = sanitize_text_field('Nouveau');
    $array_status['picto'] = sanitize_text_field('dashicons-plus');

    array_push($altreport_status, $array_status);

    $array_status['value'] = sanitize_text_field('fin');
    $array_status['label'] = sanitize_text_field('Terminer');
    $array_status['picto'] = sanitize_text_field('dashicons-archive');

    array_push($altreport_status, $array_status);

    $array_status['value'] = sanitize_text_field('cours');
    $array_status['label'] = sanitize_text_field('En cours');
    $array_status['picto'] = sanitize_text_field('dashicons-index-card');

    array_push($altreport_status, $array_status);

    $new_value = serialize($altreport_status);
    self::set_option('altreport_status', $new_value);
}

if ($altreport_redmine == '') {
    $array_redmine['key'] = $altreport_redmine['adresse'] = $altreport_redmine['projet'] = $altreport_redmine['protocol'] ='';
    $array_redmine['status0'] = 1;
    $array_redmine['actif'] = 'off';
    $new_value = serialize($array_redmine);
    self::set_option('altreport_redmine', $new_value);
}


$upload_dir = wp_upload_dir();
$alt_report_dir = $upload_dir['basedir'] . '/alt_report';
if (!file_exists($alt_report_dir)) {
    wp_mkdir_p($alt_report_dir);
}