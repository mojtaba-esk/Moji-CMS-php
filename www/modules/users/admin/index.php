<?php
/**
* @author Mojtaba Eskandari
* @since 2009-12-25
* @name Module Admin Panel.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	include( 'functions.inc.php');
	
	if( $_GET['mod'] == 'logout')
	{
		$tpl -> display( 'header');
		sLog( array(
				'itemId'	=> Session::$userId,
				'desc'		=> Lang::getVal( 'logout'),
			)
		);

		User::logout();
		msgDie( Lang::getVal( 'redirecting'), './?md=users&sub=login', 1, 'info', Lang::getVal( 'login'), true /*No ajax*/);
		return;
	}

	if( !in_array( $_GET['mod'], array( 'edt', 'lst', 'new', 'view', 'pop')))
	{
		printr( 'The Mode is not Valid!');
		return;
	}

	$inc = $_GET['mod'];
	$inc == 'new' and $inc = 'edt';
	
	$_GET['mod'] == 'edt' or isset( $_GET['sub']) or $_REQUEST['sub'] = $_GET['sub'] = 'adminUsers';

	if( isset( $_GET['sub']))
	{
		if( !in_array( $_GET['sub'], array( 'login', 'adminUsers', 'adminGroups', 'viewUsers')))
		{
			$_REQUEST['sub'] = $_GET['sub'] = '';

		}else{

			$inc = $_GET['sub'] .'/'. $inc;

		}//End of if( !in_array( $_GET['sub'], array( 'cats', . . . ))) ;

	}//End of if( isset( $_GET['sub']));

	$_GET['mod'] == 'pop' or $tpl -> display( 'header');
	require( $inc. '.inc.php');
?>
