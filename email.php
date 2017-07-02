<?php 
require_once("vendor/autoload.php");




//Create a new PHPMailer instance
		$mail = new PHPMailer;

		$mail->SMTPOptions = array(
		    'ssl' => array(
		        'verify_peer' => false,
		        'verify_peer_name' => false,
		        'allow_self_signed' => true
		    )
		);

		$mail->CharSet = 'UTF-8';

		//Tell PHPMailer to use SMTP
		$mail->IsSMTP();

		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 2;

		//Ask for HTML-friendly debug output
		$mail->Debugoutput = 'html';

		//Set the hostname of the mail server
		$mail->Host = 'smtp.gmail.com';

		// use
		// $mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6

		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$mail->Port = 587;

		//Set the encryption system to use - ssl (deprecated) or tls
		$mail->SMTPSecure = 'tls';

		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$mail->Username = 'username';

		//Password to use for SMTP authentication
		$mail->Password = 'senha';

		//Set who the message is to be sent from
		$mail->SetFrom('username', 'Elnatã - Teste PHPMailer');

		//Set an alternative reply-to address
		// $mail->addReplyTo('replyto@example.com', 'First Last');

		//Set who the message is to be sent to
		$mail->AddAddress('dest', 'Elnatã Destinatário PHPMailer');

		//Set the subject line
		$mail->Subject = 'Teste PHPMailer (Gmail)';

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$mail->MsgHTML(file_get_contents('email.html'), dirname(__FILE__));

		//Replace the plain text body with one created manually
		$mail->AltBody = 'This is a plain-text message body';

		//Attach an image file
		// $mail->addAttachment('images/phpmailer_mini.png');

		if (!$mail->Send()) {
		    echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
		    echo "<br>Mensagem enviada!";
		}

		
 ?>