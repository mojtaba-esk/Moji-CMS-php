<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Full view;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	//<!-- Log the user's information...
	
		//<!-- Find the customer Id...
			
			
			$_GET['serial'] = preg_replace("([^0-9\-]*)", '', $_GET['serial']);

			$SQL = 'SELECT
						`rltdId`	AS	`id`
					FROM
						`'. Module::$name . '_main`
					WHERE
						`lockSerial` = \''. $_GET['serial'] .'\'
					LIMIT	1';
			$cuRw = DB::load( $SQL, Module::$name . '_main');

		//-->
		
		if( !sizeof( $cuRw)) return;
		
		$cols = array();		
		$cols[ 'cuId'] = & $cuRw[0]['id'];
		$cols[ 'insrtTime'] = time();
		$cols[ 'ip'] = & $_SERVER['REMOTE_ADDR'];
		
		DB::insert( array(
				'tableName' => Module::$name . '_logs',
				'cols' => & $cols,
			)
			,  Module::$name /* Cache Prefix*/
		);		
	
	//End of logging-->

	$tpl -> set_filenames( array(
		'full' => Module::$name .'.'. $_GET['mod'] .'.view.full',
		)
	);
	
	//<!-- Fetch The Record By Related Id

		//$_GET['name'] = preg_replace("([^0-9a-zA-Z]*)", '', $_GET['name']);

		$rws = DB::load(
			array( 
				//'tableName' => Module::$name . '_main',
				'tableName' => 'pages_main',
				'where'	=> array( 
					'name'	=> & $_GET['mod'],
					'lngId'	=> Lang::viewId(),
					'productId'	=> & $_cfg['product']['id'],
				),
			), true
		);

		$rw	= & $rws[0];
		$_GET['id'] = & $rw['rltdId'];
		
	//End of Fetch The Record By Related Id-->	
	
	//<!-- Send vars to Template . . .
	
		$tpl -> assign_vars( array(

			//'PAGE_TITLE'	=> $_cfg['product']['title'] .' | '. 	Lang::getval( $rw['name']),
			//'PAGE_KEYWORDS'	=> $_cfg['product']['title'] .','.		Lang::getval( $rw['name']),
			//'PAGE_DESC'		=> $_cfg['product']['title'] .' - '. 	Lang::getval( $rw['name']) .' - '. briefStr( strip_tags( $rw['body']), 80),

			//'L_NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
			//'DATA_EXIST'=>	sizeof( $rw),
			
			//'ATCHMNT'	=>	isset( $atchRws) && sizeof( $atchRws),
			
			//'ITEM_TITLE'=>	Lang::getval( $rw['name']),
			'ITEM_BODY'	=>	& $rw['body'],
			
			//'ITEM_IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'h' => 250, 'w' => 250)),

			)
		);

	//End of Send vars to Template -->

//	$tpl -> display( 'header');
	$tpl -> display( 'full');
	defined( 'DEBUG_MODE') or exit();
?>
