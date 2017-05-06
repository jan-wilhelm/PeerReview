<?php
require_once $SENDMAILPATH;

$transport = Swift_SmtpTransport::newInstance('ssl://smtp.gmail.com', 465)
    ->setUsername('peer.review.ck@gmail.com')
    ->setPassword('2ZEnmrzPZYSVZRs2mUvX');
$mailer = Swift_Mailer::newInstance($transport);

function sendMail($receiver, $subject, $body) {
	$message = Swift_Message::newInstance($subject)
	    ->setFrom(array('peer.review.ck@gmail.com' => 'CK Peer Review'))
	    ->setTo($receiver)
	    ->setBody($body, 'text/html');
	return $GLOBALS["mailer"]->send($message);
}

?>