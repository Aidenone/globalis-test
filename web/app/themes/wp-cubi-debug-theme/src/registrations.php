<?php

namespace Globalis\WP\Test;

define('REGISTRATION_ACF_KEY_LAST_NAME', 'field_64749cfff238e');
define('REGISTRATION_ACF_KEY_FIRST_NAME', 'field_64749d4bf238f');
define('REGISTRATION_ACF_KEY_EVENT_ID', "field_64749cde33fd7");
define('REGISTRATION_ACF_KEY_EMAIL', "field_64749d780cd14");

add_filter('wp_insert_post_data', __NAMESPACE__ . '\\save_auto_title', 99, 2);
add_filter('wp_insert_post_data', __NAMESPACE__ . '\\send_mail_to_attendee', 99, 2);
add_action('edit_form_after_title', __NAMESPACE__ . '\\display_custom_title_field');

function save_auto_title($data, $postarr)
{
    if (! $data['post_type'] === 'registrations') {
        return $data;
    }
    if ('auto-draft' == $data['post_status']) {
        return $data;
    }

    if (!isset($postarr['acf'][REGISTRATION_ACF_KEY_LAST_NAME]) || !isset($postarr['acf'][REGISTRATION_ACF_KEY_FIRST_NAME])) {
        return $data;
    }

    $data['post_title'] = "#" . $postarr['ID'] .  " (" . $postarr['acf'][REGISTRATION_ACF_KEY_LAST_NAME] . " " . $postarr['acf'][REGISTRATION_ACF_KEY_FIRST_NAME] . ")";

    $data['post_name']  = wp_unique_post_slug(sanitize_title(str_replace('/', '-', $data['post_title'])), $postarr['ID'], $postarr['post_status'], $postarr['post_type'], $postarr['post_parent']);

    return $data;
}

function send_mail_to_attendee($data, $postarr)
{
    if (! $data['post_type'] === 'registrations') {
        return $data;
    }
    if ('auto-draft' == $data['post_status']) {
        return $data;
    }

    $attendee_name = $postarr['acf'][REGISTRATION_ACF_KEY_FIRST_NAME].' '.$postarr['acf'][REGISTRATION_ACF_KEY_LAST_NAME];
    $attendee_mail = $postarr['acf'][REGISTRATION_ACF_KEY_EMAIL];

    $ticket_id = get_field('event_pdf_entrance_ticket', $postarr['acf'][REGISTRATION_ACF_KEY_EVENT_ID]);
    $ticket_url = wp_get_attachment_url($ticket_id);
    $event_date = get_field('event_date', $postarr['acf'][REGISTRATION_ACF_KEY_EVENT_ID]);
    $event_time = get_field('event_time', $postarr['acf'][REGISTRATION_ACF_KEY_EVENT_ID]);
    $event_name = get_the_title($postarr['acf'][REGISTRATION_ACF_KEY_EVENT_ID]);

    $admin_mail = get_option('admin_email');

    $subject = "Details of your registration for the event : ".$event_name;
    $message = "Hi ".$attendee_name.", you have been registered to the following event : ".$event_name." @ ".$event_date." ".$event_time;
    $headers = 'From: '. $admin_mail . "\r\n";
    $attachments = $ticket_url;

    wp_mail( $attendee_mail, $subject, $message, $headers, $attachments );

    return $data;
}

function display_custom_title_field($post)
{
    if ($post->post_type !== 'registrations' || $post->post_status === 'auto-draft') {
        return;
    }
    ?>
    <h1><?= $post->post_title ?></h1>
    <?php
}
