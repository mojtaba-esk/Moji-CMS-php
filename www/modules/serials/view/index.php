<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module View.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

if( !in_array( $_GET['mod'], array( 'active')))
{
	printr( 'The Mode is not Valid!');
	return;
}

require( $_GET['mod'] .'.inc.php');
?>
