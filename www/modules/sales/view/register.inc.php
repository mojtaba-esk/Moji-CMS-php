<?php
/*
* Author: Mojtaba Eskandari
* Started at 2011-04-02
* Active mode for the products.
*/
	//printr( base64_encode( '123-123-124/1.1.9.0'));

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	lib( array(
			'Input',
			'Session',
			'Search'
		)
	);

	$tpl -> set_filenames( array(
		'body' => Module::$name .'.view.'. $_GET['mod'],
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
	
	//<!-- fetch the product information...

		$pInf = DB::load( "SELECT * FROM `products_main` WHERE `id` = {$_cfg['product']['id']}");
		$pInf = & $pInf[0];

	//-->

	//<!-- Get the parameters...
	
		$infoArr = explode( '/', base64_decode( $_GET['info']));
		
		//Clear the input serial...
		$lockSerial = preg_replace( '([^0-9\-]*)', '', $infoArr[0]);
		$version = $infoArr[1];
		
		//<!-- Fetch the version ID...

			$verRw = DB::load(
				array( 
					'tableName' => 'products_versions',
					'where' => array(
						'title' => & $version,
					),
				)
			);

			$verId = $verRw[0]['id'];
			unset( $verRw);

		//-->			

	//-->
	
	//<!-- Redirect the user to the Activation page if the serial has been registered...
		
			$tmpRw = DB::load( array(
					'tableName' => Module::$name . '_main',
					'cols' 	=> array( 'rltdId'),
					'where'	=> array(
						'lockSerial' => & $lockSerial,
					),
				)
			);
			
			if( !empty( $tmpRw))
			{
				$tpl -> display( 'header');

				//msgDie( Lang::getVal( 'inserted'), NULL, 1);
				msgDie( Lang::getVal( 'serialExisted'), $_cfg['URL']. 'activation/'. $lockSerial, 1, 'info', Lang::getVal( 'activation'));
				return;

			}//End of if( !empty( $tmpRw));

	//-->
	
	/*------------------------------------------------------*/

	if( isset( $_POST['submit']))
	{

			$rltdId = $frstLngId = 0;
			while( $cols = $inpt -> getRow())
			{
				//<!-- Validate Security Captcah Code

					lib( array( 'Captcha'));
					$sImg = new Captcha( Module::$opt['id']);
	
					if( strtolower( $cols['captcha']) != $sImg -> GetCode())
					{
						$msg = Lang::getVal( 'wrongCaptcha');
						break;
					}
					$sImg -> Remove();//Remove the Sec-Code from System.
					unset( $cols['captcha']);
	
				//-->			
			
				$cols['lockSerial'] = & $lockSerial;
				$cols['enableActv'] = 1;
				$cols['verId'] 		= $verId;
				
				//IF The Required fields are empty, ignore the insertion action.
				if( !$cols['lastName'] || !$cols['lockSerial']) continue;
				isset( $cols[ 'saleTime']) and $cols[ 'saleTime'] = Date::mkTime( $cols[ 'saleTime']);
				
				$cols['firstName']	= $inpt -> dbClr( $cols['firstName']);
				$cols['lastName']	= $inpt -> dbClr( $cols['lastName']);
				$cols['coTitle']	= $inpt -> dbClr( $cols['coTitle']);
				$cols['comments']	= $inpt -> dbClr( $cols['comments']);
				
				$cols['nationalCode']	= $inpt -> dbClr( $cols['nationalCode']);
				$cols['tel']			= $inpt -> dbClr( $cols['tel']);
				$cols['mobile']			= $inpt -> dbClr( $cols['mobile']);
				$cols['email']			= $inpt -> dbClr( $cols['email']);
				$cols['website']		= $inpt -> dbClr( $cols['website']);
				$cols['address']		= $inpt -> dbClr( $cols['address']);
				
				$cols['userId']			= 0;
				
				//<!-- Preaper Related Id

					/* For each Row ( rws) reset the Related Id*/
					$frstLngId or $frstLngId = $cols['lngId'];
					$frstLngId == $cols['lngId'] and $rltdId = 0;
					$rltdId and $cols[ 'rltdId'] = $rltdId;

				//End of Preaper Related Id-->
				
				$cols[ 'insrtTime'] = time();
				DB::insert( array(
						'tableName' => Module::$name . '_main',
						'cols' => & $cols,
					)
					,  Module::$name /* Cache Prefix*/
				);
				
				//printr( $cols);
				
				if( !$rltdId)
				{

					//Only for first rows.
					$rltdId = DB::insrtdId();
					DB::update( array(
							'tableName' => Module::$name . '_main',
							'cols' 	=> array( 'rltdId' => $rltdId),
							'where'	=> array(
								'id' => DB::insrtdId(),
							),
						)
					);

				}//End of if( !$rltdId)
				
				//<!-- Save the Search key words... ( Indexing)

					isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
					$srch -> setIndexes( $cols['firstName'] .' '. $cols['lastName'] .' '. @$cols['tel'] .' '. @$cols['mobile'] .' '. @$cols['email'] .' '. @$cols['nationalCode'] .' '. @$cols['coTitle'] .' '. @$cols['comments'], $rltdId, $cols['lngId']);

				//End of Search-->
				
				break;

			}//End of while( $cols = $inpt -> getRow());

			if( empty( $msg))
			{
				$tpl -> display( 'header');

				//msgDie( Lang::getVal( 'inserted'), NULL, 1);
				msgDie( Lang::getVal( 'inserted'), $_cfg['URL']. 'activation/'. $lockSerial, 1, 'info', Lang::getVal( 'activation'));
				return;

			}// End of if( empty( $msg));

	}//End of if( isset( $_POST['submit']));
	
	/*------------------------------------------------------*/
	
	//<!-- Fetch The Record By Name

		//$_GET['name'] = preg_replace("([^0-9a-zA-Z]*)", '', $_GET['name']);

		$pRws = DB::load(
			array( 
				'tableName' => 'pages_main',
				'where'	=> array( 
					'name'	=> & $_GET['mod'], //'register',
					'lngId'	=> Lang::viewId(),
					'productId'	=> $_cfg['product']['id'],
				),
			), true
		);

		$pgRw	= & $pRws[0];
		
	//End of Fetch The Record By Name-->
	
	//<!-- Load The libraries For pages Module.

		lib( array( 'File', 'Img'));
		$file = new File( 'pages');
		Img::setPrfx( 'pages');

	//-->

	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'save'),
		'CANCEL' => Lang::getVal( 'cancel'),
		//'PRINT'	 => Lang::getVal( 'print'),
		
		//'RETURN_URL' => '?md='. Module::$name,
		
		'ACTION_TITLE' => Lang::getVal( 'register'),
		
		//<!-- Static Page..
		
			//'ITEM_TITLE'=>	Lang::getval( $rw['name']),
			'ITEM_BODY'		=>	& $pgRw['body'],
			'ITEM_IMG_SRC'	=>	$_cfg['URL'] . Img::get( $file -> getPth( $pgRw['rltdId']), array( 'h' => 250, 'w' => 250)),

		//-->

		)
	);
	
	/*------------------------------------------------------*/
	
		$form[] = $inpt -> hidden( 'id', $lngsIds /* can send an array of languages Ids*/);
		$form[] = $inpt -> hidden( 'productId', 0, $_cfg['product']['id']);
			
		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'saleInfo'));
		
			$form[] = $inpt -> html( 'lockSerial', '<b>'. $lockSerial .'</b>');

			$form[] = $inpt -> date( 'saleTime', 0 /*Language Id*/, array( 
																'elmnts' => 'd,M,Y',//,G,i',
																//'type' => 'jalali',
																'value' => 'now',
																'defVal'	=> $_GET['mod'] == 'new' ? 'now' : NULL,
																'difY'		=> array( -1, 1),
																'attribs' => array( 
																	'class' => & Lang::$info['dir'],
															)
														)
													);

			Module::$opt['categoryMod'] and $form[] = $inpt -> dropDown( 'catId', 0, array( 
																			'items'	=> getCats( Module::$name, Lang::viewId()), 
																			'dir'		=> Lang::$info['dir'],
																			'align'	=> Lang::$info['align']
																	)
															);

			$form[] = $inpt -> dropDown( 'salerId', 0, array( 
													'items'	=> getCats( Module::$name, Lang::viewId(), 'salers', ' AND `productId` = 0'. $_cfg['product']['id']),
													'class'	=> & Lang::$info['dir'],
												)
										);

		$form[] = $inpt -> fldSetClos();

		//----------------------------------

		//<!-- Prepare the Languages Tabs ...
		
			//$tab = new Tab( $lngsTitle, Lang::viewId());
			//$form[] = '<tr><td colspan="2">'. $tab -> bar();

			//foreach( $lngs as $lng)
			{
				//$form[] = $tab -> opn( $lng);
				
				$form[] = $inpt -> fldSetOpn( Lang::getVal( 'customerInfo'));
				
					$form[] = $inpt -> dropDown( 'gender', 0, array( 
										'items'	=> array( 1 => Lang::getVal( 'male'), 0 => Lang::getVal( 'female')),
										'class'	=> & Lang::$info['dir'],
									)
							);

					$form[] = $inpt -> text( 'firstName', 0, array( 'class' => & Lang::$info['dir'], 'size' => 30));
					$form[] = $inpt -> text( 'lastName', 0, array( 'class' => & Lang::$info['dir'], 'size' => 30));
					
					$form[] = $inpt -> text( 'coTitle', 0, array( 'class' => & Lang::$info['dir'], 'size' => 40));
					
					
					$form[] = $inpt -> text( 'nationalCode', 0, array( 'class' => 'ltr', 'size' => 40));
					$form[] = $inpt -> text( 'tel', 0, array( 'class' => 'ltr', 'size' => 40));
					$form[] = $inpt -> text( 'mobile', 0, array( 'class' => 'ltr', 'size' => 40));
					$form[] = $inpt -> text( 'email', 0, array( 'class' => 'ltr', 'size' => 40));
					$form[] = $inpt -> text( 'website', 0, array( 'class' => 'ltr', 'size' => 40));
					
					$form[] = $inpt -> text( 'address', 0, array( 'class' => & Lang::$info['dir'], 'size' => 60));
					$form[] = $inpt -> text( 'comments', 0, array( 'class' => & Lang::$info['dir'], 'size' => 60));

				$form[] = $inpt -> fldSetClos();

				//$form[] = $tab -> clos();
			}
			//$form[] = '</td></tr>';

		//End of Prepare the Languages Tabs-->
		
		$form[] = $inpt -> captcha( 'captcha', 0);
		
	
	/*------------------------------------------------------*/
	
	for( $i = 0; $i != sizeof( $form); $i++)
	{
		$tpl -> assign_block_vars( 'myblck',  array(
				'INPUT' => & $form[ $i]
			)
		);
	}

	$tpl -> display( 'header');
	$tpl -> display( 'body');
?>
