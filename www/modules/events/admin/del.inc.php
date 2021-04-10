<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Delete The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	if( !is_array( $_REQUEST['chk'])) return;
	
	isset( $srch) or $srch = new Search( Module::$opt[ 'id']);

	$deltdRws = 0;
	if( isset( $_REQUEST[ 'delAllLngs']))
	{
		//Delete from all Languages...
		$deltdRws = DB::delete( array(
				'tableName' => Module::$name . '_main',
				'where'	=> array(
					'rltdId'	=> & $_REQUEST['chk'],
					'domId'		=> $_cfg['domain']['id'],
				),
			)
			,( Module::$name . $_cfg['domain']['id'])  /* Cache Prefix*/
		);
		
		$srch -> clearIndxs( $_REQUEST['chk']);
		
	}else{
		
		$deltdRws = DB::delete( array(
				'tableName' => Module::$name . '_main',
				'where'	=> array(
					'rltdId'	=> & $_REQUEST['chk'],
					'lngId'		=> Lang::viewId(),
					'domId'		=> $_cfg['domain']['id'],
				),
			)
			, (Module::$name . $_cfg['domain']['id'])  /* Cache Prefix*/
		);
		
		$srch -> clearIndxs( $_REQUEST['chk'], Lang::viewId());
		
	}//End of if( isset( $_REQUEST[ 'delAllLngs']));

	sLog( array(
			'itemId'	=> $_REQUEST['chk'][0],
			'desc'		=> implode( ', ', $_REQUEST['chk']),
			'action'	=> 'del',
		)
	);
		
	//<!-- Delete The Files

		if( ( Module::$opt['imageFile'] || Module::$opt['attchmnt']) &&	$deltdRws)
		{
			lib( array( 'File', 'Img'));

			Img::setPrfx( Module::$name);
			$file = new File( Module::$name);
			foreach( $_REQUEST['chk'] as $id)
			{
				$file -> delete( $id);
			}
			
			//<!-- Delete The Attachements
				
				if( Module::$opt['attchmnt'])
				{
					$SQL = 'SELECT `id` FROM `'. Module::$name .'_attachments` WHERE `itemId` IN( 0'. implode( ',', $_REQUEST['chk']) .' )';
					$rws = DB::load( $SQL, 0, 1);
					$rws or $rws = array();
					foreach( $rws as $id)
					{
						$file -> delete( $id, 0, 'attchmnt.');
					}
					
					DB::delete( array(
							'tableName' => Module::$name . '_attachments',
							'where'	=> array(
								'itemId'	=> & $_REQUEST['chk'],
							),
						)
						,( Module::$name . $_cfg['domain']['id'])  /* Cache Prefix*/
					);

				}//End of if( Module::$opt['attchmnt']);
				
			//End of Delete The Attachements-->

		}// End of if( ( Module::$opt['imageFile'] || Module::$opt['attchmnt']) &&	$deltdRws);

	//End of Delete The Files-->

	msgDie( Lang::getVal( 'deleted'), URL::get( array( 'pg', 'chk[]', 'del')), 1);
?>
