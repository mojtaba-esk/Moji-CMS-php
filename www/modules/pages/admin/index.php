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
	
	isset( $_GET['name']) and $_GET['mod'] = 'edt';

	if( !defined( 'DEBUG_MODE') && ( /* $_GET['mod'] == 'new' || /**/ $_GET['mod'] == 'lst'))
	{
		printr( 'Access Denied! Please Call the Developer.');
		return;
	}/**/	

	$inc = $_GET['mod'];
	$inc == 'new' and $inc = 'edt';

	$tpl -> display( 'header');

	require( $inc. '.inc.php');
?>
