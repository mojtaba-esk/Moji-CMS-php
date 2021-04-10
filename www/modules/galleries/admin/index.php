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

	$inc = $_GET['mod'];
	$inc == 'new' and $inc = 'edt';

	if( isset( $_GET['sub']))
	{
		if( !in_array( $_GET['sub'], array( 'cats', 'gallery'))) 
		{
			$_GET['sub'] = '';

		}else{

			$inc = $_GET['sub'] .'/'. $inc;

		}//End of if( !in_array( $_GET['sub'], array( 'cats', . . . ))) ;

	}//End of if( isset( $_GET['sub']));

	if( 
		is_array( Module::$opt['permission']) &&
		(
			isset( $_GET['sub']) && !isset( Module::$opt['permission']['sub'][$_GET['sub']][ $_GET['mod'] ]) ||
			isset( $_GET['mod']) && !isset( Module::$opt['permission']['mod'][$_GET['mod']] )
		)
	)
	{
		msgDie( Lang::getVal( 'accessDenied'), NULL, 0, 'error');
		return;
	}

	$tpl -> display( 'header');

	require( $inc. '.inc.php');
?>
