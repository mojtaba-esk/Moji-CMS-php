<?php
/*
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Config Array.
*/

$_cfg = array(
	
	'DB' => array(
		'name' => getenv( 'DB_NAME'),
		'host' => getenv( 'DB_HOST'),
		'readUser' => array(
			'user' => getenv( 'DB_USER'),
			'pass' => getenv( 'DB_PASSWORD'),
		),
		'writeUser' => array(
			'user' => getenv( 'DB_USER'),
			'pass' => getenv( 'DB_PASSWORD'),
		),
		'bckupUser' => array(
			'user' => getenv( 'DB_USER'),
			'pass' => getenv( 'DB_PASSWORD'),
		),
	),
	
	'Bank'	=> array(
		'MID'	=>	'000000000',
		'pass'	=>	'000000000000000',
	),

	'lang'  => 'en', //Default Language. (fa,en,de,fr,...)

	'URL'  => getenv( 'WEB_HOST'), //with trail slash.
);

@define( 'DEBUG_MODE', true);
// @define( 'DISABLE_CACHE', true);
//@define( 'DISABLE_LOG', true);
@define( 'TRANSLATION_MODE', true);
@define( 'DEVELOPER_MODE', true);

//if( @$_GET['md'] != 'templates') 
//@define( 'AJAX', true);

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED); ini_set('display_errors', 1);
//error_reporting( 0);

$_cfg['domain'] = array(
	'id'		=>	1,
	'title'		=>	'سایت باحال ما',
	'statusId'	=>	0,
	'tmpId'		=>	1,
	'planId'	=>	1,
	'sidebar'	=>	'news',
	'home'		=>	array( 'products', 'news', 'faq', 'help'),
);

?>