<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	$tpl -> set_filenames( array(
		'full' => Module::$name .'.view.full',
		)
	);
	
	//<!-- Fetch The Record By Name

		//$_GET['name'] = preg_replace("([^0-9a-zA-Z]*)", '', $_GET['name']);

		$rws = DB::load(
			array( 
				'tableName' => 'pages_main',
				'where'	=> array( 
					'name'	=> $_GET['name'] = 'homePage',
					'lngId'	=> Lang::viewId(),
					'productId'	=> $_cfg['product']['id'],
				),
			), true
		);

		$rw	= & $rws[0];
		$_GET['id'] = $rw['rltdId'];
		
	//End of Fetch The Record By Name-->
	
	//<!-- Load The libraries For pages Module.

		lib( array( 'File', 'Img'));
		$file = new File( 'pages');
		Img::setPrfx( 'pages');

	//-->

	//<!-- Prepare the Attachements

		/*
		if( Module::$opt['attchmnt'])
		{
			include( 'attchmnt.inc.php');

		}//End of if( Module::$opt['attchmnt']);
		
		/**/

	//End of Prepare the Attachements -->
	
	//<!-- Send vars to Template . . .
	
	
		$tpl -> assign_vars( array(

			'PAGE_TITLE'	=> $_cfg['product']['title'] .' | '. Lang::getval( $rw['name']),
			'PAGE_KEYWORDS'	=> $_cfg['product']['title'] .','.	Lang::getval( $rw['name']),
			'PAGE_DESC'		=> $_cfg['product']['title'] .' - '. Lang::getval( $rw['name']) .' - '. briefStr( strip_tags( $rw['body']), 80),

			'L_NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
			'DATA_EXIST'	=> sizeof( $rw),
			
			'ATCHMNT'	=>	isset( $atchRws) && sizeof( $atchRws),
			
			'ITEM_TITLE'=>	Lang::getval( $rw['name']),
			'ITEM_BODY'	=>	$rw['body'],
			
			'ITEM_IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'h' => 250, 'w' => 250)),
			
			)
		);

	//End of Send vars to Template -->
	
	//<!-- Show The Random Products...
	
/*		$mdName = 'products';

		URL::$rwRules['/md='. $mdName .'/'] = $mdName;
		URL::$rwRules['/mod=full/']	 = '';
		URL::$rwRules['/id=([0-9]*)/'] = '\\1';

		$SQL = '
			SELECT
				`rltdId`,
				`title`
			FROM
				`'. $mdName .'_main`
			WHERE
				`lngId` = '. Lang::viewId().'
			
			ORDER BY RAND()
			LIMIT 4
		';
		
		$rws = DB::load( $SQL);

		$file = new File( $mdName);
		Img::setPrfx( $mdName);
		
		is_array( $rws) or $rws = array();
		foreach( $rws as $key => $rw)
		{
			$tpl -> assign_block_vars( $mdName,  array(

				'ID' => $rw['rltdId'],
				
				'TITLE'		=> $rw['title'],
				'IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'w' => 140)),
				'URL' 		=> URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. $mdName .'&mod=full&id='. $rw['rltdId'], '/'. $rw['title']),

				)
			);

		}//End of foreach( $rws as $key => $rw);
		
/**/
	//End of Random Products-->
	
	$tpl -> display( 'header');
	$tpl -> display( 'full');
?>
