<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Full view;
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
		
		if( Module::$opt['categoryMod'])
		{
			$SQL = 'SELECT `m`.*, `c`.`title` AS `catTitle`
				FROM
					`'. Module::$name . '_main` `m` LEFT JOIN `'. Module::$name . '_cats` `c`
					ON 
						`m`.`catId` = `c`.`rltdId`
						AND
							`c`.`lngId` = `m`.`lngId`
				WHERE 
					`m`.`rltdId` = '. intval( $_GET['id']) .'
					AND
						`m`.`lngId` = '. Lang::viewId() .'
					AND
						`m`.`productId` = '. $_cfg['product']['id'];
		
		}else{

			$SQL = 'SELECT * FROM `'. Module::$name . '_main` AS `m`
				WHERE 
					`m`.`rltdId` = '. intval( $_GET['id']) .'
					AND
						`m`.`lngId` = '. Lang::viewId() .'
					AND
						`m`.`domId` = '. $_cfg['domain']['id'];

		}//End of if( Module::$opt['categoryMod']);
		
		$rws= DB::load( $SQL, Module::$name . '_main');
		$rw	= & $rws[0];
		
	//End of Fetch The Record By Related Id-->
	
	//<!-- Prepare the Attachements
	
		if( Module::$opt['attchmnt'])
		{
			include( 'attchmnt.inc.php');

		}//End of if( Module::$opt['attchmnt']);

	//End of Prepare the Attachements -->
	
	//<!-- Prepare the Gallery
	
		if( Module::$opt['gallery'])
		{
			include( 'gallery.inc.php');

		}//End of if( Module::$opt['gallery']);

	//End of Prepare the Gallery -->

	
	//<!-- Send vars to Template . . .
	
		$tpl -> assign_vars( array(

			'PAGE_TITLE'		=> $_cfg['domain']['title'] .' | '. Lang::getVal( Module::$name) .' | '. $rw['title'],
			'PAGE_KEYWORDS'		=> $_cfg['domain']['title'] .','.	Lang::getVal( Module::$name) .','.	$rw['title'],
			'PAGE_DESC'			=> $_cfg['domain']['title'] .' - '. Lang::getVal( Module::$name) .' - '. briefStr( isset( $rw['lead']) ? $rw['lead'] : strip_tags( $rw['body']), 80),

			'L_NOT_EXIST_MESSAGE'	=> Lang::getVal( 'noDataExist'),
			'DATA_EXIST'			=> sizeof( $rw),
			
			'ATCHMNT'			=>	isset( $atchRws) && sizeof( $atchRws),
			'HAVE_GALLERY'		=>	isset( $gallRws) && sizeof( $gallRws),
			'L_GALLERY'			=>	Lang::getVal( 'gallery'),
			
			'ITEM_TITLE'=>	& $rw['title'],
			'ITEM_BODY'	=>	& $rw['body'],
			'CAT_TITLE'	=>	@$rw['catTitle'],
			'CAT_URL'		=>	URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&catId='. $rw['catId']),
			
			'ITEM_IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'w' => 200)),
			
			'L_PUBLISH_TIME'	=> Lang::getVal( 'pblishTime'),
			'ITEM_PUBLISH_TIME'	=> Lang::numFrm( Date::get( 'D d M Y', $rw[ 'pblishTime'])),

			)
		);

	//End of Send vars to Template -->

	$tpl -> display( 'header');
	$tpl -> display( 'full');
?>
