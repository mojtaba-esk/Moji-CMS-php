<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Delete The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( !is_array( $_REQUEST['chk'])) return;

	DB::delete( array(
			'tableName' => 'admin_menu',
			'where'	=> array(
				'id' => & $_REQUEST['chk'],
			),
		)
		//, 'tpl_'. xx /* Cache Prefix*/
	);
	
	Cache::clean( 'admin_menu' /* Cache Prefix*/, '');

	msgDie( Lang::getVal( 'deleted'), URL::get( array( 'pg', 'chk[]', 'del')), 1);

?>
