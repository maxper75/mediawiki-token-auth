<?php
/*
Copyright (C) 2012,2013 Olaf Lenz <http://www.mediawiki.org/wiki/User:Olenz>
Copyright (C) 2007,2008,2009,2010,2011 Tim Laqua

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
http://www.gnu.org/copyleft/gpl.html
*/
use \phpseclib\Crypt\RSA;
if ( !defined('MEDIAWIKI') ) {
	die();
}

class TokenAuth {
	/**
	 * TokenAuth constructor.
	 * @param $authrecords
	 * @param $networkauthusers
	 */
	function __construct( $authrecords, $networkauthusers ) {
		if ( is_array( $authrecords ) )
			$this->authrecords = $authrecords;
		else
			$this->authrecords = array( $authrecords );

		if ( is_array( $networkauthusers ) )
			$this->networkauthusers = $networkauthusers;
		else
			$this->networkauthusers = array( $networkauthusers );
	}

	/**
	 * if no user is logged in after the MW tried to load the session,
	 * test whether the user can be logged in due to its source address
	 *
	 * @param User $user
	 * @return bool
	 * @throws FatalError
	 * @throws MWException
	 */
	public function onUserLoadAfterLoadFromSession( $user ) {
		// If we are logged in at this point, there is no need to network
		// authenticate
		// If we are on the login or logout page, we should also not be
		// logged in automatically
		$context = RequestContext::getMain();
		if ( $user->isLoggedIn() ) {
			return true;
		} else {
			$title = $context->getTitle();
			if ( $title && $title->isSpecial('Userlogin') ) {
				wfDebug( "TokenAuth: Login Special page detected" );
				$user->mId = 0;
				// taken from User::doLogout()
				// remove session cookie
				$user->getRequest()->setSessionData( 'wsUserID', 0 );
				$user->getRequest()->setSessionData( 'wsUserName', null );
				return true;
			}
		}

		// fetch the IP address
		// loop over TokenAuth records and see if any of it matches
		$username = "";
		foreach ($this->authrecords as $authrecord) {
			if ( !isset( $authrecord['user'] ) ) {
				// no 'user' is specified for record, so don't do anything
				wfDebug( "TokenAuth: Record $record does not contain 'user' field!\n" );
			} else {
				$username = $authrecord['user'];
				$authToken=$user->getRequest()->getRawVal($authrecord['authTokenName'],"authToken");
				$matched = $this->checkAuth($authToken,$authrecord);

		if ( $matched ) {
			wfDebug( "TokenAuth: Logging in IP $ip, User $username!\n" );

			// log in user
			$mid = User::idFromName( $username );
			$user->setId( $mid );
			$user->loadFromId();
			// set cookie and save settings only when this is not a
			// networkauth user
			if ( !in_array( $username, $this->networkauthusers ) ) {
				$user->saveSettings();
				$user->setCookies();
			}
			Hooks::run('UserLoginComplete', array(&$user, ""));
		}

		return true;
	}
}
}

	private function checkAuth($authToken,$authrecord){
		extract($authrecord);
		$separatore="%_%";
		$decryptrsa = new RSA();
		$decryptrsa->setPrivateKeyFormat(RSA::PRIVATE_FORMAT_XML);
		$decryptrsa->loadKey($authrecord['privkey']);
		$decryptedToken=$decryptrsa->decrypt(base64_decode($authToken));
		$tokenParts = explode($separatore,$decryptedToken);
		return (($tokenParts[0]===$psk)&&($this->validToken($tokenParts[1],$lifetime)));
	}
	private function validToken($tokenTime,$maxRangeSecs){
		$now = time();
		return ((($now+$maxRangeSecs) > $tokenTime)&&(($now-$maxRangeSecs) <$tokenTime));
	}
	/**
	 * for network authenticated users in $this->networkauthusers,
	 * generate login and logout links in the personal urls, and hide
	 * preferences, talk page, contributions, etc.
	 *
	 * @param array $personal_urls
	 * @param Title $title
	 * @return bool
	 * @throws MWException
	 */
	public function onPersonalUrls( &$personal_urls, &$title ) {
		global $wgUseCombinedLoginLink, $wgSecureLogin;

		// fetch context
		$context = RequestContext::getMain();

		// generate special personal urls only when the user is a special
		// networkauth user
		$name = $context->getUser()->getName();
		if ( !in_array( $name, $this->networkauthusers ) ) {
			return true;
		}

		$request = $context->getRequest();
		$ip = $context->getRequest()->getIP();

		wfDebug( "TokenAuth: modifying personal URLs for TokenAuth special user $name from $ip.\n" );

		// generate personal urls
		$newurls = array();
		// generate username
		$newurls['userpage'] = array(
			'text' => wfMessage( 'networkauth-purltext', $name, $ip ),
			'href' => null, 'active' => true
		);

		// copy default logout url
		$newurls['logout'] = $personal_urls['logout'];

		// GENERATE LOGIN LINK

		$query = array();
		if ( !$request->wasPosted() ) {
			$query = $request->getValues();
			unset( $query['title'] );
			unset( $query['returnto'] );
			unset( $query['returntoquery'] );
		}
		$thisquery = wfArrayToCgi( $query );

		// The following is copied from SkinTemplate::buildPersonalUrls

		// Due to bug 32276, if a user does not have read permissions,
		// $this->getTitle() will just give Special:Badtitle, which is
		// not especially useful as a returnto parameter. Use the title
		// from the request instead, if there was one.
		$page = Title::newFromText( $request->getVal( 'title', '' ) );
		$page = $request->getVal( 'returnto', $page );
		$a = array();
		if ( strval( $page ) !== '' ) {
			$a['returnto'] = $page;
			$query = $request->getVal( 'returntoquery', $thisquery );
			if( $query != '' ) {
				$a['returntoquery'] = $query;
			}
		}

		if ( $wgSecureLogin && $request->detectProtocol() === 'https' ) {
			$a['wpStickHTTPS'] = true;
		}

		$returnto = wfArrayToCgi( $a );

		$loginlink = $context->getUser()->isAllowed( 'createaccount' ) && $wgUseCombinedLoginLink
			? 'nav-login-createaccount'
			: 'login';
		$is_signup = $request->getText( 'type' ) == 'signup';

		// anonlogin & login are the same
		$proto = $wgSecureLogin ? PROTO_HTTPS : null;

		$login_url = array(
			'text' => $context->msg( $loginlink )->text(),
			'href' => Skin::makeSpecialUrl( 'Userlogin', $returnto, $proto ),
			'active' => $title->isSpecial( 'Userlogin' ) && ( $loginlink == 'nav-login-createaccount' || !$is_signup ),
			'class' => $wgSecureLogin ? 'link-https' : ''
		);
		$createaccount_url = array(
			'text' => $context->msg( 'createaccount' )->text(),
			'href' => Skin::makeSpecialUrl( 'Userlogin', "$returnto&type=signup", $proto ),
			'active' => $title->isSpecial( 'Userlogin' ) && $is_signup,
			'class' => $wgSecureLogin ? 'link-https' : ''
		);

		if ( $context->getUser()->isAllowed( 'createaccount' ) && !$wgUseCombinedLoginLink ) {
			$newurls['createaccount'] = $createaccount_url;
		}

		$newurls['networkauth-login'] = $login_url;

		$personal_urls = $newurls;

		return true;
	}
}
