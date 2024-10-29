<?php
/**
 * Les statistiques
 */
if (!defined('ABSPATH'))
    exit;



$report = array(
    'posts_per_page' => '-1',
    'post_type' => 'rapport',
);

$post_report = new WP_Query($report);
$type_statut = array();
$sum_nb = $sum_tps = 0;
foreach ($post_report->posts as $value) {
    $status = esc_attr(get_post_meta($value->ID, 'altreport_status', true));
    $temps = esc_attr(get_post_meta($value->ID, 'altreport_temps', true));
    if (isset($array_status[$status])) {
        $array_status[$status]['nb'] ++;
        $array_status[$status]['nb'] += $temps;
    } else {
        $array_status[$status]['nb'] = 1;
        $array_status[$status]['temps'] = $temps;
    }
    $sum_nb++;
    $sum_tps += $temps;
}
?>
<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        text-align: left;
        padding: 8px;
    }

    tr:nth-child(even) {background-color: #f2f2f2;}
</style>
<div class="wrap">
    <h2><?php _e('Les statistiques', 'alt_report') ?></h2>
    <table>
        <tr>
            <th>
                etat
            </th>
            <th>
                nombre
            </th>
            <th>
                temps
            </th>
        </tr>

        <?php
        foreach ($array_status as $key => $value) {
            ?>
            <tr>
                <td><?php echo altreport_label_status($key, 1); ?></td>
                <td><?php echo $value['nb']; ?></td>
                <td><?php echo $value['temps']; ?></td>
            </tr>
            <?Php
        }
        ?>
        <tr>
            <th>
                Total
            </th>
            <th>
                <?php echo $sum_nb ?>
            </th>
            <th>
                <?php echo $sum_tps ?>
            </th>
        </tr>
    </table>
</div>

