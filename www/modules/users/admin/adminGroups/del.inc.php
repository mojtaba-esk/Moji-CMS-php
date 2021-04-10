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
	

	//<!-- Check Accessibility

		$rwP = DB::load( 
					array(
						'tableName' => 'admin_users_groups',
						'cols'	=> array( 'id'),
						'where'	=> array( 'key' => 'admins'),
					)
				);

		if( $rwP && in_array( $rwP[0]['id'], $_REQUEST['chk']))
		{
			msgDie( Lang::getVal( 'youCantDeleteThis'), './'. URL::get( array( 'chk[]', 'del')), 1, 'error');
			return;

		}//End of if( Session::$userId == $_GET['id']);

	//End of Check Accessibility-->

	//Delete from all Languages...
	DB::delete( array(
			'tableName' => 'admin_users_groups',
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
				$file -> delete( $id, 0, 'img.'. $_GET['sub'] .'.');
			}
		}

	//End of Delete The Files-->

	msgDie( Lang::getVal( 'deleted'), URL::get( array( 'pg', 'chk[]', 'del')), 1);

?>
