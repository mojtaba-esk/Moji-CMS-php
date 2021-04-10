<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Delete The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	if( isset( $grInf))
	{
		msgDie( Lang::getVal( 'accessDenied'), NULL, 0, 'error');
		return;
	}

	if( !is_array( $_REQUEST['chk'])) return;
	
	if( in_array( Session::$userId, $_REQUEST['chk']))
	{
		msgDie( Lang::getVal( 'youCantDeleteThis'), './'. URL::get( array( 'chk[]', 'del')), 1, 'error');
		return;

	}//End of if( Session::$userId == $_GET['id']);

	//Delete from all Languages...
	DB::delete( array(
			'tableName' => 'view_users_main',
			'where'	=> array(
				'id' => & $_REQUEST['chk'],
			),
		)
		, Module::$name /* Cache Prefix*/
	);
	
	sLog( array(
			'itemId'	=> $_REQUEST['chk'][0],
			'desc'		=> implode( ', ', $_REQUEST['chk']),
			'action'	=> 'del',
		)
	);
	
	//<!-- Delete The Files

		if( Module::$opt[ $_GET['sub'] .'ImageFile'])
		{
			lib( array( 'File', 'Img'));

			Img::setPrfx( Module::$name);
			$file = new File( Module::$name);
			foreach( $_REQUEST['chk'] as $id)
			{
				$file -> delete( $id, 0, 'vImg.');
			}
		}

	//End of Delete The Files-->

	msgDie( Lang::getVal( 'deleted'), URL::get( array( 'pg', 'chk[]', 'del')), 1);

?>
