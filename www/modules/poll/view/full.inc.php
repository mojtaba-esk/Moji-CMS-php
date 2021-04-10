<?php
/**
* @author Ghasem Babaie
* @since 2013-02-02
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	lib( array(
			'Input',
		)
	);
	
	//<!-- Preaper Input Object ...
	
		$lngs = Lang::getAll();
		//require( $_cfg['path'] .'/inc/lib/Input.class.inc.php');
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$lngsIds[] = $lngs[ $i ][ 'id' ];
			$lngsTitle[ $lngs[ $i ][ 'id' ] ] = $lngs[ $i ][ 'title'];
		}
		$inpt = new Input( $lngsIds);

	//End of Preaper Input Object -->	

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
						`m`.`pblishTime` > 0
					AND 
						`m`.`domId` = '. $_cfg['domain']['id'];
						
		}else{

			$SQL = 'SELECT * 
				FROM
					`'. Module::$name . '_main` AS `m`
				WHERE 
					`m`.`rltdId` = '. intval( $_GET['id']) .'
					AND
						`m`.`lngId` = '. Lang::viewId().'
					AND
						`m`.`pblishTime` > 0
					AND
						`m`.`domId` = '. $_cfg['domain']['id'];
						
		}//End of if( Module::$opt['categoryMod']);

		$rws = DB::load( $SQL, Module::$name . $_cfg['domain']['id'] . '_main');
		$rw	= & $rws[0];
		
		if( $rw['pblishTime'] > time()) unset( $rw, $rws);		
		
		
		$SQL_Opt = 'SELECT * 
			FROM
				`'. Module::$name . '_options` AS `o`
			WHERE 
				`o`.`itemId` = '. intval( $_GET['id']) .'
				AND
					`o`.`lngId` = '. Lang::viewId().'
				ORDER BY 
					`ordrId` ASC';	
						
		$rwsOpt = DB::load( $SQL_Opt, Module::$name . $_cfg['domain']['id'] . '_options');
				
		if (sizeof( $rwsOpt) <= 0)
		{
			$tpl -> display( 'header');
			msgDie( Lang::getVal( 'defectedPollData'), '', 1, 'error');
			return ;
		}		
		
	//End of Fetch The Record By Related Id-->
	
	//<!-- Prepare the Attachements
	
		if( Module::$opt['attchmnt'])
		{
			include( 'attchmnt.inc.php');

		}//End of if( Module::$opt['attchmnt']);

	//End of Prepare the Attachements -->

	//<!-- Update rows
	
		if( $_GET['mod'] == 'full' && isset( $_POST['submit']))
		{
			$rltdId = intval( $_GET['id']);

			$cols = $inpt -> getRow();

			// Update Count Of Selected Option
				$SQL = 'UPDATE `'. Module::$name .'_options` 
						SET `count` = `count` + 1 
						WHERE `rltdId` = '.intval($cols[ 'option']);
				$rws = DB::exec( $SQL);
			// ====>
			
			// Insert user IP in table
				$ip = $_SERVER['REMOTE_ADDR'];
				$insrtTime = time();
				
				$SQL = 'INSERT INTO `'. Module::$name .'_ip` 
							(`itemId`, `ip`, `insrtTime`) 
						VALUES (\''.$rltdId.'\',\''.$ip.'\',\''.$insrtTime.'\')';
				$rws = DB::exec( $SQL, Module::$name);
			// ====>			
			
			Cache::clean( Module::$name);			
			
			$tpl -> display( 'header');
			msgDie( Lang::getVal( 'inserted'), './'. URL::get( array( 'mod', 'id')), 1);
			return;

		}//End of if( $_GET['mod'] == 'edt' && isset( $_POST));
	
	//End of Update rows -->
	
	//<!-- Send vars to Template . . .
	
		$tpl -> assign_vars( array(

			'PAGE_TITLE'	=> $_cfg['domain']['title'] .' | '. Lang::getVal( Module::$name) .' | '. $rw['title'],
			'PAGE_KEYWORDS'	=> $_cfg['domain']['title'] .','.	Lang::getVal( Module::$name) .','.	$rw['title'],
			'PAGE_DESC'		=> $_cfg['domain']['title'] .' - '. Lang::getVal( Module::$name) .' - '. briefStr( isset( $rw['lead']) ? $rw['lead'] : strip_tags( $rw['desc']), 80),

			'L_NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
			'DATA_EXIST'	=> sizeof( $rw),

			'ATCHMNT'			=>	isset( $atchRws) && sizeof( $atchRws),
			'L_ATTACHEMENTS'	=>	Lang::getVal( 'attachements'),
			'L_DOWNLOAD_HITS'	=>	Lang::getVal( 'downloadHits'),
			'L_FILE_SIZE'		=>	Lang::getVal( 'fileSize'),
			'L_KB'				=>	Lang::getVal( 'KB'),

			'ITEM_TITLE'=>	& $rw['title'],
			'ITEM_BODY'	=>	& $rw['question'],
			'CAT_TITLE'	=>	@$rw['catTitle'],
			//'CAT_URL'	=>	URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&catId='. $rw['catId']),
			
			//'ITEM_IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'h' => 400, 'w' => 300)),
			
			'L_PUBLISH_TIME'	=> Lang::getVal( 'pblishTime'),
			'ITEM_PUBLISH_TIME'	=> Lang::numFrm( Date::get( 'D d M Y G:i', $rw[ 'pblishTime'])),
			
			'SHORT_DESC' => & $rw['desc'],
			
			'MESSAGE'=> @$msg,
		
			'SUBMIT' => Lang::getVal( 'submit'),
			'CANCEL' => Lang::getVal( 'cancel'),
		
			'RETURN_URL' => '?md='. Module::$name,			

			)
		);

	//End of Send vars to Template -->
	
	//<!-- Fetch the sum poll count...
		
		$SQL = 'SELECT SUM(`count`) FROM `'. Module::$name .'_options` WHERE `itemId` = '. intval( $_GET['id']) . ' AND `lngId` = '. Lang::viewId();
		$pollSum = DB::load( $SQL, 0, 1);
		$pollSum = $pollSum[0];
	
	//End of Fetch the sum poll count -->		
	
	//<!-- Prepare Form Elements...
		
		unset( $items); $items = array();
		for( $i = 0; $i != sizeof( $rwsOpt); $i++)
		{	
			$items[ $rwsOpt[$i]['rltdId'] ] = $rwsOpt[$i]['title'] .' ('. Lang::numFrm(  $rwsOpt[$i]['count']). ' ' . Lang::getVal( 'vote') . ' | ' .Lang::numFrm( round((($rwsOpt[$i]['count'] / $pollSum ) * 100), 2, PHP_ROUND_HALF_DOWN)). ' ' . Lang::getVal( 'percent') .')';
		}
		
		$form[] = $inpt -> rdoBx( 'option', 0, array( 
												'items'	=> & $items,
												'class'	=> & Lang::$info['dir'],
												//'values' 	=> $inpt -> getVal( 'option'),
												'delimiter'	=> '&nbsp;<br />',
												//'disabled'	=>	false,
										),
										false //template...
								);
		unset( $items);
													
		
		for( $i = 0; $i != sizeof( $form); $i++)
		{
			$tpl -> assign_block_vars( 'myblck',  array(
					'INPUT' => & $form[ $i]
				)
			);
		}

	//End of Prepare Form Elements, and sent to Template-->	

	if( !sizeof( $rw))
	{
		notFound( NULL); //Send Only 404 Header...
	}

	$tpl -> display( 'header');
	$tpl -> display( 'full');
?>
