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
	
	if( !defined( 'DEVELOPER_MODE'))
	{
		printr( 'Access Denied! Please Call the Developer.');
		return;
	}

	$inc = $_GET['mod'];
	$inc == 'new' and $inc = 'edt';

	if( isset( $_GET['sub']))
	{
		if( !in_array( $_GET['sub'], array( 'templates', 'modules', 'purgeCache', 'adminMenu', 'hints'))) 
		{
			$_GET['sub'] = '';

		}else{

			$inc = $_GET['sub'] .'/'. $inc;

		}//End of if( !in_array( $_GET['sub'], array( 'cats', . . . ))) ;
	
	}else{
		
		printr( 'No Index Page!');
		return;

	}//End of if( isset( $_GET['sub']));

	$_GET['sub'] == 'adminMenu' or $tpl -> assign_vars( array(
		'LANG_DIR'		=> 'ltr',
		'LANG_ALIGN'	=> 'left',
		)
	);

	$tpl -> display( 'header');
	require( $inc. '.inc.php');
?>
