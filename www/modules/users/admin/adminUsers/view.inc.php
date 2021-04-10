<?php
/**
* @author Mojtaba Eskandari
* @since 2010-Oct-31
* @name Module Admin Panel. view data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	lib( array(
			
//			'Tab',
			'File',
			'Img',
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

	if( Module::$opt[ $_GET['sub'] .'ImageFile'])
	{
		$file = new File( Module::$name);
		Img::setPrfx( Module::$name);
	}
	
	$tpl -> set_filenames( array(
		'edit' => $_GET['sub'] .'.sub.admin.edit',
		)
	);

	$rws = DB::load(
		array( 
			'tableName' => 'admin_users_main',
			'where' => array(
				'id' => intval( $_GET['id']),
			),
		)
	);
	
	$row = & $rws[0];

	// $msg = 'Gholi kochooolooo';
	
	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'submit'),
		'CANCEL' => Lang::getVal( 'cancel'),
		
		'EDIT_MOD' => NULL,
		
		'SUB_NAME' => Lang::getVal( $_GET['sub']),
		
		'RETURN_URL'	=> '?md='. Module::$name .'&sub='. $_GET['sub'],
		'MODULE_URL'	=> '?md='. Module::$name,

		)
	);
	
	//<!-- Prepare Form Elements...

		if( Module::$opt[ $_GET['sub'] .'ImageFile'])
		{
			$imgSrc = $file -> getPth( intval( @$_GET['id']));
			$imgSrc and $imgSrc = '../'. Img::get( $imgSrc, array( 'h' => 120 ));
			$imgSrc and $form[] = $inpt -> html( 'image', '<img src="'. $imgSrc .'" alt="" />');
		
		}//End of if( Module::$opt[ $_GET['sub'] .'ImageFile']);

		@Module::$opt['showId'] && isset( $rws) and $form[] = $inpt -> html( 'fileCode', $row[ 'id']);
		$form[] = $inpt -> html( 'firstName', $row[ 'firstName']);
		$form[] = $inpt -> html( 'lastName', $row[ 'lastName']);
		$form[] = $inpt -> html();
		
		isset( $grInf) or $form[] = $inpt -> html( 'username', $row[ 'username']);
		$form[] = $inpt -> html( 'email', $row[ 'email']);

		//<!-- Groups Drop Down List...
		
		if( !isset( $grInf))
		{
			$SQL = 'SELECT `id`, `title` FROM `admin_users_groups` WHERE `id` = '. $row[ 'groupId'];
			$gRws = DB::load( $SQL);
			$form[] = $inpt -> html( 'groupId', $gRws[0]['title']);
		}

		//End of Groups Drop Down List.-->

		$form[] = $inpt -> html();
		isset( $grInf) or $form[] = $inpt -> html( 'active', $row[ 'active'] ? Lang::getVal( 'yes') : Lang::getVal( 'no'));
		isset( $grInf) or isset( $rws) and $form[] = $inpt -> html( 'regDate', Lang::numFrm( Date::get( 'D d M Y - G:i', $row[ 'regDate'])));
		isset( $grInf) or isset( $rws) and $form[] = $inpt -> html( 'lastLoginDate', Lang::numFrm( Date::get( 'D d M Y - G:i', $row[ 'lastLoginDate'])));
		isset( $grInf) or isset( $rws) and $form[] = $inpt -> html( 'lastLoginIP', $row[ 'lastLoginIP']);
		
		for( $i = 0; $i != sizeof( $form); $i++)
		{
			$tpl -> assign_block_vars( 'myblck',  array(
					'INPUT' => & $form[ $i]
				)
			);
		}

	//End of Prepare Form Elements, and sent to Template-->

	$tpl -> display( 'edit');
?>
