<?php
/**
* @author Ghasem Babaie
* @since 2013-02-02
* @name Module View.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

if( !in_array( $_GET['mod'], array( 'lst', 'full', 'rss')))
{
	printr( 'The Mode is not Valid!');
	return;
}

//<!-- Add Rewrite Rules...

	//URL::$rwRules['/md='. Module::$name .'/'] = Module::$name;
	//'/([&])?md=([^&]*)(&)?/' => '\\2',
	URL::$rwRules['/mod=full/']	 	= '';
	//URL::$rwRules['/mod=download/']	= 'download';
	URL::$rwRules['/id=([0-9]*)/']	= '\\1';
	URL::$rwRules['/t=((.)*)/']	= '\\1';

//-->

require( $_GET['mod'] .'.inc.php');
?>
