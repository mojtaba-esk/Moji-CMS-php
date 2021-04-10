<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	$_GET['name'] = Module::$name;

	if( !in_array( $_GET['mod'], array( 'lst', 'edt', 'new')))
	{
		printr( 'The Mode is not Valid!');
		return;
	}
	
	/*if( !defined( 'DEBUG_MODE') && ( $_GET['mod'] == 'new' ||  $_GET['mod'] == 'lst'))
	{
		printr( 'Access Denied! Please Call the Developer.');
		return;
	}/**/

	isset( $_GET['sub']) or isset( $_GET['name']) and $_GET['mod'] = 'edt';

	$inc = $_GET['mod'];
	$inc == 'new' and $inc = 'edt';

	if( isset( $_GET['sub']))
	{
		if( !in_array( $_GET['sub'], array( 'emails', ))) 
		{
			$_GET['sub'] = '';

		}else{

			$inc = $_GET['sub'] .'/'. $inc;

		}//End of if( !in_array( $_GET['sub'], array( 'cats', . . . ))) ;

	}//End of if( isset( $_GET['sub']));

	$tpl -> display( 'header');
	
	if( !isset( $_SESSION['pId']))
	{
		msgDie( Lang::getVal( 'prdctNotSelctd'), './?md=products&mod=select', 1, 'error', Lang::getVal( 'productSelect'));
	}

	require( $inc. '.inc.php');
?>
