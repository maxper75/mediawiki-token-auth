<?php

/**
 * This class is used to generate, depending from the exchanged settings, a valid token that can be authenticated by Mediawiki
*/
require "vendor/autoload.php";
use \phpseclib\Crypt\RSA;

class TokenGenerator {

  private $publicKey;
  private $separator;
  private $psk;
  public function getToken($urlEncoded = true){
      $rsa = new RSA();
      $rsa->loadKey($this->publicKey);
      if($urlEncoded===true)
          return urlencode($rsa->encrypt($this->generateToken()));
        else
          return $rsa->encrypt($this->generateToken());
  }
  private function generateToken(){
    return $this->psk.$this->separator.time();
  }
  public function setPsk($psk){
    $this->psk = psk;
  }
  public function setSeparator($separator){
    $this->separator = $separator;
  }
  public function setPublicKey($publicKey){
    $this->publicKey = $publicKey;
  }
}

 ?>
