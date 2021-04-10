<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( Module::$opt['imageFile'] || Module::$opt['attchmnt'])
	{
		lib( array( 'File'));
		$file = new File( Module::$name);
		
		if( Module::$opt['imageFile'])
		{
			lib( array( 'Img'));
			Img::setPrfx( Module::$name);

		}//End of if( Module::$opt['imageFile']);

	}//End of if( Module::$opt['imageFile'] || Module::$opt['attchmnt']);

	$tpl -> set_filenames( array(
		'full' => Module::$name .'.view.full',
		)
	);
	
	//<!-- Fetch The Record By Related Id
	
		$_GET['name'] = preg_replace("([^0-9a-zA-Z]*)", '', $_GET['name']);

		$rws = DB::load(
			array( 
				'tableName' => Module::$name . '_main',
				'where'	=> array( 
					'name'	=> & $_GET['name'],
					'lngId'	=> Lang::viewId(),
					'domId'	=> & $_cfg['domain']['id'],
				),
			), true
		);

		$rw	= & $rws[0];
		$_GET['id'] = & $rw['rltdId'];
		
		
	//End of Fetch The Record By Related Id-->
	
	//<!-- Prepare the Attachements

		if( Module::$opt['attchmnt'])
		{
			include( 'attchmnt.inc.php');

		}//End of if( Module::$opt['attchmnt']);

	//End of Prepare the Attachements -->
	
	//<!-- Send vars to Template . . .
	
		$tpl -> assign_vars( array(

			'PAGE_TITLE'	=> $_cfg['domain']['title'] .' | '. 	Lang::getval( $rw['name']),
			'PAGE_KEYWORDS'	=> $_cfg['domain']['title'] .','.		Lang::getval( $rw['name']),
			'PAGE_DESC'		=> $_cfg['domain']['title'] .' - '. 	Lang::getval( $rw['name']) .' - '. briefStr( strip_tags( $rw['body']), 80),

			'L_NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
			'DATA_EXIST'=>	sizeof( $rw),
			
			'ATCHMNT'	=>	isset( $atchRws) && sizeof( $atchRws),
			
			'ITEM_TITLE'=>	Lang::getval( $rw['name']),
			'ITEM_BODY'	=>	& $rw['body'],
			
			'ITEM_IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'h' => 250, 'w' => 250)),

			)
		);

	//End of Send vars to Template -->

	if( !sizeof( $rw))
	{
		notFound( NULL); //Send Only 404 Header...
	}
	
	$tpl -> display( 'header');
	$tpl -> display( 'full');
?>
