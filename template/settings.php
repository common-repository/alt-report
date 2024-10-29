<?php
if (!defined('ABSPATH'))
    exit;

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
$altreport_infos = unserialize(get_option('altreport_infos'));

$altreport_infos['user_log'] = (isset($altreport_infos['user_log'])) ? $altreport_infos['user_log'] : '|administrator|';
$roles = get_editable_roles();
?>
<div class="wrap">
    <h2><?php _e('Paramétrage général', 'alt_report') ?></h2>

    <form method="POST">
        <input type="hidden" name="infos" value="<?php echo wp_create_nonce('altreport_infos') ?>"/>
        <table class="form-table">
            <tr>
                <th><label for="user_log"><?php _e("Resctriction d'affichage", 'alt_report') ?> </label></th>
                <td>
                    <input type="checkbox" name="user_log[]" value="all" id="all_user" <?php echo (strpos($altreport_infos['user_log'], "|all|") !== false ) ? 'checked' : '' ?>> <label for="all_user"><?php _e("Tous les utilisateurs même déconnecté", 'alt_report') ?></label><br>
                    <?php
                    foreach ($roles as $key => $role) {
                        ?>
                        <input type="checkbox" name="user_log[]" value="<?php echo $key ?>" id="<?php echo $key ?>" <?php echo (strpos($altreport_infos['user_log'], "|" . $key . "|") !== false ) ? 'checked' : '' ?> > <label for="<?php echo $key ?>"><?php echo $role['name'] ?></label> 
                        <?php
                    }
                    ?>        
                </td>      
            </tr>
            <tr>
                <th><label for="couleur_text"><?php _e("Couleur du texte", 'alt_report') ?> </label></th>
                <td> <input type="color" id="couleur_text" name="couleur_text" value="<?php echo $altreport_infos['couleur_text'] ?>"></td>
            </tr><tr>
                <th><label for="couleur_bouton"><?php _e("Couleur du bouton", 'alt_report') ?></label></th>
                <td> <input type="color" id="couleur_bouton" name="couleur_bouton" value="<?php echo $altreport_infos['couleur_bouton'] ?>"></td>
            </tr><tr>
                <th><label for="text_bouton"><?php _e("Texte du bouton", 'alt_report') ?> </label></th>

                <td> <input type="text" id="text_bouton" name="text_bouton" value="<?php echo $altreport_infos['text_bouton'] ?>"></td>
            </tr> 
            <tr>
                <th><label for="user_email"><?php _e("Envoyer des notifications, email séparé d'une virgule", 'alt_report') ?> </label></th>
                <td> <input style=" width: 300px" type="text" id="user_email" name="user_email" placeholder="<?php _e("Emails séparé d'une virgule", 'alt_report') ?>"  value="<?php echo (isset($altreport_infos['user_email'])) ? $altreport_infos['user_email'] : '' ?>" > </td>
            </tr>
            <tr>
                <th><label for="rapport_message"><?php _e("Message envoyer par email aux visiteurs qui on créé un rapport", 'alt_report') ?> </label></th>
                <td> <textarea style=" width: 300px" id="rapport_message" name="rapport_message"><?php echo $altreport_infos['rapport_message'] ?></textarea> </td>
            </tr>
            <tr>
                <th><?php _e("Statut par défaut", 'alt_report') ?> </th>
                <td>  
                    <select name="default_status">
                        <option value=""></option>
                        <?php foreach ($altreport_status as $key => $value) { ?>
                            <option value="<?php echo $value['value'] ?>" <?php echo ($altreport_infos['default_status'] == $value['value']) ? 'selected' : '' ?>><?php echo $value['label'] ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit"></td>
            </tr>
        </table>
    </form>
    <h2><?php _e("Gérer vos statut de ticket", 'alt_report') ?></h2>
    <p><?php _e("Indiquer comme valeur de statut 'fin' pour masquer les tickets", 'alt_report') ?> </p>
    <table class="form-table">
        <tr>
            <th><?php _e("Identifiant", 'alt_report') ?></th>
            <th><?php _e("Affichage", 'alt_report') ?></th>
            <th><?php _e("Icone", 'alt_report') ?></th>
            <th colspan="2"><?php _e("Edition", 'alt_report') ?></th>
        </tr>
        <form method="POST">
            <input type="hidden" name="status_once" value="<?php echo wp_create_nonce('altreport_status') ?>"/>
            <tr >
                <td>
                    <input type="text" name="value" placeholder="<?php _e("valeur statut", 'alt_report') ?>" required> 
                </td>
                <td>
                    <input type="text" name="label" placeholder="<?php _e("label statut", 'alt_report') ?>" required>
                </td>
                <td style=" position: relative;">
                    <input type="text" class="picto" id="pictobase" name="picto" placeholder="<?php _e("cliquer pour choisir", 'alt_report') ?>" required>
                    <?php include 'pictos.php'; ?>
                </td>
                <td> <input type="submit"  class="button"  style="width: 100px;"></td>
            </tr>
        </form>
        <?php
        if (!empty($altreport_status)) {
            foreach ($altreport_status as $key => $value) {
                ?>
                <tr>
                <form method="POST">
                    <input type="hidden" name="edit" value="<?php echo wp_create_nonce('altreport_edit') ?>"/>
                    <input type="hidden" name="edit_status" value="<?php echo $key ?>"/>

                    <td><input type="text" name="value" value="<?php echo $value['value'] ?>" required></td>
                    <td><input type="text" name="label" value="<?php echo $value['label'] ?>" required></td>
                    <td><span class="dashicons <?php echo $value['picto'] ?>"></span>
                        <input type="text" name="picto" id="picto<?php echo $key ?>" class="picto" value="<?php echo $value['picto'] ?>" required>
                    </td>
                    <td><input type="submit" value="Editer"></td>
                </form>
                <td>
                    <form method="POST">
                        <input type="hidden" name="sup" value="<?php echo wp_create_nonce('altreport_sup') ?>"/>
                        <input type="hidden" name="sup_status" value="<?php echo $key ?>" >
                        <input type="submit" class="button button-primary" value="<?php _e("Supprimer", 'alt_report') ?>" style="background: red; border-color: brown; width: 100px;" onclick="javascript:return confirm('<?php _e("Etes vous sur?", 'alt_report') ?>')">
                    </form>
                </td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
    <h2><?php _e("Inclure alt Report sur un site tier", 'alt_report') ?></h2>
    <p><?php _e("Ajouter /altreport_script/?jquery a la suite du script si vous avez déja jquery sur votre site ", 'alt_report') ?> 
        <textarea cols="70"><?php echo "<?php echo file_get_contents(" . get_option('siteurl') . "/altreport_script/'); ?>"; ?></textarea>
</div>

