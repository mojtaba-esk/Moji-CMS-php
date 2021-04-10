<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Delete The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( !is_array( $_REQUEST['chk'])) return;

	is_array( $_REQUEST['chk']) or $_REQUEST['chk'] = array();
	
	foreach( $_REQUEST['chk'] as $fileName)
	{
		unlink( dirname( __FILE__) .'/../../../../ext/tpl/'. $fileName);
	}

	msgDie( Lang::getVal( 'deleted'), URL::get( array( 'pg', 'chk[]', 'del')), 1);
?>
