<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( !in_array( $_GET['mod'], array( 'lst', 'reply', 'download')))
	{
		printr( 'The Mode is not Valid!');
		return;
	}

	$inc = $_GET['mod'];

	if( isset( $_GET['sub']))
	{
		if( !in_array( $_GET['sub'], array())) 
		{
			$_GET['sub'] = '';

		}else{

			$inc = $_GET['sub'] .'/'. $inc;

		}//End of if( !in_array( $_GET['sub'], array( 'cats', . . . ))) ;

	}//End of if( isset( $_GET['sub']));
	
	
	if( empty( $_SESSION['pId']))
	{
		$tpl -> display( 'header');
		msgDie( Lang::getVal( 'prdctNotSelctd'), './?md=products&mod=select', 1, 'error', Lang::getVal( 'productSelect'));
	}

	if( $_GET['mod'] != 'download') $tpl -> display( 'header');
	require( $inc. '.inc.php');
?>
