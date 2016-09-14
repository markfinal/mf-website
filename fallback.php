<?php
register_shutdown_function( "fatal_handler" );

function fatal_handler()
{
  $errfile = "unknown file";
  $errstr  = "shutdown";
  $errno   = E_CORE_ERROR;
  $errline = 0;

  $error = error_get_last();

  if (!is_null($error))
  {
    $errno   = $error["type"];
    $errfile = $error["file"];
    $errline = $error["line"];
    $errstr  = $error["message"];
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    echo json_encode($errstr);
  }
}

function send_email($to, $subject, $message, $attachments=array())
{
	$from = 'Mark Final <mark@markfinal.me.uk>';

	$boundary = uniqid('np');

	// note: for both header and message body, the EOL characters must be enclosed in
	// double quotes, in order for PHP to interpret these as carriage return and line feeds

	/* this is an alternative multipart message
	// the header
	$header = array();
	$header[] = 'MIME-Version: 1.0';
	$header[] = 'Content-Type: multipart/alternative; boundary="'.$boundary.'"';
	$header[] = 'From: '.$from;
	$header[] = 'Reply-To: '.$from;
	$header[] = 'X-Mailer: PHP/'.phpversion();

	// the message body
	$message = 'This is a MIME encoded message.'."\r\n\r\n";
	$message .= '--'.$boundary."\r\n";
	$message .= 'Content-type: text/plain;charset=utf-8'."\r\n";
	$message .= 'Content-Transfer-Encoding: 7bit'."\r\n";
	$message .= "\r\n";
	$message .= 'Some plain text here'."\r\n\r\n";
	$message .= '--'.$boundary."\r\n";
	$message .= 'Content-Type: text/html;charset=utf-8'."\r\n";
	$message .= 'Content-Transfer-Encoding: 7bit'."\r\n";
	$message .= "\r\n";
	$message .= '<html><body>Hello world</body></html>'."\r\n\r\n";
	$message .= '--'.$boundary.'--';
	*/
	$header = array();
	$header[] = 'MIME-Version: 1.0';
	$header[] = 'Content-Type: multipart/mixed; boundary="'.$boundary.'"';
	$header[] = 'From: '.$from;
	$header[] = 'Reply-To: '.$from;
	$header[] = 'X-Mailer: PHP/'.phpversion();

	// the message body
	$message = 'This is a MIME encoded message.'."\r\n\r\n";
	$message .= '--'.$boundary."\r\n";
	$message .= 'Content-type: text/plain;charset=utf-8'."\r\n";
	$message .= 'Content-Transfer-Encoding: 7bit'."\r\n";
	$message .= "\r\n";
	$message .= $message."\r\n\r\n";
	foreach ($attachments as $key => $value)
	{
		$message .= '--'.$boundary."\r\n";
		$message .= 'Content-Type: text/plain;name="'.$key.'"'."\r\n";
		$message .= 'Content-Transfer-Encoding: base64'."\r\n";
		$message .= 'Content-Disposition: attachment'."\r\n";
		$message .= "\r\n";
		$content = chunk_split(base64_encode($value));
		$message .= $content."\r\n\r\n";
	}
	$message .= '--'.$boundary.'--';

	// actually send the mail
	$mail_sent = mail($to, $subject, $message, implode("\r\n", $header), '-v');

	error_log($mail_sent ? 'Mail was sent' : 'Mail failed, '.error_get_last());
}

function user_registration()
{
	$password = explode("\n", file_get_contents('phppasswd'));

	$connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
	$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// determine if there is an existing user
	$matching_users = $connection->prepare('SELECT COUNT(*) FROM User WHERE email=:email');
	$matching_users->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
	$matching_users->execute();
	if (0 == $matching_users->fetchColumn())
	{
		// generate a public/private key for the new user
		$private_key_resource = openssl_pkey_new();
		openssl_pkey_export($private_key_resource, $private_key);

		// add the user into the db
		/* TEMPORARILY DISABLE DURING MAIL TESTS
		$insert_user = $connection->prepare('INSERT INTO User (email,privatekey) VALUES (:email,:private_key)');
		$insert_user->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
		$insert_user->bindParam(':private_key', $private_key, PDO::PARAM_STR);
		$insert_user->execute();
		*/

		$private_key_details = openssl_pkey_get_details($private_key_resource);
		$public_key = $private_key_details['key'];

		// send the public key as an email attachment
		send_email($_POST['email'], 'Access', 'Please find attached...', array('publickey.txt'=>$public_key));

		header('Content-Type: application/json', true, 201);

		$response = array();
		$response['success'] = 'yes';
		$response['email'] = $_POST['email'];
		$response['publickey'] = $public_key;
		echo json_encode($response);
	}
	else
	{
		header('Content-Type: application/json', true, 500);
		$response = array();
		$response['success'] = 'no';
		$response['email'] = $_POST['email'];
		echo json_encode($response);
	}

	$connection = null;
}

if ($_SERVER['REQUEST_URI'] === '/api/v1/register')
{
	user_registration();
}
else
{
    $response = array();
    $response['errormessage'] = 'Unrecognized path: '.$_SERVER['REQUEST_URI'];

    header($_SERVER['SERVER_PROTOCOL'].' 404 not found', true, 404);
    header('Content-Type: application/json', true);
    echo json_encode($response);
}
?>
