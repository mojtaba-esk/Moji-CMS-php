<?php
/**
* @author Ghasem Babaie
* @since 2013-01-19
* @name Module Admin Panel.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( !in_array( $_GET['mod'], array( 'lst', 'edt', 'new', 'graph')))
	{
		printr( 'The Mode is not Valid!');
		return;
	}

	$inc = $_GET['mod'];
	$inc == 'new' and $inc = 'edt';

	if( isset( $_GET['sub']))
	{
		if( !in_array( $_GET['sub'], array( 'cats', 'options'))) 
		{
			$_GET['sub'] = '';

		}else{

			$inc = $_GET['sub'] .'/'. $inc;

		}//End of if( !in_array( $_GET['sub'], array( 'cats', . . . )));

	}//End of if( isset( $_GET['sub']));
	
	$tpl -> display( 'header');
	require( $inc. '.inc.php');
?>
