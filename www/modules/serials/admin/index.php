<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( !in_array( $_GET['mod'], array( 'lst', 'edt', 'new')))
	{
		printr( 'The Mode is not Valid!');
		return;
	}
	
	Lang::$wrds[ 'url' ] = Lang::getVal( 'urlOrSerial');

	$inc = $_GET['mod'];
	$inc == 'new' and $inc = 'edt';

	if( isset( $_GET['sub']))
	{
		if( !in_array( $_GET['sub'], array( 'cats', ))) 
		{
			$_GET['sub'] = '';

		}else{

			$inc = $_GET['sub'] .'/'. $inc;

		}//End of if( !in_array( $_GET['sub'], array( 'cats', . . . ))) ;

	}//End of if( isset( $_GET['sub']));

	if( isset( $_GET['sub']) || $inc != 'edt' || !isset( $_POST['submit'])) $tpl -> display( 'header');
	require( $inc. '.inc.php');
?>
