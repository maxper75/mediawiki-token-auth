********************************************************************************
* This script helps to generate a simple file that contains the lines          * 
* that can be used to configure this extension easily.                         * 
*                                                                              *
* There will be some easy questions that will be asked to create this config   *
* and it will be saved in a file that is named config.php                      *
* Keys generated are completely random, so everything should work just fine    *
* SECURITY WARNING!!!!!                                                        *
* REMEMBER TO REMOVE THE config.php FILE THAT GETS GENERATED ONCE YOU COPIED   *
* SETTINGS IN LocalSettings.php, AS INSIDE THERE'S PRIVATE AND PUBLIC KEY FOR  *
* TOKEN DECRYPTION                                                             * 
*                                                                              * 
********************************************************************************
<?php

function ob_file_callback($buffer)
{
  global $ob_file;
  fwrite($ob_file,$buffer);
}

//ini_set('include_path','/var/www/wikisicaf:'.ini_get('include_path'));
require ("phpseclib/autoload.php");

use \phpseclib\Crypt\RSA;

$rsa = new RSA();
$rsa->setPrivateKeyFormat(RSA::PRIVATE_FORMAT_XML);
$rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_XML);
$generatedKey = $rsa->createKey();
$configFile="config.php";
$authUser="TokenUser";
$authTokenName="auth_token";
$authPSK="4uthPSK!";
$tokenLifetime=600;
echo "Config filename [".$configFile."]: ";
fscanf(STDIN, "%s\n", $configFile);
if(strlen($configFile)==0)
	$configFile="config.php";
echo "Authenticated User [".$authUser."]: ";
fscanf(STDIN, "%s\n", $authUser);
if(strlen($authUser)==0)
	$authUser="TokenUser";
echo "Authentication Token Name [".$authTokenName."]: ";
fscanf(STDIN, "%s\n", $authTokenName);
if(strlen($authTokenName)==0)
	$authTokenName="auth_token";
echo "Authentication PSK [".$authPSK."]: ";
fscanf(STDIN, "%s\n", $authPSK);
if(strlen($authPSK)==0)
	$authPSK="4uthPSK!";
echo "Token lifetime [".$tokenLifetime."]: ";
fscanf(STDIN, "%d\n", $tokenLifetime);
if(strlen($tokenLifetime)==0)
	$tokenLifetime=600;

$ob_file = fopen($configFile,'w');
ob_start('ob_file_callback');
echo "<?php\n";
?>
//Remember to uncomment the line below in LocalConfig.php!!!
//require_once "$IP/extensions/TokenAuth/TokenAuth.php";
$wgTokenAuthUsers[] = [
	'user'    => '<?php echo $authUser;?>',
	'authTokenName' => "<?php echo $authTokenName;?>",
	'psk' => '<?php echo $authPSK; ?>',
	'lifetime' => <?php echo $tokenLifetime; ?>,
	'privkey' => '<?php echo $generatedKey['privatekey']?>',
	'pubkey' => '<?php echo $generatedKey['publickey']?>'
];
<?php
echo "?>";
ob_end_flush();
?>
