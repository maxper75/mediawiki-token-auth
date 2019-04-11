<?php
ini_set('include_path','/var/www/wikisicaf:'.ini_get('include_path'));
require ("vendor/autoload.php");

use \phpseclib\Crypt\RSA;

$rsa = new RSA();

$rsa->loadKey(file_get_contents(__DIR__."/pubkey.xml"));

echo "https://wikisicaf.cafcisl.it/?sicaf_auth=".urlencode(base64_encode($rsa->encrypt("SiC4fAuth!%_%".time())));

?>
