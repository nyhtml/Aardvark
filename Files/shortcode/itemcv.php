<?php
/**
 * itemcv.php — WP Resume Shortcodes compatibility shortcode for Aardvark
 * Version: 1.1.0
 * Author: Stephan Pringle
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function my_itemcv_shortcode($atts, $content = null) {

    $a = shortcode_atts(array(
        'startdate'     => '',
        'enddate'       => '',
        'line1'         => '',
        'lineseparator' => ' - ',
        'line2'         => '',
    ), $atts);

    // Build the date from the original WP Resume Shortcodes attributes.
    $date = '';

    if ($a['startdate'] !== '' && $a['enddate'] !== '') {
        $date = $a['startdate'] . $a['lineseparator'] . $a['enddate'];
    } elseif ($a['startdate'] !== '') {
        $date = $a['startdate'];
    } elseif ($a['enddate'] !== '') {
        $date = $a['enddate'];
    }

    // Pass the translated attributes to Aardvark's existing cardResume renderer.
    return my_cardresume_shortcode(
        array(
            'jobtitle' => $a['line2'],
            'company'  => $a['line1'],
            'date'     => $date,
        ),
        $content
    );
}
if (get_option('aardvark_itemcv_enabled', false)) {
add_shortcode('itemcv', 'my_itemcv_shortcode');
}
