<?php

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;

require_once __DIR__ . '/src/schema.php';
require_once __DIR__ . '/src/registrations.php';

add_action( 'admin_post_export_attendees', 'prefix_admin_export_attendees' );

function prefix_admin_export_attendees() {
	global $wpdb;

	if(isset($_POST['post_id']) && check_admin_referer('export_attendees')) {
		$event_id = $_POST['post_id'];
		$writer = new \OpenSpout\Writer\XLSX\Writer();
		$writer->openToBrowser('export-'.$event_id.'.xlsx');

		$values = ['Nom', 'Prénom', 'Email', 'Téléphone'];
		$rowFromValues = Row::fromValues($values);
		$writer->addRow($rowFromValues);

	    $sql_query = $wpdb->prepare("SELECT post_id FROM %i WHERE `meta_key` = 'registration_event_id' AND `meta_value` = %d", $wpdb->postmeta, $event_id);
	    $results = $wpdb->get_results($sql_query, ARRAY_A);

	    foreach ($results as $registration_id) {
	    	$firstname = get_field("registration_first_name", $registration_id['post_id']);
	    	$lastname = get_field("registration_last_name", $registration_id['post_id']);
	    	$email = get_field("registration_email", $registration_id['post_id']);
	    	$phone = get_field("registration_phone", $registration_id['post_id']);

	    	$values = [$lastname, $firstname, $email, $phone];
			$rowFromValues = Row::fromValues($values);
			$writer->addRow($rowFromValues);
	    }

		$writer->close();
	}
}