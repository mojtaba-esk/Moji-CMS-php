<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module View.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

$_GET['mod'] == 'lst' and $_GET['mod'] = 'new';

if( !in_array( $_GET['mod'], array( 'new', 'reply', 'download')))
{
	printr( 'The Mode is not Valid!');
	return;
}

//<!-- Add Rewrite Rules...

	//URL::$rwRules['/md='. Module::$name .'/'] = Module::$name;
	//'/([&])?md=([^&]*)(&)?/' => '\\2',
	URL::$rwRules['/mod=full/']	 	= '';
	URL::$rwRules['/mod=reply/']	= 'reply';
	URL::$rwRules['/key=((.)*)/']	= '\\1';
	URL::$rwRules['/mod=download/']	= 'download';
	URL::$rwRules['/id=([0-9]*)/']	= '\\1';
	URL::$rwRules['/t=((.)*)/']	= '\\1';

//-->

require( $_GET['mod'] .'.inc.php');
?>
