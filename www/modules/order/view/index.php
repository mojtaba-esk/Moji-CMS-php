<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module View, Contact us Form;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	//<!-- Preaper Input Object ...
	
		$lngs = Lang::getAll();
		//require( $_cfg['path'] .'/inc/lib/Input.class.inc.php');
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$lngsIds[] = $lngs[ $i ][ 'id' ];
			$lngsTitle[ $lngs[ $i ][ 'id' ] ] = $lngs[ $i ][ 'title'];
		}

		lib( array( 'Input'));
		$inpt = new Input( $lngsIds);

	//End of Preaper Input Object -->

	if( isset( $_POST['submit']))
	{
		$tpl -> assign_vars( array(
			'PAGE_TITLE'	=> Lang::getVal( 'siteTitle') .' | '. Lang::getval( Module::$name),
			)
		);

		$tpl -> display( 'header');
		if( !( $msg = include( 'send.inc.php')))return;

	}//End of if( isset( $_POST['submit']));

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
	
	//<!-- Fetch The Record By Name

		$rws = DB::load(
			array( 
				'tableName' => Module::$name . '_main',
				'where'	=> array( 
					'name'	=> & Module::$name,
					'lngId'	=> Lang::viewId(),
					'productId'	=> $_cfg['product']['id'],
				),
			), true
		);

		$rw	= & $rws[0];
		$_GET['id'] = $rw['rltdId'];
		
	//End of Fetch The Record By Name-->
	
	//<!-- Prepare the Attachements
	
		if( Module::$opt['attchmnt'])
		{
			include( 'attchmnt.inc.php');
			
		}//End of if( Module::$opt['attchmnt']);

	//End of Prepare the Attachements -->
	
	//<!-- Send vars to Template . . .
	
		$tpl -> assign_vars( array(

			'PAGE_TITLE'	=> Lang::getVal( 'siteTitle') .' | '. Lang::getval( $rw['name']),
			'PAGE_KEYWORDS'	=> Lang::getVal( 'siteTitle') .','.	Lang::getval( $rw['name']),
			'PAGE_DESC'		=> Lang::getVal( 'siteTitle') .' - '. Lang::getval( $rw['name']) .' - '. briefStr( strip_tags( $rw['body']), 80),

			'MESSAGE'=> @$msg,
			
			'L_FORM_TITLE' => Lang::getVal( $rw['name'] .'_form'),
			'SUBMIT' => Lang::getVal( 'submit'),
			'CANCEL' => Lang::getVal( 'cancel'),
			
			'NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
			'DATA_EXIST'	=> sizeof( $rw),
			
			'ATCHMNT'		=>	isset( $atchRws) && sizeof( $atchRws),
			
			'ITEM_TITLE'=>	Lang::getval( $rw['name']),
			'ITEM_BODY'	=>	$rw['body'],
			
			'ITEM_IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'h' => 200 /*'h' => 400, 'w' => 300 /**/)),
			
			)
		);

	//End of Send vars to Template -->
	
	//<!-- Prepare Form Elements...
	
		$form[] = $inpt -> prValidate( 'myFrm' /* HTML Form Id*/);

		$form[] = $inpt -> text( 'name', 0, array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'size' => 40));
		$form[] = $inpt -> text( 'email', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 40, 'validate' => 'email'));

		$form[] = $inpt -> text( 'subject', 0, array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'size' => 40));
		$form[] = $inpt -> textArea( 'body', 0, array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'cols' => '60', 'rows' => '15', 'validate' => 'required'));
		
		if( Module::$opt['emailsList'])
		{
			$SQL = 'SELECT `rltdId`, `title` FROM `'. Module::$name .'_emails` WHERE `lngId` = '. Lang::viewId();
			$rws = DB::load( $SQL, Module::$name .'_emails');
			
			$rws or $rws = array();
			foreach( $rws as $rw)
			{
				$items[ $rw[ 'rltdId']] = $rw[ 'title'];
			}

			$form[] = $inpt -> dropDown( 'emailId', 0, array( 
																			'items'	=> @$items,
																			'dir'		=> Lang::$info['dir'],
																			'align'	=> Lang::$info['align']
																		)
																);
			
		}//End of if( Module::$opt['emailsList']);

		$form[] = $inpt -> captcha( 'captcha', 0);
		
		for( $i = 0; $i != sizeof( $form); $i++)
		{
			$tpl -> assign_block_vars( 'fblck',  array(
					'INPUT' => & $form[ $i]
				)
			);
		}

	//End of Prepare Form Elements, and sent to Template-->

	isset( $_POST['submit']) or $tpl -> display( 'header');
	$tpl -> display( 'full');
?>
