<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( !in_array( $_GET['mod'], array( 'lst', 'edt', 'new', 'view', 'logs')))
	{
		printr( 'The Mode is not Valid!');
		return;
	}
	
	//<!-- Check the permissions...

		if( is_array( Module::$opt['permission']) && 
			( 	!isset( $_GET['sub']) && empty( Module::$opt['permission'][ $_GET['mod'] ]))

			)
		{
			msgDie( Lang::getVal( 'accessDenied'), NULL, 0, 'error');
			return;
		}
		
	//-->	

	$inc = $_GET['mod'];
	$inc == 'new' and $inc = 'edt';

	if( isset( $_GET['sub']))
	{
		if( !in_array( $_GET['sub'], array( 'salers', 'cats', ))) 
		{
			$_GET['sub'] = '';

		}else{

			$inc = $_GET['sub'] .'/'. $inc;

		}//End of if( !in_array( $_GET['sub'], array( 'cats', . . . ))) ;

	}//End of if( isset( $_GET['sub']));

	if( !isset( $_POST[ 'export']) && !isset( $_POST[ 'updateExport'])) $tpl -> display( 'header');	

	if( !@$_SESSION['pId'])// && @$_GET['sub'] != 'salers')
	{
		msgDie( Lang::getVal( 'prdctNotSelctd'), './?md=products&mod=select', 1, 'error', Lang::getVal( 'productSelect'));
		return;
	}

	require( $inc. '.inc.php');
?>
