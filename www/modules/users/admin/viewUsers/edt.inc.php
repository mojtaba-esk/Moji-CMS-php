<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	if( isset( $grInf))
	{
		msgDie( Lang::getVal( 'accessDenied'), NULL, 0, 'error');
		return;
	}

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
	
	//<!-- Insert new rows
	
		if( $_GET['mod'] == 'new' && isset( $_POST['submit']))
		{
			while( $cols = $inpt -> getRow())
			{
				//<!-- Check for username Exisibility
	
					$cols['username'] = User::clean( $cols['username']);

					$SQL = "
						SELECT
							COUNT(*) AS `total`
						FROM
							`view_users_main`
						WHERE
							`username` = '{$cols['username']}'
						";

					$exRw = DB::load( $SQL);
					
					if( $exRw[0]['total'])
					{
						$msg = Lang::getVal( 'usernameExist');
						break;
					}

				//End of Check for username Exisibility.-->
				
				$cols['regDate'] = time();
				$cols['password'] = User::hash( $cols['newPassword'], $cols['regDate']);
				
				unset( $cols['lngId'], $cols['newPassword']);
				
				DB::insert( array(
						'tableName' => 'view_users_main',
						'cols' => & $cols,
					)
					, Module::$name /* Cache Prefix*/
				);

				$_GET['id'] = DB::insrtdId();
				
				sLog( array(
							'desc'	=> $cols['username'] .' - '. $cols['firstName'] .' '. $cols['lastName'],
						)
					);

				//<!-- Upload The image ...
				
					$files = $inpt -> getFiles();
					$file -> save( $_GET['id'], $files[ 'image'][ 'tmp_name'], 0, 'vImg.');
				
				//End of Upload The image-->
					
				//<!-- Save the Search key words... ( Indexing)

					isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
					$srch -> setIndexes( $cols['firstName'] .' '. $cols['lastName'], $_GET['id'], 0);

				//End of Search-->

				msgDie( Lang::getVal( 'inserted'), './?md='. Module::$name .'&sub='. $_GET['sub'], 1);
				return;

			}//End of while( $cols = $inpt -> getRow());
			
		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End of Insert new rows -->

	//<!-- Update rows
	
		if( $_GET['mod'] == 'edt' && isset( $_POST['submit']))
		{
			while( $cols = $inpt -> getRow())
			{

				//<!-- Upload The image ...
						
					if( isset( $cols[ 'del_image']))
					{
						$file -> delete( $_GET['id'], 0, 'vImg.');
						unset( $cols[ 'del_image']);
					}
					$files = $inpt -> getFiles();
					$files['image']['name'] and $file -> save( $_GET['id'], $files[ 'image'][ 'tmp_name'], 'vImg.');
						
				//End of Upload The image-->
				
				//<!-- Check for username Exisibility
	
					$cols['username'] = User::clean( $cols['username']);

					$SQL = "
						SELECT
							COUNT(*) AS `total`
						FROM
							`view_users_main`
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

				//End of Check for username Exisibility.-->

				if( empty( $cols['newPassword']))
				{
					unset( $cols['password']);

				}else{
				
					//for View users, change the mode, temporary.
					$tmpP = User::$tblPrfx;
					User::$tblPrfx = 'view_users';

					$cols['password'] = User::hash( $cols['newPassword'], $cols['regDate']);

					User::$tblPrfx = $tmpP;
					unset( $tmpP);
				
				}//End of if( empty( $cols['newPassword']));

				$cols['active'] = intval( @$cols['active']);

				unset( $cols['lngId'], $cols['regDate'], $cols['newPassword']);
				DB::update( array(
						'tableName' => 'view_users_main',
						'cols' 	=> & $cols,
						'where'	=> array(
							'id' => $_GET['id'],
						),
					), Module::$name /* Cache Prefix*/
				);


				sLog( array(
							'desc'	=> $cols['username'] .' - '. $cols['firstName'] .' '. $cols['lastName'],
						)
					);
				
				//<!-- Save the Search key words... ( Indexing)

					isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
					$srch -> setIndexes( $cols['firstName'] .' '. $cols['lastName'], $_GET['id'], 0);

				//End of Search-->
				
				msgDie( Lang::getVal( 'updated'), './'. URL::get( array( 'id', 'mod')), 1);
				return;

			}//End of while( $cols = $inpt -> getRow());

		}//End of if( $_GET['mod'] == 'edt' && isset( $_POST));
	
	//End of Update rows -->

	$tpl -> set_filenames( array(
		'edit' => $_GET['sub'] .'.sub.admin.edit',
		)
	);

	if( $_GET['mod'] == 'edt' && !isset( $_POST['submit']))
	{
		$rws = DB::load(
			array( 
				'tableName' => 'view_users_main',
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
		
		'EDIT_MOD' => 1,
		
		'SUB_NAME' => Lang::getVal( $_GET['sub']),
		
		'RETURN_URL'	=> '?md='. Module::$name .'&sub='. $_GET['sub'],
		'MODULE_URL'	=> '?md='. Module::$name,

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

		if( Module::$opt[ $_GET['sub'] .'ImageFile'])
		{
			$imgSrc = $file -> getPth( intval( @$_GET['id']), 0, 'vImg.');
			$imgSrc and $imgSrc = '../'. Img::get( $imgSrc, array( 'h' => 120 ));
			$form[] = $inpt -> imgUpld( 'image', 0, $imgSrc);
		
		}//End of if( Module::$opt[ $_GET['sub'] .'ImageFile']);

		
		@Module::$opt['showId'] && isset( $rws) and $form[] = $inpt -> html( 'fileCode', $inpt -> getVal( 'id'));
		$form[] = $inpt -> text( 'firstName', 0, array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'size' => 30));
		$form[] = $inpt -> text( 'lastName', 0, array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'size' => 30));
		$form[] = $inpt -> html();
		
		$form[] = $inpt -> text( 'username', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 25, 'validate' => 'required'));
		$form[] = $inpt -> text( 'email', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 25, 'validate' => 'required email'));

		//<!-- Groups Drop Down List...
	
			$SQL = 'SELECT `id`, `title` FROM `view_users_groups`';
			$gRws = DB::load( $SQL);

			$itms = array( 0 => '');
			$gRws or $gRws = array();
			foreach( $gRws as $rw)
			{
				$itms[ $rw[ 'id']] = $rw[ 'title'];
			}
			unset( $gRws);

			$form[] = $inpt -> dropDown( 'groupId', 0, array( 
												'items'	=> $itms,
												'dir'	=> Lang::$info['dir'],
												'align'	=> Lang::$info['align']
										)
								);

		//End of Groups Drop Down List.-->
		
		
		$form[] = $inpt -> html();
		isset( $rws) and $form[] = $inpt -> html( 'hint', Lang::getVal( 'passwordChangeHint'));
		$form[] = $inpt -> text( 'newPassword', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 30, 'type' => 'password', 'id' => 'newPassword', 'value' => ''));
		$form[] = $inpt -> text( 'confirmPassword', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 30, 'type' => 'password', 'name' => 'confirmPassword', 'value' => ''));
		$form[] = $inpt -> html();
		
		$form[] = $inpt -> chkBx( 'active', 0, array( 'value' => 1));
		
		//$form[] = $inpt -> html();
		/* in Edit Mode.*/ isset( $rws) and $form[] = $inpt -> hidden( 'regDate');
		isset( $rws) and $form[] = $inpt -> html( 'regDate', Lang::numFrm( Date::get( 'D d M Y - G:i', $rws[0][ 'regDate'])));
		isset( $rws) and $form[] = $inpt -> html( 'lastLoginDate', Lang::numFrm( Date::get( 'D d M Y - G:i', $rws[0][ 'lastLoginDate'])));
		isset( $rws) and $form[] = $inpt -> html( 'lastLoginIP', $rws[0][ 'lastLoginIP']);
		
		//<!-- Prepare the Languages Tabs ...
		
			/*$tab = new Tab( $lngsTitle, Lang::viewId());
			$form[] = '<tr><td colspan="2">'. $tab -> bar();

			foreach( $lngs as $lng)
			{
				$form[] = $tab -> opn( $lng);
				
				$form[] = $inpt -> text( 'firstName', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'size' => 40));
				$form[] = $inpt -> text( 'lastName', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'size' => 40));

				$form[] = $tab -> clos();
			}
			$form[] = '</td></tr>';
			/**/

		//End of Prepare the Languages Tabs-->

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
