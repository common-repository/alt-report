<?Php
/**
 * Displays the report modal
 */
?>
<div class="alt_report-wrapper">
    <div id="alt_report">
        <button type="button" id="report_bug" class="report_bug report_dady"><span class="dashicons dashicons-welcome-write-blog"></span> <?Php echo $altreport_infos['text_bouton'] ?></button>
        <div id="alt_report_bug" class="alt_report_bug reportreport_bug" style=" display: none;">
            <p><?php _e("Vous pouvez cliquer sur la zone du bug", 'alt_report') ?></p>
           <form method="POST" id="altreport_report">
                <?php if (!empty($altreport_redmine['priorite'])) { ?>
                    <?php _e("priorité", 'alt_report') ?> <select id="issue_priority_id" name="issue[priority_id]">
                    <?php
                    foreach ($altreport_redmine['priorite'] as $key => $value) {
                        echo'  <option value="' . $key . '">' . $value . '</option>';
                    }
                    ?>
                    </select>
                <?php } ?>
                <?php if (($script_co == 'deco') || (isset($user_infos) && $user_infos->ID == 0 || (!isset($user_infos)))) { ?>
                    <input type="text" name="email_user" placeholder="<?php _e("Votre email si vous souhaitez être re-contacté", 'alt_report') ?>"> 
                <?php } ?>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('altreport_nonce') ?>"/>
                <input type="text" name="issue[subject]" required placeholder="<?php _e("Titre ticket*", 'alt_report') ?>"> 
                <textarea name="issue[description]" required placeholder="<?php _e("Description du problème*", 'alt_report') ?>"></textarea>
                <input type="hidden" name="HTTP_USER_AGENT" id="HTTP_USER_AGENT" value="">
                <input type="hidden" name="REQUEST_URI" id="REQUEST_URI" value="">
                <input type="hidden" name="innerWidth" id="innerWidth" value="" >
                <input type="hidden" name="innerHeight" id="innerHeight" value="" >
                <input type="submit" class="report_input">
           </form>
        </div>
    </div><?php if (!isset($reports)) { ?>
        <div id="alt_reports">
            <?php echo $reports ?>
        </div>
    <?php } ?>
</div>
<div id="super_modale" title="Vous pouvez cliquer sur la zone du bug" ></div>

