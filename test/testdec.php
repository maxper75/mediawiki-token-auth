<?php

ini_set('include_path',ini_get('include_path').":/var/www/wikisicaf");

require "vendor/autoload.php";
use \phpseclib\Crypt\RSA;
$encrypted = base64_decode(urldecode('RHOpplfjJyNjJiGeWgOlkSB927z1%2ByOuv1fZlK30GZB4LjzixRNH6qYbZOYghADptnkrPUPZ7oVqBElBTCQ38F2K0%2Biv1zR7zEMVGGzE3wDZcwtlcfh8BS%2BDEPxCaycnvbAeKmaGQJ%2B%2FXNPJoW0o1T%2FkNLCFizNWyikB9LMe3NE%3D'));

$decryptrsa = new RSA();
$decryptrsa->setPrivateKeyFormat(RSA::PRIVATE_FORMAT_XML);
if($decryptrsa->loadKey(file_get_contents(__DIR__."/privkey.xml"))){
echo $decryptedToken=$decryptrsa->decrypt($encrypted);

}



?>
