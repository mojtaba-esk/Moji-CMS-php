<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	$_GET['id'] = $_REQUEST['id'] = $_POST['id'] = Session::$userId;

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

	if( Module::$opt['adminAvator'])
	{
		$file = new File( Module::$name);
		Img::setPrfx( Module::$name);
	}

	//<!-- Update rows
	
		if( $_GET['mod'] == 'edt' && isset( $_POST['submit']))
		{
			while( $cols = $inpt -> getRow())
			{

				User::load();
				
				if( User::$info['password'] != User::hash( $cols['oldPassword'], User::$info['regDate']))
				{
					$msg = Lang::getVal( 'wrongOldPassword');
					break;
				}

				//<!-- Upload The image ...
						
					if( isset( $cols[ 'del_image']))
					{
						$file -> delete( $_GET['id']);
					}
					$files = $inpt -> getFiles();
					$files['image']['name'] and $file -> save( $_GET['id'], $files[ 'image'][ 'tmp_name']);
						
				//End of Upload The image-->

				if( Module::$opt['adminChngUsername'] && User::$info['username'] != @$cols['username'])
				{
					
					$cols['username'] = User::clean( $cols['username']);

					$SQL = "
						SELECT
							COUNT(*) AS `total`
						FROM
							`". User::$tblPrfx . "_main`
						WHERE
							`id` != {$_GET['id']}
							AND
								`username` = '{$cols['username']}'
						";

					$exRw = DB::load( $SQL);
					
					if( $exRw[0]['total'])
					{
						$msg = Lang::getVal( 'usernameExist');
						break;
					}
					
					$updCols['username'] = $cols['username'];

				}//End of if( Module::$opt['adminChngUsername'] && User::$info['username'] != @$cols['username']);

				if( !empty( $cols['newPassword'])) $updCols['password'] = User::hash( $cols['newPassword'], User::$info['regDate']);

				$updCols['firstName']	= $cols['firstName'];
				$updCols['lastName']	= $cols['lastName'];
				$updCols['email']		= $cols['email'];
				
				//$cols[ 'updteTime'] = time();
				DB::update( array(
						'tableName' => User::$tblPrfx . '_main',
						'cols' 	=> & $updCols,
						'where'	=> array(
							'id' => $_GET['id'],
						),
					), true
				);
				
				sLog( array(
							'desc'	=> Lang::getVal( 'profileEdit'),
						)
					);
				
				//<!-- Save the Search key words... ( Indexing)

					isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
					$srch -> setIndexes( $updCols['firstName'] .' '. $updCols['lastName'], $_GET['id'], 0);

				//End of Search-->
				
				msgDie( Lang::getVal( 'updated'), './'. URL::get( array( 'id')), 1);
				return;

			}//End of while( $cols = $inpt -> getRow());

		}//End of if( $_GET['mod'] == 'edt' && isset( $_POST));
	
	//End of Update rows -->

	$tpl -> set_filenames( array(
		'edit' => Module::$name .'.admin.edit',
		)
	);

	if( $_GET['mod'] == 'edt' && !isset( $_POST['submit']))
	{
		$rws = DB::load(
			array( 
				'tableName' => User::$tblPrfx . '_main',
				'where' => array(
					'id' => intval( $_GET['id']),
				),
			)
		);

		$inpt -> setVals( $rws);//Call for Each Data Instance;

	}//End of if( $_GET['mod'] == 'edt');

	// $msg = 'Gholi kochooolooo';
	
	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'submit'),
		'CANCEL' => Lang::getVal( 'cancel'),
		
		'RETURN_URL' => '?md='. Module::$name .'&mod=edt',

		)
	);
	
	//<!-- Prepare Form Elements...

		$form[] = $inpt -> prValidate( 'form' /* HTML Form Id*/,
			'{
				rules: {
					confirmPassword: {
						equalTo: "#newPassword"
					}
				},
				messages: {
					confirmPassword: {
						equalTo: "'. Lang::getVal( 'validatorPasswordEqualTo') .'"
					}
				}
			}'
		);

		if( Module::$opt['adminAvator'])
		{
			$imgSrc = $file -> getPth( intval( @$_REQUEST['id']));
			$imgSrc and $imgSrc = '../'. Img::get( $imgSrc, array( 'h' => 120 ));
			$form[] = $inpt -> imgUpld( 'image', 0, $imgSrc);
		
		}//End of if( Module::$opt['imageFile']);

		$form[] = $inpt -> text( 'firstName', 0, array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'size' => 40));
		$form[] = $inpt -> text( 'lastName', 0, array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'size' => 40));
		$form[] = $inpt -> html();
		
		if( Module::$opt['adminChngUsername'])
		{
		
			$form[] = $inpt -> text( 'username', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 25, 'validate' => 'required'));
		
		}else{
		
			$form[] = $inpt -> text( 'username', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 25, 'disabled' => 'disabled'));

		}//End of if( Module::$opt['adminChngUsername']);
		
		$form[] = $inpt -> text( 'email', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 25, 'validate' => 'required email'));
		
		$form[] = $inpt -> html();
		$form[] = $inpt -> text( 'oldPassword', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 30, 'validate' => 'required', 'type' => 'password', 'value' => ''));
		$form[] = $inpt -> html( 'hint', Lang::getVal( 'passwordChangeHint'));

		$form[] = $inpt -> text( 'newPassword', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 30, 'type' => 'password', 'id' => 'newPassword', 'value' => ''));
		$form[] = $inpt -> text( 'confirmPassword', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 30, 'type' => 'password', 'name' => 'confirmPassword', 'value' => ''));
		$form[] = $inpt -> html();

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
