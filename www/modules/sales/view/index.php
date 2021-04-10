<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module View.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

if( !in_array( $_GET['mod'], array( 'active', 'userDesktop', 'update', 'upgrade', 'register')))
{
	printr( 'The Mode is not Valid!');
	return;
}

//<!-- Add Rewrite Rules...

	//URL::$rwRules['/md='. Module::$name .'/'] = 'activation';
	URL::$rwRules['/md=sales/'] = 'activation';
	//'/([&])?md=([^&]*)(&)?/' => '\\2',

//-->

require( $_GET['mod'] .'.inc.php');
?>
