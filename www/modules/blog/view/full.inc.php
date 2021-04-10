<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

//Begin of code part from CMS Moji

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	lib( array(
			'Input'
		)
	);
	
	//<!-- Preaper Input Object ...
	
		$lngs = Lang::getAll();
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$lngsIds[] = $lngs[ $i ][ 'id' ];
			$lngsTitle[ $lngs[ $i ][ 'id' ] ] = $lngs[ $i ][ 'title'];
			break;
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
	
	$_GET['id'] = intval( $_GET['id']);
	
//End of code part from CMS Moji

//Begin of code part from Hooman

	//<!--Start Of Inserting A New Comment Into Database
	
		if( isset( $_POST['submit']))
		{
			$rltdId = $frstLngId = 0;
			while( $cols = $inpt -> getRow())
			{
				//IF The Required fields are empty, ignore the insertion action.
				if( !$cols['name'] || !$cols['body']) continue;
	
				//<!--insert the required part of comments into database
				$iCols['body']	= $inpt -> dbClr( $cols['body']);
				$iCols['name']	= $inpt -> dbClr( $cols['name']);
				$iCols['email']	= $inpt -> dbClr( $cols['email']);
				$iCols['title']	= $inpt -> dbClr( $cols['topic']);
				$iCols['parentId']	= $inpt -> dbClr( $cols['parentId']);
				$iCols['itemId']	= intval($_GET['id']);
				$iCols['domId']	= intval($_cfg['domain']['id']);				
				//<!--End of insert the required part of comments into database				
				
				//<!-- Preaper Related Id

					/* For each Row ( rws) reset the Related Id*/
					$frstLngId or $frstLngId = $cols['lngId'];
					$frstLngId == $cols['lngId'] and $rltdId = 0;
					$rltdId and $cols[ 'rltdId'] = $rltdId;

				//End of Preaper Related Id-->

				$cols[ 'domId'] = $_cfg['domain']['id'];
				$cols[ 'insrtTime'] = time();
				DB::insert( array(
						'tableName' => Module::$name . '_comment',
						'cols' => & $iCols,
					)
					,  Module::$name /* Cache Prefix*/
				);
				
			}//End of while( $cols = $inpt -> getRow());
			
			$tpl -> display( 'header');
			msgDie( Lang::getVal( 'inserted'), './?md='. Module::$name, 1);
			return;

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End Of Inserting A New Comment Into Database-->

	
	//<!--Start Of Inserting A New Reply Into Database
	
		if( isset( $_POST['reply']))
		{
			$rltdId = $frstLngId = 0;
			while( $cols = $inpt -> getRow())
			{

				//IF The Required fields are empty, ignore the insertion action.
				if( !$cols['name'] || !$cols['body']) continue;
				
				$iCols['body']	= $inpt -> dbClr( $cols['body']);
				$iCols['name']	= $inpt -> dbClr( $cols['name']);
				$iCols['email']	= $inpt -> dbClr( $cols['email']);
				$iCols['title']	= $inpt -> dbClr( $cols['topic']);
				$iCols['parentId']	= intval( $cols['parentId']);
				$iCols['itemId']	= intval($_GET['id']);
				$iCols['domId']	= intval($_cfg['domain']['id']);				

				//<!-- Preaper Related Id

					/* For each Row ( rws) reset the Related Id*/
					$frstLngId or $frstLngId = $cols['lngId'];
					$frstLngId == $cols['lngId'] and $rltdId = 0;
					$rltdId and $cols[ 'rltdId'] = $rltdId;

				//End of Preaper Related Id-->

				$cols[ 'domId'] = $_cfg['domain']['id'];

				$cols[ 'insrtTime'] = time();
				DB::insert( array(
						'tableName' => Module::$name . '_comment',
						'cols' => & $iCols,
					)
					,  Module::$name /* Cache Prefix*/
				);
				
			}//End of while( $cols = $inpt -> getRow());
			
			$tpl -> display( 'header');
			msgDie( Lang::getVal( 'inserted'), './?md='. Module::$name, 1);
			return;

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End Of Inserting A New Reply Into Database-->

	
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

		$rws = DB::load( $SQL, Module::$name . '_main');
		$rw	= & $rws[0];
		
		if( $rw['pblishTime'] > time()) unset( $rw, $rws);
		
	//End of Fetch The Record By Related Id-->
	
	//<!-- Prepare the Attachements
	
		if( Module::$opt['attchmnt'])
		{
			include( 'attchmnt.inc.php');
		}//End of if( Module::$opt['attchmnt']);

			
	//<!-- Prepare the Attachements	

	
	//<!-- Send vars to Template . . .
		$tpl -> assign_vars( array(
			'PAGE_TITLE'	=> $_cfg['domain']['title'] .' | '. Lang::getVal( Module::$name) .' | '. $rw['title'],
			'PAGE_KEYWORDS'	=> $_cfg['domain']['title'] .','.	Lang::getVal( Module::$name) .','.	$rw['title'],
			'PAGE_DESC'		=> $_cfg['domain']['title'] .' - '. Lang::getVal( Module::$name) .' - '. briefStr( isset( $rw['lead']) ? $rw['lead'] : strip_tags( $rw['body']), 80),

			'L_NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
			'DATA_EXIST'	=> sizeof( $rw),

			'ATCHMNT'			=>	isset( $atchRws) && sizeof( $atchRws),
			'L_ATTACHEMENTS'	=>	Lang::getVal( 'attachements'),
			'L_DOWNLOAD_HITS'	=>	Lang::getVal( 'downloadHits'),
			'L_FILE_SIZE'		=>	Lang::getVal( 'fileSize'),
			'L_KB'				=>	Lang::getVal( 'KB'),

			'ITEM_TITLE'=>	& $rw['title'],
			'ITEM_BODY'	=>	& $rw['body'],
			'CAT_TITLE'	=>	@$rw['catTitle'],
			'CAT_URL'	=>	URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&catId='. $rw['catId']),
			
			'ITEM_IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'h' => 400, 'w' => 300)),
			
			'L_PUBLISH_TIME'	=> Lang::getVal( 'pblishTime'),
			'ITEM_PUBLISH_TIME'	=> Lang::numFrm( Date::get( 'D d M Y G:i', $rw[ 'pblishTime'])),
			
			'SHORT_DESC' => & $rw['shortDesc'],
			'SUBMIT' =>	Lang::getVal( 'sendcomment'),
			'REPLY' =>	Lang::getVal( 'replycomment'),
			'RATE_URL' => '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&mod=rate&id='. intval( $_GET['id']),
			'LIKE_URL' => '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&mod=like&id='. intval( $_GET['id']),
			)
		);
	//End of Send vars to Template -->
	if( !sizeof( $rw))
	{
		notFound( NULL); //Send Only 404 Header...
	}
	
		//<!-- Prepare Form Elements...
	
		$form[] = $inpt -> prValidate( 'RplyFrm' /* HTML Form Id*/);
		
		$form[] = $inpt -> hidden( 'parentId', 0 , 0, array('id' => 'parentId'));		
		$form[] = $inpt -> text( 'name', 0 , array( 'class' => & Lang::$info['dir'], 'size' => 40,'validate' => 'required'));
		$form[] = $inpt -> text( 'email', 0 , array( 'class' => 'ltr', 'size' => 40,'validate' => 'email'));
		$form[] = $inpt -> text( 'topic', 0 , array( 'class' => & Lang::$info['dir'], 'size' => 40,'validate' => 'required'));
		$form[] = $inpt -> textArea( 'body', 0, array( 'class' => & Lang::$info['dir'], 'cols' => '60', 'rows' => '15', 'validate' => 'required'));

	//End of Prepare Form Elements, and sent to Template-->
		for( $i = 0; $i != sizeof( $form); $i++)
		{
			$tpl -> assign_block_vars( 'myblck2',  array(
					'INPUT2' => & $form[ $i]
				)
			);
		}
	
	
	//<!-- Prepare Form Elements...
		unset($form);
		$form[] = $inpt -> prValidate( 'myFrm' /* HTML Form Id*/);
			
		$form[] = $inpt -> hidden( 'parentId', 0 , 0, array('id' => 'parentId'));
		$form[] = $inpt -> text( 'name', 0 , array( 'class' => & Lang::$info['dir'], 'size' => 40,'validate' => 'required'));
		$form[] = $inpt -> text( 'email', 0 , array( 'class' => 'ltr', 'size' => 40,'validate' => 'email'));
		$form[] = $inpt -> text( 'topic', 0 , array( 'class' => & Lang::$info['dir'], 'size' => 40,'validate' => 'required'));
		$form[] = $inpt -> textArea( 'body', 0, array( 'class' => & Lang::$info['dir'], 'cols' => '60', 'rows' => '15', 'validate' => 'required'));

	//End of Prepare Form Elements, and sent to Template-->
		for( $i = 0; $i != sizeof( $form); $i++)
		{
			$tpl -> assign_block_vars( 'myblck',  array(
					'INPUT' => & $form[ $i]
				)
			);
		}	
	
	//Start fetching score from database for show the rating -->
	
	$SQL = '
		SELECT
			`score`
		FROM
			`'. Module::$name . '_rate` 
		WHERE
			rltdId='.$_GET['id'];

	lib( array( 'Paging'));
	$pging = new Paging( array(
				'SQL'		=> $SQL,
				'perPage'	=> Module::$opt['viewLstLmt'],
			)
		);
	
	$SQL = $pging -> getSQL();
	$rws_score = DB::load( $SQL);
	
	//End of fetching score from database for show the rating
	
	//start of sending the fetched score into the template
		if( is_array( $rws_score))
	{
		foreach( $rws_score as $key => $rw)
		{
			$tpl -> assign_block_vars( 'scoreblck',  array(
				'RATESCORE' => intval($rw['score']),
				)
			);

		}//End of foreach( $rws_score as $key => $rw)
	
	}//End of if( is_array( $rws_score))
	
	//END of sending the fetched score into the template
	
	//Start of Calling Recursive Function for fetching the comments and the answers-->
	
	require( dirname( __FILE__) .'/../admin/functions.inc.php');
	
	$rws = recLoad( Module::$name. '_comment', ' `itemId` = '. $_GET['id'] .' AND `domId` = '. $_cfg['domain']['id']);

	//End of Calling Recursive Function for fetching the comments and the answers-->
	
	//Start showing the fetched comments and replies-->

	if( is_array( $rws))
	{

		foreach( $rws as $key => $rw)
		{

			$tpl -> assign_block_vars( 'cmtblck',  array(

				'USERNAME' => $rw['name'],
				'ID' => $rw['id'],
				'TOPIC' => $rw['title'],
				'LEVEL' => $rw['level'] * 3,
				'LEVEL2' => 71.9 - $rw['level'] * 3,
				'CMTCONTENT' => nl2br( $rw['body']),
				'CNT' => true,
				'LIKE' => $rw['like'],
				'UNLIKE' => $rw['unlike'],
				)
			);
		}//End of foreach( $rws as $key => $rw);
	
	}//End of if( sizeof( $rws));
	
	//End of showing the fetched comments and replies-->
	
	
	//end of sending the fetched score into the template
	$tpl -> display( 'header');
	$tpl -> display( 'full');
?>
