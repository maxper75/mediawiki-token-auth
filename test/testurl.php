********************************************************************************
* This script can be used to test that settings generated with config.php are  * 
* Working properly, so before you remove that file, you can generate here a URL*
* And test it with the browser                                                 *
* Once done remember to remove config.php!!!                                   *
********************************************************************************
<?php

require ("phpseclib/autoload.php");
require("../config.php");
use \phpseclib\Crypt\RSA;

$rsa = new RSA();

$rsa->loadKey($wgTokenAuthUsers[0]['privkey']);

echo "https://mysuper.wiki.com/?".$wgTokenAuthUsers[0]['authTokenName']."=".urlencode(base64_encode($rsa->encrypt($wgTokenAuthUsers[0]['psk']."%_%".time())));

?>
