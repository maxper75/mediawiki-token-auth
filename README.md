This project was born to solve simply the access to a guest access protected wiki.
Big thanks go to the authors of the NetworkAuth module which I used as a boilerplate.
Without that plugin I'd never be able to understand MediaWiki internals so easily.
The requirement was to have ALL the content protected by an access, with 3 different
roles:
1 ADMIN (can do anything on the wiki)
2 GUEST (can only read)
3 Unauthenticated user (cannot even read)

While the admin/unauthenticated user it was easy to obtain, the problem was to
obtain an intermediate role which can be easily configured, but as the user list
is not managed inside mediawiki, I had to create an alternative auth method
using a single user.
This way I authenticate the user in the other application and when I want to access
a wiki page I just add the token to the URL. The token gets read and the user is
authenticated on the system.
The process is simple.
1 You generate an RSA key pair, you put the private on your MW server and
transfer the public to your client application(s). Once you do that,
you should generate a token on the client that is composed this way


```
<PSK><SEPARATOR><TIMESTAMP>
```
Where PSK is a common word defined within the MW server (it can be used, for
  instance to authenticate different users instead of just one)
  The separator itself is agreed between server and client, (default '%_%')
  And the timestamp is the actual time value expressed in SECONDS.
  I released with the code even a PHP client that calculates the token very
  easily with the TokenGenerator.class.php. The example is Where

```php

<?php
require "TokenGenerator.class.php";
$tg = new  TokenGenerator();
$urlEncodedToken = $tg->getToken(true);
?>
```
