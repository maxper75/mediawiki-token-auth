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

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

$wgExtensionCredits['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'TokenAuth',
	'version'        => '2.1.2',
	'author'         => array( 'Tim Laqua', 'Olaf Lenz', 'Massimiliano Perantoni' ),
	'descriptionmsg' => 'tokenauth-desc',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:TokenAuth',
	'license-name'   => 'GPL-2.0-or-later',
);

$wgAutoloadClasses['TokenAuth'] = __DIR__ . '/TokenAuth.class.php';
$wgMessagesDirs['TokenAuth'] = __DIR__ . '/i18n';
// defaults
if ( !isset( $wgTokenAuthUsers ) )
  $wgTokenAuthUsers = array();
if ( !isset( $wgTokenAuthSpecialUsers ) )
  $wgTokenAuthSpecialUsers = array();

$wgExtensionFunctions[] = function() {
	global $wgHooks, $wgTokenAuth, $wgTokenAuthUsers, $wgTokenAuthSpecialUsers;

	$wgTokenAuth = new TokenAuth( $wgTokenAuthUsers, $wgTokenAuthSpecialUsers );

	$wgHooks['UserLoadAfterLoadFromSession'][] =
		array( $wgTokenAuth, 'onUserLoadAfterLoadFromSession' );
	$wgHooks['PersonalUrls'][] =
		array( $wgTokenAuth, 'onPersonalUrls' );

	return true;
};
