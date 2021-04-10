<?php
/**
* @author Mojtaba Eskandari
* @since 2009-12-25
* @name Module Admin Panel. Login Form;
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
	
	$msg = NULL;
	if( isset( $_POST['submit']))
	{
	
		$cols = $inpt -> getRow();

		if( Module::$opt['adminCaptcha'])
		{
			//<!-- Validate Security Captcah Code
	
				lib( array( 'Captcha'));
				$sImg = new Captcha( Module::$opt['id']);
		
				if( strtolower( $cols['captcha']) != $sImg -> GetCode())
				{
					$msg = Lang::getVal( 'wrongCaptcha');
				}
				$sImg -> Remove();//Remove the Sec-Code from System.
		
			//-->

		}//End of if( Module::$opt['adminCaptcha']);
		
		if( !$msg)
		{
			if( User::login( $cols['username'], $cols['password']))
			{
				sLog( array(
						'itemId'	=> Session::$userId,
						'desc'		=> Lang::getVal( 'login'),
					)
				);
				
				//<!-- Find the Permitted product Id...
					
					$_SESSION['pId'] = 0;
					
					$SQL = 'SELECT 
									`g`.`key`
							FROM
								`admin_users_groups`	AS	`g`,
								`admin_users_main`		AS	`u`
							WHERE
								`u`.`id` = 0'. Session::$userId .'
								AND
									`g`.`id` = `u`.`groupId`
							LIMIT 1';
					$prIdRw = DB::load( $SQL);
					
					$_SESSION['pId'] = @$prIdRw[0]['productId'];
					if( $_SESSION['pId'])
					{
						$_SESSION['lmtPrId'] = 1; // Limited to a specific product Id.
					}
					
					if( @$prIdRw[0]['productId'] == 'admins')
					{
						unset( $_SESSION['pId'], $_SESSION['lmtPrId']);
					}
					
					//printr( $_SESSION);

				//-->
				
				msgDie( Lang::getVal( 'redirecting'), './', 1, 'info', Lang::getVal( 'adminPanel'), true /*No ajax*/);
				return;
			}
			$msg = implode( '<br />', User::$msg);

		}//End of if( !$msg);
		
		$rws = array(
					0 => array(
							'username' => $cols['username'],
							'password' => '',
					)
			);
		//$rws = array();
		$inpt -> setVals( $rws);

	}//End of if( isset( $_POST['submit']));
	
	$tpl -> set_filenames( array(
		'body' => $_GET['sub'] .'.sub.admin',
		)
	);
	
	$tpl -> assign_vars( array(

		'ADMIN_MENU'	=> NULL,
		'MESSAGE'		=> $msg,
		'LOGIN'			=> Lang::getVal( 'login'),

		)
	);


	//<!-- Prepare Form Elements...

		//<!-- Prepare the Languages Tabs ...

			$form[] = $inpt -> prValidate( 'myFrm' /* HTML Form Id*/);

			$form[] = $inpt -> text( 'username', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 20, 'validate' => 'required', 'focus' => 1));
			$form[] = $inpt -> text( 'password', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 20, 'type' => 'password', 'validate' => 'required'));

			Module::$opt['adminCaptcha'] and $form[] = $inpt -> captcha( 'captcha', 0);

		//End of Prepare the Languages Tabs-->

		for( $i = 0; $i != sizeof( $form); $i++)
		{
			$tpl -> assign_block_vars( 'myblck',  array(
					'INPUT' => & $form[ $i]
				)
			);
		}

	//End of Prepare Form Elements, and sent to Template-->

	$tpl -> display( 'body');
?>
