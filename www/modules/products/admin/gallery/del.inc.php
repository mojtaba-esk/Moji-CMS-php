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
		$delRws = DB::delete( array(
				'tableName' => Module::$name .'_'. $_GET['sub'],
				'where'	=> array(
					'rltdId'	=> & $_REQUEST['chk'],
					'domId'		=> $_cfg['domain']['id'],
				),
			)
			, Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
		);
		
	}else{
		
		$delRws = DB::delete( array(
				'tableName' => Module::$name .'_'. $_GET['sub'],
				'where'	=> array(
					'rltdId'=> & $_REQUEST['chk'],
					'lngId'	=> Lang::viewId(),
					'domId' => $_cfg['domain']['id'],
				),
			)
			, Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
		);
		
	}//End of if( isset( $_REQUEST[ 'delAllLngs']));
	
	//<!-- Delete The Files

		if( Module::$opt[ $_GET['sub'] .'ImageFile'] && !empty( $delRws))
		{
			lib( array(
					'File',
					'Img',
				)
			);

			Img::setPrfx( Module::$name);
			$file = new File( Module::$name);
			foreach( $_REQUEST['chk'] as $id)
			{
				$file -> delete( $id, 0, 'img.'. $_GET['sub'] .'.');
			}

		}//End of if( Module::$opt[ $_GET['sub'] .'ImageFile'] && !empty( $delRws));

	//End of Delete The Files-->

	msgDie( Lang::getVal( 'deleted'), URL::get( array( 'pg', 'chk[]', 'del')), 1);

?>
