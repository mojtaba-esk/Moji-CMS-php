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
		if( !in_array( $_GET['sub'], array( 'types', 'cats', 'fields', 'fields_params', 'gallery'))) 
		{
			$_GET['sub'] = '';

		}else{
			
			if( $_GET['sub'] == 'gallery' && !Module::$opt['hasGallery'])
			{
				printr( 'This option is not accesible!');
				return;
			}

			$inc = $_GET['sub'] .'/'. $inc;

		}//End of if( !in_array( $_GET['sub'], array( 'cats', . . . )));

	}//End of if( isset( $_GET['sub']));

	$tpl -> display( 'header');
	
	$tpl -> assign_vars( array( 'MODULE_URL' => '?md='. Module::$name .'&sub=types',));
	
	require( $inc. '.inc.php');
?>
