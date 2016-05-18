<?php

	// If no ID was passed...
	if ( ! isSet ( $_GET['id'] ) ) {
	
		// Show an error message.
		echo '<h2>Error</h2>';
		echo 'No ID was specified.';
		die;	
	
	}

	// Load the settings.
	require_once ( 'settings.php' );

	// Initialize a curl session.
	$ch = curl_init();
	
	// Specify the type of HTTP request that we'll be sending.
	// In this case, we'll be sending GET requests.
	curl_setopt( $ch, CURLOPT_HTTPGET, 1 );		
	
	// Request that the raw output be returned.
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );	
	
	// Specify a timeout value (in seconds).
	curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );	
	
	// Create an array to use to pass parameters via HTTP headers.
	$http_headers = array();						
	
	// We need to pass the AirTable API key as an HTTP header, so add it to the array.
	$http_headers[] = 'Authorization: Bearer ' . AIRTABLE_API_KEY;		
	
	// Specify the HTTP headers to send.
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $http_headers );	
	
	// Build the URL to use to get the next batch of records.
	$airtable_url = AIRTABLE_API_URL . AIRTABLE_APP_ID;
	$airtable_url .= '/Artists/' . $_GET['id'];	
	
	// Specify the URL to call.
	curl_setopt( $ch, CURLOPT_URL, $airtable_url );								

	// Execute the request.
	$response_json = curl_exec( $ch );

	// If there was a curl error encountered while making the call...
	if ( curl_errno( $ch ) != 0 ) {

		// Show an error message with the CURL error code.
		// For a complete list of error codes, see:
		// http://curl.haxx.se/libcurl/c/libcurl-errors.html 
		echo '<h2>CURL Error</h2>';
		echo 'Code: ' . curl_errno( $ch );
		die;

	}

	// If Airtable returned an error...
	if ( $response_json == 404 ) {

		// Show an error message.
		echo '<h2>Airtable Error</h2>';
		echo 'Code: ' . $response_json . '<br />';
		echo 'Record ID ' . $_GET['id'] . ' is invalid.';
		die;			

	}	

	// Decode the JSON-formatted response that was received from the Airtable API.
	$airtable_response = json_decode( $response_json, TRUE );

	// If the Airtable API returned an error...
	if ( array_key_exists ( 'error', $airtable_response ) ) {

		// Show an error message.
		echo '<h2>Airtable Error</h2>';
		echo 'Type: ' . $airtable_response['error']['type'] . '<br />';
		echo 'Message: ' . $airtable_response['error']['message'] . '<br />';
		die;			

	}
	
		
	
	// Create a pointer to the fields array, for convenience.
	$fields = $airtable_response['fields'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title>Artist Profile: <?php echo $fields['Name']; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" media="all" href="stylesheet.css" />		
	</head>

	<body>	
	
		<?php
		
			echo '<div class="page-wrapper">';
			
				// Navigation back to the list view.
				echo '<div class="nav-wrapper"><a href="index.php">&lt Return to Artist List</a></div>';
		
				// Display the artist's name.
				echo '<div class="header-wrapper">';
				echo '<h1>' .  $fields['Name'] . '</h1>';
				echo '</div>';
		
				echo '<div class="content-wrapper">';
			
					// If there is a bio for the artist...
					if ( $fields[ 'Bio' ] != '' ) {
			
						echo $fields[ 'Bio' ];		
			
					}
	
					// If genres are available...
					if ( array_key_exists ( 'Genre', $fields ) ) {
			
						echo '<h2>Genres</h2>';
						echo '<ul>';
						foreach ( $fields[ 'Genre' ] as $genre ) {
							echo '<li>' . $genre . '</li>';
						}		
						echo '</ul>';
			
					}	
			
					// If attachments are available...
					if ( array_key_exists ( 'Attachments', $fields ) ) {
			
						echo '<h2>Artwork</h2>';
			
						// Show thumbnails for each attachment...
						foreach ( $fields[ 'Attachments' ] as $attachment ) {
							// Here, we're displaying the larger of the two thumbnails, 
							// and linking to the original (full-size) image.			
							echo '<a href="' . $attachment['url'] . '" target="_new">';
							echo '<img src="' . $attachment['thumbnails']['large']['url'] . '" style="margin-right: 12px; max-width: 50px;" border="none"></a>';
						}			
			
					}		
								
				echo '</div>';		

			echo '</div>';		
		
		?>		
		
	</body>
	
</html>