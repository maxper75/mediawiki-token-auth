<?php
require_once "$IP/extensions/TokenAuth/TokenAuth.php";
$wgTokenAuthUsers[] = [
	'user'    => 'AuthenticatedUser',
	'authTokenName' => "authToken",
	'psk' => 'prefixAuth',
	'lifetime' => 600
];
?>
