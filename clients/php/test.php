<?php
require "vendor/autoload.php";
require "TokenGenerator.class.php";
$tg = new  TokenGenerator();
$tg->setPsk("MyWonderfulPSK");
$rsaKey=file_get_contents("pubkey.xml");
$tg->setPublicKey($rsaKey);
$urlEncodedToken = $tg->getToken();
echo $urlEncodedToken;
 ?>
