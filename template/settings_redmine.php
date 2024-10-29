<?php
if (!defined('ABSPATH'))
    exit;

/* formulaire en rapport avec redmine */
if ((isset($_POST['redmine'])) && (wp_verify_nonce($_POST['redmine'], 'altreport_redmine'))) {
    $altreport_redmine = unserialize(get_option('altreport_redmine'));


    $array_redmine['actif'] = sanitize_text_field($_POST['actif']);
    $array_redmine['adresse'] = sanitize_text_field($_POST['adresse']);
    $array_redmine['key'] = ($_POST['key'] == 'noooop') ? $altreport_redmine['key'] : sanitize_text_field($_POST['key']);
    $array_redmine['projet'] = sanitize_text_field($_POST['projet']);
    $array_redmine['protocol'] = sanitize_text_field($_POST['protocol']);

    foreach ($_POST['status'] as $key => $value) {
        if ((is_numeric($value)) && (is_numeric($key))) {
            $array_redmine['status' . $key] = $value;
        }
    }

    $new_value = serialize($array_redmine);
    self::set_option('altreport_redmine', $new_value);
}
/* post en rapport avec la configuration de base du plugin */
if ((isset($_POST['infos'])) && (wp_verify_nonce($_POST['infos'], 'altreport_infos'))) {

    if (isset($_POST['user_log'])) {
        $type_user = '|administrator|' . implode("|", $_POST['user_log']) . '|';
    } else {
        $type_user = '|administrator|';
    }
    $array_infos['default_status'] = sanitize_text_field($_POST['default_status']);
    $array_infos['user_log'] = sanitize_text_field($type_user);
    $array_infos['couleur_bouton'] = sanitize_text_field($_POST['couleur_bouton']);
    $array_infos['couleur_text'] = sanitize_text_field($_POST['couleur_text']);
    $array_infos['text_bouton'] = sanitize_text_field($_POST['text_bouton']);
    $array_infos['user_email'] = sanitize_text_field($_POST['user_email']);
    $array_infos['rapport_message'] = sanitize_text_field($_POST['rapport_message']);
    $new_value = serialize($array_infos);
    self::set_option('altreport_infos', $new_value);
}

/* post en rapport avec l'ajout de nouveau status */
if ((isset($_POST['status_once'])) && (wp_verify_nonce($_POST['status_once'], 'altreport_status'))) {
    $altreport_status = unserialize(get_option('altreport_status'));
    if (empty($altreport_status)) {
        $altreport_status = array();
    }

    $array_status['value'] = sanitize_text_field($_POST['value']);
    $array_status['label'] = sanitize_text_field($_POST['label']);
    $array_status['picto'] = sanitize_text_field($_POST['picto']);

    array_push($altreport_status, $array_status);

    $new_value = serialize($altreport_status);
    self::set_option('altreport_status', $new_value);
}

/* post en rapport avec la suppréssion de status */
if ((isset($_POST['sup'])) && (wp_verify_nonce($_POST['sup'], 'altreport_sup'))) {
    if (is_numeric($_POST['sup_status'])) {
        $altreport_status = unserialize(get_option('altreport_status'));
        unset($altreport_status[$_POST['sup_status']]);
        $new_value = serialize($altreport_status);
        self::set_option('altreport_status', $new_value);
    }
}
/**
 * post en rapport avec l'édition d'un status
 */
if ((isset($_POST['edit'])) && (wp_verify_nonce($_POST['edit'], 'altreport_edit'))) {
    if (is_numeric($_POST['edit_status'])) {

        $altreport_status = unserialize(get_option('altreport_status'));
        $altreport_status[$_POST['edit_status']]['value'] = sanitize_text_field($_POST['value']);
        $altreport_status[$_POST['edit_status']]['label'] = sanitize_text_field($_POST['label']);
        $altreport_status[$_POST['edit_status']]['picto'] = sanitize_text_field($_POST['picto']);
        $new_value = serialize($altreport_status);
        self::set_option('altreport_status', $new_value);
    }
}


$altreport_status = unserialize(get_option('altreport_status'));

$altreport_redmine = unserialize(get_option('altreport_redmine'));


$roles = get_editable_roles();
?>
<div class="wrap">
    <h2><?php _e('Paramétrage Redmine', 'alt_report') ?></h2>
    <i><?php _e("Ne fonctionnera sur si vous indiquez toutes les informations", 'alt_report') ?></i>
    <form method="POST">
        <input type="radio" id="actif_redmine_on" name="actif" value="on" <?php echo ($altreport_redmine['actif'] == 'on') ? 'checked' : '' ?>> <label for="actif_redmine_on"> Activer redmine</label>
        <input type="radio" id="actif_redmine_off" name="actif" value="off" <?php echo ($altreport_redmine['actif'] == 'off') ? 'checked' : '' ?>><label for="actif_redmine_off"> Désactiver redmine</label>
        <input type="hidden" name="redmine" value="<?php echo wp_create_nonce('altreport_redmine') ?>"/>
        <table class="form-table">
            <tr>
                <th><label for="protocol"><?php _e("Protocole", 'alt_report') ?>  </label></th>
                <td><select id="protocol" name="protocol">
                        <option value="http" <?php echo ($altreport_redmine['protocol'] == 'http') ? 'selected' : '' ?>>http</option>
                        <option value="https" <?php echo ($altreport_redmine['protocol'] == 'https') ? 'selected' : '' ?>>https</option>
                    </select></td>
            </tr>
            <tr> 
                <th><label for="status_new"><?php _e("Statut <i>Indiquez les ID redmine correspondant au statut</i>", 'alt_report') ?></label></th>
                <td>
                    <table>
                        <?php
                        foreach ($altreport_status as $key => $value) {
                            ?>
                            <tr>
                                <td><?php echo $value['value'] ?></td>
                                <td><?php echo $value['label'] ?></td>
                                <td>
                                    <input type="text" name="status[<?php echo $key ?>]" value="<?php echo (isset($altreport_redmine['status' . $key])) ? $altreport_redmine['status' . $key] : '' ?>">
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>

                </td>
            </tr>
            <tr>
                <th><label for="adresse"><?php _e("Adresse <i>exemple site.fr sans le http</i>", 'alt_report') ?></label></th>
                <td><input type="text" id="adresse" name="adresse" value="<?php echo $altreport_redmine['adresse'] ?>"></td>
            </tr>
            <tr>
                <th><label for="key"><?php _e("Clé d'acces API : disponible sur votre compte redmine '/my/account'", 'alt_report') ?> </label></th>
                <td><input type="password" id="key" name="key" value="<?php echo ($altreport_redmine['key'] != '') ? "noooop" : "" ?>"></td>
            </tr>
            <tr>
                <th><label for="projet"><?php _e("Projet", 'alt_report') ?> </label></th>
                <td><input type="text" id="projet" name="projet" value="<?php echo $altreport_redmine['projet'] ?>"></td>
            </tr><tr>
                <td colspan="2"><input type="submit"></td>
            </tr>
        </table>
    </form>
</div>