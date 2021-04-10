<?php
define('IN_MJY_CMS', true);

$Colors = array('000000', '000080', 'FF0000', '0000FF', '808080', '996600', '888822', '007777', 'A22656');
$Fonts = array('ALGER.TTF', 'arial.ttf', 'comic.ttf');

//Requires...
	require( '../inc/config.inc.php');
	require( '../inc/functions.common.inc.php');

	lib( array(
			'DB',
			'Session',
			'Captcha',
			'Captcha02',
		)
	);

	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	// error_reporting(0);
	
if( isset( $_GET['imgId']))//imgId is a id for sevral sections... such as Contact us and register form and . . . 
{

	// Generate Security Code ...

		$sImg = new Captcha( intval( $_GET['imgId']), 1 /* 1 = Generate a new code*/);
		$newCode = $sImg -> GetCode();

	//---------------------

	$Color = $Colors[ rand( 0 , count( $Colors)-1 ) ];
	$Font = './fnts/'. $Fonts[ rand( 0 , count( $Fonts)-1 ) ];
	$Img = new GifCaptchaImg( strtoupper( $newCode) , $Color, $Font);

	header('Content-type: image/gif');
	print( $Img -> AnimatedOut());

}else {

	defined( 'DEBUG_MODE') and printr('Fatal Error:The Security Code is not Set!');
}

?>
