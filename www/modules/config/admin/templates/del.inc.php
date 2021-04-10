<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Delete The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( !is_array( $_REQUEST['chk'])) return;

	DB::delete( array(
			'tableName' => $_GET['sub'],
			'where'	=> array(
				'name' => & $_REQUEST['chk'],
			),
		)
		//, 'tpl_'. xx /* Cache Prefix*/
	);
	
	msgDie( Lang::getVal( 'deleted'), URL::get( array( 'pg', 'chk[]', 'del')), 1);

?>
