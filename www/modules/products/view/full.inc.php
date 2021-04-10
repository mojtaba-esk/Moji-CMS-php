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
		
		if( Module::$opt['categoryMod'])
		{
			$SQL = 'SELECT 
					`m`.*,
					`c`.`title` AS `catTitle`
				FROM
					`'. Module::$name . '_main` AS `m` LEFT JOIN `'. Module::$name . '_cats` AS `c`
					ON 
						`m`.`catId` = `c`.`rltdId` AND 
						`c`.`lngId` = `m`.`lngId`
				WHERE 
					`m`.`rltdId` = '. intval( $_GET['id']) .'
					AND
						`m`.`lngId` = '. Lang::viewId().'
					AND 
						`m`.`domId` = '. $_cfg['domain']['id'];
						
		}else{

			$SQL = 'SELECT 
					`m`.*,
					`t`.`title` AS `typeTitle`,
					`t`.`niceUrl` AS `typeNiceUrl`
				FROM
					`'. Module::$name . '_main`		AS `m`,
					`'. Module::$name . '_types`	AS `t`
				WHERE 
					`m`.`typeId` = `t`.`rltdId` 
					AND 
						`t`.`lngId` = `m`.`lngId`
					AND
					(
						`m`.`rltdId` = '. intval( $_GET['id']) .'
						OR
						`m`.`niceUrl` = \''. $_GET['id'] .'\'
					)
					AND
						`m`.`lngId` = '. Lang::viewId().'
					AND 
						`m`.`domId` = '. $_cfg['domain']['id'];

		}//End of if( Module::$opt['categoryMod']);

		$rws = DB::load( $SQL, Module::$name . $_cfg['domain']['id'] .'_main');
		$rw	= & $rws[0];
		$_GET['id'] = $rw['rltdId'];
		
	//End of Fetch The Record By Related Id-->
	
	//printr( $rw);
	
	//<!-- Prepare the Attachements
	
		if( Module::$opt['attchmnt'])
		{
			include( 'attchmnt.inc.php');

		}//End of if( Module::$opt['attchmnt']);

	//End of Prepare the Attachements -->
	

	//<!-- Prepare the Attachements
	
		if( Module::$opt['hasGallery'])
		{
			Module::$opt['hasGallery'] = include( 'gallery.inc.php');

		}//End of if( Module::$opt['attchmnt']);

	//End of Prepare the Attachements -->	
	
	//<!-- Send vars to Template ...
	
		$tpl -> assign_vars( array(

			'PAGE_TITLE'	=> $_cfg['domain']['title'] .' | '. Lang::getVal( Module::$name) .' | '. $rw['title'],
			'PAGE_KEYWORDS'	=> $_cfg['domain']['title'] .','.	Lang::getVal( Module::$name) .','.	$rw['title'],
			'PAGE_DESC'		=> $_cfg['domain']['title'] .' - '. Lang::getVal( Module::$name) .' - '. briefStr( isset( $rw['lead']) ? $rw['lead'] : strip_tags( $rw['body']), 80),

			'L_NOT_EXIST_MESSAGE'	=> Lang::getVal( 'noDataExist'),
			'DATA_EXIST'			=> sizeof( $rw),

			'ATCHMNT'			=>	isset( $atchRws) && sizeof( $atchRws),
			'L_ATTACHEMENTS'	=>	Lang::getVal( 'attachements'),
			'L_DOWNLOAD_HITS'	=>	Lang::getVal( 'downloadHits'),
			'L_FILE_SIZE'		=>	Lang::getVal( 'fileSize'),
			'L_KB'				=>	Lang::getVal( 'KB'),

			'ITEM_TITLE'=>	& $rw['title'],
			'ITEM_BODY'	=>	& $rw['body'],

			'CAT_TITLE'	=>	@$rw['catTitle'],
			'CAT_URL'	=>	URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&catId='. $rw['catId']),

			'TYPE_TITLE'	=>	& $rw['typeTitle'],
			'TYPE_URL'		=>	URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&typeId='. ( $rw['typeNiceUrl'] ? $rw['typeNiceUrl'] : $rw['typeId'])),
			
			'ITEM_IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'h' => 400, 'w' => 300)),
			
			'L_PUBLISH_TIME'	=> Lang::getVal( 'pblishTime'),
			'ITEM_PUBLISH_TIME'	=> Lang::numFrm( Date::get( 'D d M Y G:i', $rw[ 'insrtTime'])),
			
			'L_PRICE'		=> Lang::getVal( 'price'),
			'ITEM_PRICE'	=> Module::$opt['hasPrice'] && $rw[ 'price'] ? Lang::numFrm( number_format( $rw[ 'price'])) .' '. Lang::getVal( 'rials') : false,
			
			//'SHORT_DESC' => & $rw['shortDesc'],
			
			'HAS_GALLERY'	=> Module::$opt['hasGallery'],
			'L_GALLERY'		=> Lang::getVal( 'gallery'),
			
			'L_FIELDS'		=> Lang::getVal( 'fields'),

			)
		);

	//End of Send vars to Template -->
	
	//<!-- Custom fields...

		$fldInfo = require( dirname( __FILE__). '/../admin/fldInfo.inc.php');

		$SQL = 'SELECT
					`f`.`ordrId`,
					`f`.`id`,
					`f`.`title`,
					`f`.`fldType`,
					
					`v`.`fldId`,
					`v`.`txtVal`,
					`v`.`numVal`
				FROM
					`'. Module::$name . '_fields`			AS	`f`,
					`'. Module::$name . '_fields_values`	AS	`v`
				WHERE
						`f`.`typeId` = '. $rw['typeId'] .'
					AND
						`f`.`domId` = '. $_cfg['domain']['id'] .'
					AND
						`v`.`itemId` = '. $rw['rltdId'] .'
					AND
						`f`.`lngId` = '. Lang::viewId().'
					AND
						`v`.`fldId` = `f`.`rltdId`
					AND
						`f`.`lngId` = `v`.`lngId`
					AND
						`f`.`domId` = `v`.`domId`
				ORDER BY
					`f`.`ordrId` ASC,
					`f`.`id` ASC';
					
		$cRws = DB::load( $SQL, Module::$name . $_cfg['domain']['id']);

		is_array( $cRws) or $cRws = array();
		foreach( $cRws as $key => $rw)
		{
			$rw['val'] = empty( $rw['txtVal']) ? $rw['numVal'] : $rw['txtVal'];

			if( $fldInfo[ $rw['fldType']]['type'] == 'multi')
			{
				//<!-- Fetch the fileds params...

					$SQL = 'SELECT
								`title`
							FROM
								`'. Module::$name . '_fields_params`
							WHERE
								`lngId` = '. Lang::viewId().'
								AND
									`domId` = '. $_cfg['domain']['id'] .'
								AND
									`fldId` = '. $rw['fldId'] .'
								AND
									`rltdId` IN ( 0'. $rw['val'] .')
							ORDER BY
								`ordrId` ASC,
								`id` ASC';

					$pRws = DB::load( $SQL, Module::$name . $_cfg['domain']['id'] .'_fields_params', true /*Single field*/);
					$rw['val'] = implode( '<br />', $pRws);
					
					unset( $pRws);

				//-->

			}//End of if( $fldInfo[ $rw['fldType']]['type'] == 'multi');
			
			elseif( $rw['fldType'] == 'dateTime'){
			
				$rw['val'] = Lang::numFrm( Date::get( 'D d M Y G:i', $rw[ 'val']));
			
			}//End of elseif( $rw['fldType'] == 'dateTime');
			
			elseif( $rw['fldType'] == 'date'){
			
				$rw['val'] = Lang::numFrm( Date::get( 'D d M Y', $rw[ 'val']));
			
			}//End of elseif( $rw['fldType'] == 'date');
			
			elseif( $rw['fldType'] == 'checkbox'){
			
				$rw['val'] = empty( $rw['val']) ? Lang::getVal( 'no') : Lang::getVal( 'yes');
			
			}//End of elseif( $rw['fldType'] == 'date');
			
			$tpl -> assign_block_vars( 'cublck',  array(

				'RW_ODD'=> $key & 1,
				'TITLE' => $rw['title'],
				'VALUE' => $rw['val'],
				'IMG'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['fldId'], 0, 'img.fields.'), array( 'h' => 40/*, 'w' => 50 /**/)),

				)
			);

		}//End of foreach( $cRws as $key => $rw);
	
	//End of Custom fields-->
	

	if( !sizeof( $rws))
	{
		notFound( NULL); //Send Only 404 Header...
	}

	$tpl -> display( 'header');
	$tpl -> display( 'full');
?>
