<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Delete The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	if( !is_array( $_REQUEST['chk']) || empty( $_cfg['domain']['id'])) return;
	
		DB::delete( array(
				'tableName' => 'words_main',
				'where'	=> array(
					'key'	=> & $_REQUEST['chk'],
					'lngId'	=> Lang::viewId(),
					'domId' => $_cfg['domain']['id'],
				),
			)
			, 'words'. $_cfg['domain']['id'] /* Cache Prefix*/
		);
		
		Cache::clean( 'admin_menu_'. $_cfg['domain']['id']);

	sLog( array(
			'itemId'	=> 1,
			'desc'		=> implode( ', ', $_REQUEST['chk']),
			'action'	=> 'del',
		)
	);
	
	msgDie( Lang::getVal( 'deleted'), URL::get( array( 'pg', 'chk[]', 'del')), 1);
	return;
?>
