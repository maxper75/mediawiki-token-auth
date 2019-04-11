<?php
ini_set('include_path','/var/www/wikisicaf:'.ini_get('include_path'));
require ("vendor/autoload.php");

use \phpseclib\Crypt\RSA;

$rsa = new RSA();
$rsa->setPrivateKeyFormat(RSA::PRIVATE_FORMAT_XML);
$rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_XML);
print_r($rsa->createKey());
?>
