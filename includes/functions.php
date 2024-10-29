<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Tells the email the format
 * @param type $status
 * @return string
 */
function wpdocs_set_html_mail_content_type() {
    return 'text/html';
}

/**
 * Connected with redmine
 * @param type $altreport_redmine
 * @param type $content
 * @param type $post_id
 * @param type $type
 * @return type
 */
function liaison_redmine($altreport_redmine, $content, $post_id, $type = '') {

    if ($altreport_redmine['actif'] == 'on') {

        if ($type != '') {
            $issue = array(
                'issue' => array(
                    'notes' => $content,
                    'status_id' => $type,
                )
            );

            $post_type = 'PUT';
            $fin_url = '/issues/' . $post_id . '.json';
        } else {
            $status_id = (isset($altreport_redmine['status0'])) ? $altreport_redmine['status0'] : '1';
            $issue = array(
                'issue' => array(
                    'project_id' => $altreport_redmine['projet'],
                    'reporter_id' => 1,
                    'status_id' => $status_id,
                    'subject' => sanitize_text_field($_POST['REQUEST_URI'] . '] ' . $_POST['issue']['subject']),
                    'description' => $content,
                //'assigned_to_id' => $altreport_redmine['user_id'],
                )
            );
            $post_type = 'POST';
            $fin_url = '/issues.json';
        }

        $postdata = http_build_query(
                $issue
        );

        $opts = array('http' =>
            array(
                'method' => $post_type,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        $context = stream_context_create($opts);

        $url = $altreport_redmine['protocol'] . '://' . $altreport_redmine['adresse'] . '/' . $fin_url . '?key=' . $altreport_redmine['key'];
        $result = file_get_contents($url, false, $context);
        if ($type == '') {
            $obj = json_decode($result);
            $issue = $obj->{'issue'};

            add_post_meta($post_id, 'altreport_redmine', $issue->id, true);
            return '<p>' . __("Redmine", 'alt_report') . ' :[<a href="' . $altreport_redmine['protocol'] . '://' . $altreport_redmine['adresse'] . '/issues/' . $issue->id . '">' . $issue->id . '</a>]</p>';
        }
    }
}

function get_redmine($altreport_redmine, $type = '') {
    if ($altreport_redmine['actif'] == 'on') {
        $fin_url = '/issues.json';
        $getdata = http_build_query(
                array(
                    'issue_id' => 'open',
                    'project_id' => $altreport_redmine['projet'],
                    'offset' => '0',
                    'limit' => '100'
                )
        );
        $url = $altreport_redmine['protocol'] . '://' . $altreport_redmine['adresse'] . '/' . $fin_url . '?key=' . $altreport_redmine['key'] . '&' . $getdata;
        $result = file_get_contents($url, false);
        return json_decode($result);
    }
}

/**
 * Displays status list
 * @param type $status
 * @return string
 */
function altreport_list_status($status) {
    $altreport_status = unserialize(get_option('altreport_status'));
    $liste_status = '';
    if (!empty($altreport_status)) {
        $liste_status = '<label for="altreport_status">Statut : </label><select id="altreport_status" name="altreport_status">';
        foreach ($altreport_status as $value) {
            $selected = ($status == $value['value']) ? "selected" : "";
            $liste_status .= '<option ' . $selected . ' value="' . $value['value'] . '">' . $value['label'] . '</option>';
        }
        $liste_status .= "</select>";
    }
    return $liste_status;
}

/**
 * Displays status 
 * @param type $status
 * @param type $picto
 * @return string
 */
function altreport_label_status($status, $picto = '0') {
    $altreport_status = unserialize(get_option('altreport_status'));
    $label = 'N/A';
    foreach ($altreport_status as $value) {
        if ($value['value'] == $status) {
            if ($picto == '0') {
                $label = $value['label'];
            } else {
                $label = '<span class="dashicons ' . $value['picto'] . '"></span> ' . $value['label'];
            }
        }
    }
    return $label;
}

/**
 * Returns status according to id
 * @param type $status
 * @return type
 */
function altreport_status_redmine($status) {
    $altreport_status = unserialize(get_option('altreport_status'));
    $altreport_redmine = unserialize(get_option('altreport_redmine'));
    foreach ($altreport_status as $key => $value) {
        if ($value['value'] == $status) {
            return $altreport_redmine['status' . $key];
        }
    }
}

add_action('wp_footer', 'altreport_footer');

/**
 * Adding the error report to the footer
 * @param type $script_co
 */
function altreport_footer($script_co = '') {

    //todo vérifier les roles
    $altreport_infos = unserialize(get_option('altreport_infos'));
    $altreport_redmine = unserialize(get_option('altreport_redmine'));
    $user_infos = wp_get_current_user();
    $user_role = $reports = $infos = '';
    /* définiton par défaut du parametrage */

    //todo limiter au meta diférent de fini
    /* récupération des post de type rapport différent de fini et par rapport a la page en cours */
    if (isset($user_infos) && $user_infos->ID != 0) {
        $user_role = '|' . implode("|", $user_infos->roles) . '|';
        if (strpos($altreport_infos['user_log'], $user_role) !== false) {

            $report = array(
                'posts_per_page' => '-1',
                'post_type' => 'rapport',
                'meta_query' => array(
                    array(
                        'key' => 'altreport_urlbug',
                        'value' => sanitize_text_field($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']),
                        'compare' => 'LIKE'
                    )
                ),
                'meta_key' => 'altreport_status',
                'orderby' => 'meta_value',
                'order' => 'DESC'
            );

            $post_report = new WP_Query($report);
            $type_statut = array();
            foreach ($post_report->posts as $value) {

                $status = esc_attr(get_post_meta($value->ID, 'altreport_status', true));

                $picto = altreport_label_status($status, 1);
                $type_statut[$status]['title'] = $picto;

                $list_status = altreport_list_status($status);
                $ticket_id = get_post_meta($value->ID, 'altreport_redmine', true);
                $url_redmine = $url = '';
                if (isset($ticket_id) && ($altreport_redmine['actif'] == 'on')) {
                    $url = $altreport_redmine['protocol'] . '://' . $altreport_redmine['adresse'] . '/issues/' . $ticket_id;
                    $url_redmine = '<a href="' . $url . '" target="_blank">#' . $ticket_id . ' ticket</a>';
                }
                $url_back = '<a href="/wp-admin/post.php?post=' . $value->ID . '&action=edit"  target="_blank">Edit</a>';

                $infos = '<button type="button" id="' . $value->ID . '" class="item-report report_bug report_ticket">' . $value->post_title . '</button>'
                        . '<div class="report' . $value->ID . ' report_display report_modale"><div class="report_modale-wrapper"><div class="report_modale-title">Commentaire :</div> ' . $value->post_excerpt . '</div>'
                        . '<form method="POST" class="altreport_report_update" id="report' . $value->ID . '" >'
                        . '<input type="hidden" name="nonce" value="' . wp_create_nonce('altreport_nonce') . '"/>'
                        . '<input type="hidden" name="edit" value="' . $value->ID . '" >'
                        . $list_status
                        . '<textarea name="commentaire" placeholder="' . __("Description", 'alt_report') . '"></textarea>'
                        . '<input type="submit"  class="report_input">'
                        . '</form>'
                        . '<p>Suivis du ticket : ' . $url_redmine . ' - ' . $url_back . '</p>'
                        . '</div>';
                $type_statut[$status]['report'][] = $infos;
            }
            foreach ($type_statut as $key => $un_status) {
                $nb_report = count($un_status['report']);
                $reports .= '<button type="button" class="report_bug report_dady" id="statut' . $key . '">(' . $nb_report . ') ' . $un_status['title'] . '</button>';
                $reports .= '<div class="alt_reports-wrapper reportstatut' . $key . '" style="display:none;">' . implode(" ", $un_status['report']) . '</div>';
            }

            if ($altreport_redmine['actif'] == 'on') {
                $reports .= '<a href="/altreport_update" id="altreport_update" class="dashicons dashicons-update">' . __("Synchroniser", 'alt_report') . '</a>';
            }

            require_once 'modales.php';
        }
    } else if (strpos($altreport_infos['user_log'], "|all|") !== false) {
        require_once 'modales.php';
    }
}

add_action('send_headers', "altreport_rooter");

/**
 * Manage redirection for ajax
 */
function altreport_rooter() {
    $altreport_infos = unserialize(get_option('altreport_infos'));
    $altreport_redmine = unserialize(get_option('altreport_redmine'));
    $user_infos = wp_get_current_user();

    /* définiton par défaut du parametrage */

    //todo limiter au meta diférent de fini
    /* récupération des post de type rapport différent de fini et par rapport a la page en cours */



    if (strpos($_SERVER['REQUEST_URI'], 'altreport_report') !== false) {
        header("Access-Control-Allow-Origin: *");
        status_header(200);

        if ((isset($_POST['nonce'])) && (wp_verify_nonce($_POST['nonce'], 'altreport_nonce'))) {
            if (isset($_POST['edit'])) {
                $post = get_post($_POST['edit']);
                $post_content = $post->post_content;
                $post_commentaire = "Commentaire  :[" . $_POST['commentaire'] . "]";
                $post_content .= "<p> ------------------------------- </p> \r\n"
                        . "<p>" . $post_commentaire . "]</p>\r\n";

                $my_post_update['ID'] = $post->ID;
                $my_post_update['post_content'] = $post_content;

                altreport_updatemeta($post->ID, 'altreport_status', $_POST['altreport_status']);

                wp_update_post($my_post_update);
                if (isset($altreport_redmine['key'])) {

                    $status_redmine = altreport_status_redmine($_POST['altreport_status']);
                    $ticket_id = get_post_meta($post->ID, 'altreport_redmine', true);
                    if (isset($ticket_id)) {
                        liaison_redmine($altreport_redmine, $post_commentaire, $ticket_id, $status_redmine);
                    }
                }
            } else {

                $url_absolue = $_POST['REQUEST_URI'];
                $navigateur = $_POST['HTTP_USER_AGENT'];

                $content = '';

                $my_post = array(
                    'post_title' => sanitize_text_field(wp_strip_all_tags($_POST['issue']['subject'])),
                    'post_content' => sanitize_text_field($content),
                    'post_status' => 'publish', //new //ok //fin
                    'post_type' => 'rapport',
                    'post_excerpt' => sanitize_text_field($_POST['issue']['description']),
                );

                if (isset($user_infos) && $user_infos->ID != 0) {
                    $my_post['post_author'] = $user_infos->ID;
                }


                $post_id = wp_insert_post($my_post);
                echo $post_id . get_current_blog_id();

                $content .= '<p>' . __("Navigateur", 'alt_report') . ' :[' . $navigateur . "]</p>\r\n";
                $content .= '<p>' . __("Url du bug", 'alt_report') . ' :[<a href="' . $url_absolue . '">' . $url_absolue . "</a>]</p>\r\n";
                $content .= '<p>' . __("Largeur fenêtre", 'alt_report') . ' :[' . $_POST['innerWidth'] . "px]</p> \r\n";
                $content .= '<p>' . __("Hauteur fenêtre", 'alt_report') . ' :[' . $_POST['innerHeight'] . "px]</p> \r\n";
                $content .= '<p>' . __("Impression écran", 'alt_report') . ': <img src="' . ALTREPORT_LOAD_URL . $post_id . get_current_blog_id() . '.png"></p>';
                $content .= '<p>' . __("Description", 'alt_report') . ' :[' . $_POST['issue']['description'] . "]</p>\r\n";


                if (isset($altreport_redmine['key'])) {
                    $content .= liaison_redmine($altreport_redmine, $content, $post_id, '');
                }
                $my_post['ID'] = $post_id;
                $my_post['post_content'] = $content;
                wp_update_post($my_post);
                if (isset($altreport_infos['default_status']) && $altreport_infos['default_status'] != '') {
                    add_post_meta($post_id, 'altreport_status', $altreport_infos['default_status'], true);
                }
                add_post_meta($post_id, 'altreport_urlbug', sanitize_text_field($url_absolue), true);
                if (isset($_POST['email_user']) && $_POST['email_user'] != '') {
                    if (filter_var($_POST['email_user'], FILTER_VALIDATE_EMAIL)) {
                        add_post_meta($post_id, 'altreport_email_user', sanitize_text_field($_POST['email_user']), true);
                        wp_mail($_POST['email_user'], 'Rapport :' . sanitize_text_field($_POST['issue']['subject']), $altreport_infos['rapport_message']);
                    }
                }
            }


            if (isset($altreport_infos['user_email'])) {
                $emails = explode(',', $altreport_infos['user_email']);
                if (!empty($emails)) {
                    foreach ($emails as $email) {
                        add_filter('wp_mail_content_type', 'wpdocs_set_html_mail_content_type');
                        wp_mail($email, 'Rapport :' . sanitize_text_field($_POST['issue']['subject']), $content);
                        remove_filter('wp_mail_content_type', 'wpdocs_set_html_mail_content_type');
                    }
                }
            }
        }
        die();
    }
    if (strpos($_SERVER['REQUEST_URI'], 'altreport_image') !== false) {
        header("Access-Control-Allow-Origin: *");
        status_header(200);
        $imagedata = base64_decode($_POST['imgdata']);
        $filename = trim($_GET['id_post']);
        $file = ALTREPORT_LOAD . $filename . '.png';
        $imageurl = ALTREPORT_LOAD . $filename . '.png';
        file_put_contents($file, $imagedata);
        echo $imageurl;
        die();
    }
    if (strpos($_SERVER['REQUEST_URI'], 'altreport_script') !== false) {
        header("Access-Control-Allow-Origin: *");
        status_header(200);
        require_once 'script.php';
        die();
    }
    if (strpos($_SERVER['REQUEST_URI'], 'altreport_update') !== false) {
        header("Access-Control-Allow-Origin: *");
        status_header(200);
        $altreport_status = unserialize(get_option('altreport_status'));
        $var = get_redmine($altreport_redmine, $type = '');
        $exclude = array();
        foreach ($var->issues as $issue) {

            $args['post_type'] = 'rapport';
            $args['meta_query'] = array(
                array(
                    'key' => 'altreport_redmine',
                    'value' => $issue->id,
                    'compare' => '=',
                )
            );

            $status_redmine = '';
            $max = 0;
            $query = new WP_Query($args);
            if (!empty($query->posts)) {
                foreach ($altreport_status as $key => $value) {
                    if (isset($altreport_redmine['status' . $key])) {
                        if ($altreport_redmine['status' . $key] == $issue->status->id) {
                            $status_redmine = (isset($altreport_redmine['status' . $key])) ? $altreport_redmine['status' . $key] : '';
                        }
                    }
                    $max++;
                }

                if ($status_redmine == '') {
                    $array_status['value'] = sanitize_text_field(strtolower(trim($issue->status->name)));
                    $array_status['label'] = sanitize_text_field($issue->status->name);
                    $array_status['picto'] = sanitize_text_field('dashicons-update');
                    $array_status['redmine'] = $issue->status->id;

                    array_push($altreport_status, $array_status);

                    $new_value = serialize($altreport_status);

                    update_option('altreport_status', $new_value);

                    $altreport_redmine['status' . $max] = $issue->status->id;

                    $new_value = serialize($altreport_redmine);

                    update_option('altreport_redmine', $new_value);
                    //todo ajouter 
                }
                altreport_updatemeta($query->posts[0]->ID, 'altreport_status', $issue->status->id);
                array_push($exclude, $query->posts[0]->ID);
            }
        }



        unset($args['meta_query']);
        if (!empty($exclude)) {
            $args['exclude'] = array_values($exclude);
        }
        $query = new WP_Query($args);
        if (!empty($query->posts)) {
            foreach ($query->posts as $value) {
                altreport_updatemeta($value->ID, 'altreport_status', $altreport_infos['default_status'], true);
            }
        }
        //todo pour tous les post non fermer et qui ne sont pas avec les précédent 
        die();
    }
}

add_action('init', 'altreport_create_post_type_rapport');

/**
 * Create a post-report type Rapport
 */
function altreport_create_post_type_rapport() {
    $labels = array(
        "name" => __('Report tickets'),
        "singular_name" => __('Rapport'),
    );

    $args = array(
        "label" => __('Rapports'),
        "labels" => $labels,
        "description" => "",
        "public" => false,
        "publicly_queryable" => false,
        "show_ui" => true,
        "show_in_rest" => false,
        "rest_base" => "",
        "has_archive" => false,
        "show_in_menu" => true,
        'menu_icon' => 'dashicons-tickets',
        "exclude_from_search" => true,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array("slug" => "Rapport", "with_front" => true),
        "query_var" => true,
        "supports" => array("title", "editor", "excerpt"),);
    register_post_type("rapport", $args);
}

add_action('add_meta_boxes', 'altreport_add_post_meta_boxes');

function altreport_add_post_meta_boxes() {

    add_meta_box(
            'altreport-bugurl', // Unique ID
            'Parametrage', 'altreport_post_bug_meta_box', // Callback function
            'rapport', // Admin page (or post type)
            'side', // Context
            'default'         // Priority
    );

    add_meta_box(
            'altreport-comment', // Unique ID
            'commentaires', 'altreport_post_comment_meta_box', // Callback function
            'rapport', // Admin page (or post type)
            'normal', // Context
            'default'         // Priority
    );
}

/**
 * Allows to display on the post, the fields.
 * @param type $object
 * @param type $box
 */
function altreport_post_bug_meta_box($object, $box) {
    $status = esc_attr(get_post_meta($object->ID, 'altreport_status', true));
    $altreport_email_user = get_post_meta($object->ID, 'altreport_email_user', true);
    ?>
    <p>
        <?php echo altreport_list_status($status); ?>
    </p>
    <p><label for="altreport_urlbug"><?php _e("Url du bug", 'alt_report') ?></label></p>          
    <p><input class="widefat" type="text" name="altreport_urlbug" id="altreport_urlbug" placeholder="Bug url" value="<?php echo esc_attr(get_post_meta($object->ID, 'altreport_urlbug', true)); ?>" size="30" /></p>
    <p><label for="altreport_temps"><?php _e("Temps", 'alt_report') ?></label></p>          
    <p><input class="widefat" type="text" name="altreport_temps" id="altreport_urlbug" placeholder="Temps en heure" value="<?php echo esc_attr(get_post_meta($object->ID, 'altreport_temps', true)); ?>" size="30" /></p>


    <input type="hidden" name="altreport_parametres" value="<?php echo wp_create_nonce('altreport_parametres') ?>"/>
    <?php if ($altreport_email_user != '') { ?>
        <p><label for="altreport_email_user"><?php _e("Email visiteur", 'alt_report') ?></label></p> 
        <p><input class="widefat" type="text" name="altreport_email_user" id="altreport_email_user" placeholder="Email visiteur" value="<?php echo esc_attr($altreport_email_user); ?>" size="30" /></p>
        <?php
    }
}

function altreport_post_comment_meta_box($object, $box) {
    ?>
    <p><label for="altreport_comment"><?php _e("Ajouter un commentaire", 'alt_report') ?></label></p>          
    <p><input class="widefat" type="text" name="altreport_comment" id="altreport_urlbug" placeholder="votre commentaire" value="" /></p>


    <p><?php _e("Les commentaires", 'alt_report') ?></p>
    <table>
        <?php
        $commentaires = get_post_meta($object->ID, 'altreport_comment');
        foreach ($commentaires as $commentaire) {
            $commentaire_clean = unserialize($commentaire);
            if (!isset($commentaire_clean['message']) || $commentaire_clean['message'] != '') {
                delete_post_meta($object->ID, 'altreport_comment', $commentaire);
                continue;
            }
            ?>
            <tr>
                <td><?php echo $commentaire_clean['message'] ?></td>
                <td><?php echo $commentaire_clean['auteur'] ?></td>
                <td><?php echo date("d/m/Y", $commentaire_clean['date']) ?></td>
                <td>Supprimer <input type="checkbox" name="altreport_sup_comment" value='<?php echo $commentaire ?>' /></td>
            </tr>
        <?php }
        ?>
    </table>
    <?php
}

add_action('save_post', 'altreport_save_post_class_meta', 13, 2);

/**
 * Registers post meta to post modification
 * @param type $post_id
 * @param type $post
 */
function altreport_save_post_class_meta($post_id, $post) {
    if ((isset($_POST['altreport_parametres'])) && (wp_verify_nonce($_POST['altreport_parametres'], 'altreport_parametres'))) {
        /* Verify the nonce before proceeding. */
        if ((isset($_POST['altreport-bugurl'])) || (isset($_POST['altreport_status']))) {

            $status_redmine = '';
            if (isset($_POST['altreport_status'])) {
                $status_redmine = sanitize_text_field($_POST['altreport_status']);
                altreport_updatemeta($post_id, 'altreport_status', $status_redmine);
            }
            if (isset($_POST['altreport_urlbug'])) {
                altreport_updatemeta($post_id, 'altreport_urlbug', sanitize_text_field($_POST['altreport_urlbug']));
            }

            if (isset($_POST['altreport_temps'])) {
                altreport_updatemeta($post_id, 'altreport_temps', sanitize_text_field($_POST['altreport_temps']));
            }
            if (isset($_POST['altreport_email_user'])) {
                altreport_updatemeta($post_id, 'altreport_email_user', sanitize_text_field($_POST['altreport_email_user']));
            }
            $altreport_redmine = unserialize(get_option('altreport_redmine'));
            if (isset($altreport_redmine['key'])) {
                $status_redmine = altreport_status_redmine($_POST['altreport_status']);
                $ticket_id = get_post_meta($post_id, 'altreport_redmine', true);
                /* si le ticket existe alors je met a jours le status du ticket redmine */
                if (isset($ticket_id) && $ticket_id != '') {
                    $content = 'Modification du statut en ' . altreport_label_status($status_redmine, 0);
                    liaison_redmine($altreport_redmine, $content, $ticket_id, $status_redmine);
                } else {
                    $content_post = get_post($post_id);
                    $content = $content_post->post_content;
                    $_POST['REQUEST_URI'] = $_POST['altreport_urlbug'];
                    $_POST['issue']['subject'] = $content_post->post_title;
                    liaison_redmine($altreport_redmine, $content, $post_id, '');
                }
            }
        }
        if (isset($_POST['altreport_comment'])) {
            $user_infos = wp_get_current_user();
            $comment['message'] = sanitize_text_field($_POST['altreport_comment']);
            $comment['auteur'] = $user_infos->user_login;
            $comment['date'] = time();
            add_post_meta($post_id, 'altreport_comment', serialize($comment));
        }
        if (isset($_POST['altreport_sup_comment'])) {
            delete_post_meta($post_id, 'altreport_comment', $_POST['altreport_sup_comment']);
        }
    }
}

/**
 * Does the update or add test for meta posts
 * @param type $post_id
 * @param type $meta_key
 * @param type $new_meta_value
 */
function altreport_updatemeta($post_id, $meta_key, $new_meta_value) {
    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta($post_id, $meta_key, true);

    /* If a new meta value was added and there was no previous value, add it. */
    if ($new_meta_value && '' == $meta_value)
        add_post_meta($post_id, $meta_key, $new_meta_value, true);

    /* If the new meta value does not match the old value, update it. */
    elseif ($new_meta_value && $new_meta_value != $meta_value)
        update_post_meta($post_id, $meta_key, $new_meta_value);

    /* If there is no new meta value but an old value exists, delete it. */
    elseif ('' == $new_meta_value && $meta_value)
        delete_post_meta($post_id, $meta_key, $meta_value);
}

add_filter('manage_edit-rapport_columns', 'altreport_columns');

/**
 * Add new columns
 * @global type $wp_query
 * @param type $columns
 * @return type
 */
function altreport_columns($columns) {
    global $wp_query;
    if (isset($wp_query->query) && ($wp_query->query['post_type'] == "rapport")) {
        $altreport_redmine = unserialize(get_option('altreport_redmine'));
        $columns['cb'] = __('<input type="checkbox" />', 'alt_report');
        $columns['title'] = __('Title', 'alt_report');
        $columns['status'] = __('Statut', 'alt_report');
        $columns['temps'] = __('Temps', 'alt_report');
        if ($altreport_redmine['actif'] == 'on') {
            $columns['redmine'] = __('Redmine', 'alt_report');
        }
        $columns['url_bug'] = __('Url bug', 'alt_report');
        $columns['date'] = __('Date', 'alt_report');
    }
    return $columns;
}

add_filter('manage_edit-rapport_sortable_columns', 'altreport_sortable_columns');

function altreport_sortable_columns($columns) {
    $altreport_redmine = unserialize(get_option('altreport_redmine'));
    if (isset($altreport_redmine['key'])) {
        $columns['redmine'] = 'redmine';
    }
    $columns['url_bug'] = 'url_bug';
    return $columns;
}

add_action('manage_posts_custom_column', 'altreport_columns_values', 10, 2);

/**
 * Manage table view
 * @global type $post
 * @global type $wp_query
 * @param type $column
 * @param type $post_id
 */
function altreport_columns_values($column, $post_id) {
    $altreport_redmine = unserialize(get_option('altreport_redmine'));
    global $post, $wp_query;
    if (isset($wp_query->query) && ($wp_query->query['post_type'] == "rapport")) {

        switch ($column) {

            /* If displaying the 'duration' column. */
            case 'status' :
                $status = esc_attr(get_post_meta($post->ID, 'altreport_status', true));
                echo altreport_label_status($status, 1);
                break;
            case 'redmine' :
                if ($altreport_redmine['actif'] == 'on') {
                    $ticket_id = get_post_meta($post->ID, 'altreport_redmine', true);
                    if (isset($ticket_id) && $ticket_id != '') {
                        echo '<a href="' . $altreport_redmine['protocol'] . '://' . $altreport_redmine['adresse'] . '/issues/' . $ticket_id . '"  target="_blank">#' . $ticket_id . '</a>';
                    } else {
                        if (isset($_GET['altreport_refresh_post']) && ($_GET['altreport_refresh_post'] == $post->ID)) {
                            $content = $post->post_content;
                            $_POST['REQUEST_URI'] = get_post_meta($post->ID, 'altreport_urlbug', true);
                            $_POST['issue']['subject'] = $post->post_title;
                            echo liaison_redmine($altreport_redmine, $content, $post_id, '');
                        } else {
                            echo'<form method="GET"><input type="hidden" value="' . $post->ID . '" name="altreport_refresh_post"><input type="submit" value="synchro"></form>';
                        }
                    }
                }
                break;
            case 'url_bug' :
                $altreport_urlbug = get_post_meta($post->ID, 'altreport_urlbug', true);
                if (isset($altreport_urlbug)) {
                    echo '<a href="' . $altreport_urlbug . '" target="_blank">' . $altreport_urlbug . '</a>';
                }
                break;
            case 'temps' :
                $altreport_temps = get_post_meta($post->ID, 'altreport_temps', true);
                if (isset($altreport_temps)) {
                    echo '' . $altreport_temps . '';
                }
                break;

            default :
                break;
        }
    }
}

/**
 * defining the filter that will be used so we can select posts by 'author'
 * @global type $wp_query
 */
function custom_restrict_manage_posts() {
    global $wp_query;

    if (isset($wp_query->query) && ($wp_query->query['post_type'] == "rapport")) {
        //rapport
        $altreport_status = unserialize(get_option('altreport_status'));
        //todo prévoir un asset avec l'inclusion du js et et du css pour la partie admin et pour la partie front
        ?>      
        <label for="filter-by-status" class="screen-reader-text"></label>
        <select name="meta_status" id="filter-by-status">
            <option value=""><?php _e("Tout les statut", 'alt_report') ?></option>
            <?php
            foreach ($altreport_status as $value) {
                ?>
                <option value="<?php echo $value['value'] ?>" <?php echo (isset($_GET['meta_status']) && ($_GET['meta_status'] == $value['value'])) ? 'selected' : '' ?>><?php echo $value['label'] ?></option>
                <?php
            }
            ?>
        </select>
        <?php
    }
}

add_action('restrict_manage_posts', 'custom_restrict_manage_posts');

/**
 * 
 * @global type $pagenow
 * @global type $post_type
 * @param type $vars
 * @return type
 */
function custom_request_query($vars) {
    global $pagenow, $post_type;
    $possible_post_types = array('rapport');
    if (!empty($pagenow) && $pagenow == 'edit.php' && in_array($post_type, $possible_post_types)) {
        if (!empty($_GET['meta_status'])) {
            $meta_status = $_GET['meta_status'];
            $vars['meta_key'] = 'altreport_status';
            $vars['meta_value'] = $meta_status;
        }
    }
    return $vars;
}

add_filter('request', 'custom_request_query');

