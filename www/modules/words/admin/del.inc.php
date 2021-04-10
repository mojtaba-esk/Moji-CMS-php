<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Delete The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	if( !is_array( $_REQUEST['chk'])) return;
	
	if( isset( $_REQUEST[ 'delAllLngs']))
	{
		//Delete from all Languages...
		DB::delete( array(
				'tableName' => Module::$name . '_main',
				'where'	=> array(
					'key'	=> & $_REQUEST['chk'],
					'domId'	=> 0,
				),
			)
			, Module::$name /* Cache Prefix*/
		);
		
	}else{
		
		DB::delete( array(
				'tableName' => Module::$name . '_main',
				'where'	=> array(
					'key'	=> & $_REQUEST['chk'],
					'lngId'	=> Lang::viewId(),
					'domId'	=> 0,
				),
			)
			, Module::$name /* Cache Prefix*/
		);
		
	}//End of if( isset( $_REQUEST[ 'delAllLngs']));

	sLog( array(
			'itemId'	=> 1,
			'desc'		=> implode( ', ', $_REQUEST['chk']),
			'action'	=> 'del',
		)
	);
	
	msgDie( Lang::getVal( 'deleted'), URL::get( array( 'pg', 'chk[]', 'del')), 1);
	return;

?>
