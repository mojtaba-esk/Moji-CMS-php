<?php
/*
* Author: Mojtaba Eskandari
* Started at 2011-04-02
* Active mode for the products.
*/
	//printr( base64_encode( '123-123/2.00//2,4,8,6,7'));
	//printr( base64_decode( 'MjAxMC05MDExLTI0MTUvMi4wMg=='));
	
	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	lib( array(
			'Input',
			'Session',
			'nusoap',
			'SBPayment',
		)
	);
	
	require( 'upgradeReq.inc.php');
	
	//<!-- Enc & Dec functions...
	
		function encrypt( $str, $key)
		{
		  /*
			  $result = '';
			  for( $i = 0; $i < strlen( $str); $i++)
			  {
				 $char		= substr($str, $i, 1);
				 $keychar	= substr( $key, ( $i % strlen( $key)) - 1, 1);
				 $char		= chr( ord( $char) + ord( $keychar));
				 $result	.= $char;

			  }// End of for( $i = 0; $i < strlen( $str); $i++);

			  return base64_encode( $result); 
		  /**/
		  
		  return ChangeMainStringToCodeString( GhasemEncrypt( $inputvalue));
		  
		}// End of function encrypt( $str, $key);


		function decrypt( $str, $key)
		{
		
			return GhasemDecrypt( ChangeCodeStringToMainString( $str));
			
			/*
			  $str = base64_decode( $str);
			  $result = '';
			  for( $i = 0; $i < strlen( $str); $i++)
			  {
				$char		= substr( $str, $i, 1);
				$keychar	= substr( $key, ( $i % strlen( $key)) - 1, 1);
				$char		= chr( ord( $char) - ord( $keychar));
				$result		.= $char;

			  }// End of for( $i = 0; $i < strlen( $str); $i++);
			  
				return $result;
			/**/

		}// End of function decrypt( $str, $key);
	
	
	//-->

    $sb = new SBPayment( $_cfg['Bank']['MID'], $_cfg['Bank']['pass']);
    $sb -> redirectURL = $_cfg['URL'] . 'upgrade/'. $_GET['info'] .'/';

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
		//printr( $infoArr);
		
		//Clear the input serial...
		$lockSerial = preg_replace( '([^0-9\-]*)', '', $infoArr[0]);
		$version = $infoArr[1];
		if( isset( $infoArr[2])) $trackingNum = preg_replace( '([^0-9\-]*)', '', $infoArr[2]);
		if( isset( $infoArr[3])) $listOfSoldFeatureCodes = explode( ',', $infoArr[3]);
		
		//<!-- Fetch the version ID...

			$verRw = DB::load(
				array( 
					'tableName' => 'products_versions',
					'where' => array(
						'title' => & $version,
						'itemId'=> $_cfg['product']['id'],
					),
				)
			);

			$verId = $verRw[0]['id'];
			unset( $verRw);
			
		//-->

	//-->
	
	//<!----------
	
		if( !empty( $_GET['upgradeSaveFeatures'])) //Store sold features into database by software invoke
		{
			if( empty( $listOfSoldFeatureCodes)) die( '0');
			if( empty( $verId)) die( '0');
			
			//<!-- Customer Info...
			
				$rws = DB::load(
					array( 
						'tableName' => Module::$name . '_main',
						'where' => array(
							'lockSerial' => & $lockSerial,
						),
					)
				);

				$cuRw = & $rws[0];
				
				if( empty( $cuRw)) die( '0');

			//-->
			
			//<!-- Find the list of features' Ids...
			
				$SQL = 'SELECT
						`f`.`id`
					FROM
						`products_features`		AS	`f`
					WHERE
						`f`.`verId` = 0'. $verId .'
						AND
							`f`.`activationCode` IN ( 0'. implode( ',', $listOfSoldFeatureCodes) .')';

				$listOfSoldFeatureIds = DB::load( $SQL, 0 , 1);

			//-->
			
			//<!-- Register some informations about new features...
	
				$iCols = array();

				$iCols['cuId'] 			= $cuRw['rltdId'];
				$iCols['paymentId'] 	= 0;
				$iCols['buyTime'] 		= time();
				$iCols['ip'] 			= & $_SERVER['REMOTE_ADDR'];
				$iCols['verId']			= $verId;
				$iCols['featureIds']	= $featureIds = implode( ',', $listOfSoldFeatureIds);
				$iCols['trackingNum']	= '';//abs( crc32( sha1( 0 .'-'. microtime()))) .'-'. abs( crc32( sha1( microtime() .'.'. $cuRw['rltdId'] .'-'. $featureIds)));

				DB::insert( array(
						'tableName' => Module::$name . '_features',
						'cols' 	=> & $iCols,
					)
				);

			//-->
			
			print( '1');
			exit();
	
		}// End of if( !empty( $_GET['upgradeSaveFeatures']));
	
	//-->
	
	//<!-- Get the information from the Software...
	
		if( !empty( $_GET['upgradeAciveCode'])) //Prepare and download the final upgrade batch file
		{
			if( empty( $trackingNum)) die( '0');
			if( empty( $verId)) die( '0');
			
			//<!-- Fetch the features activation codes...
			
				$SQL = 'SELECT `featureIds` FROM `sales_features` WHERE `trackingNum` = \''. $trackingNum .'\'';
				$fIds = DB::load( $SQL, NULL, true);
				
				$SQL = 'SELECT
						`activationCode`	AS	`code`
					FROM
						`products_features`
					WHERE
							`verId` = 0'. $verId .'
							AND
								`id` IN	( 0'. @implode( ',', $fIds) .' )';
								
				$fRws = DB::load( $SQL);
				
				if( empty( $fRws)) die( '0');
				
				$activationCode = '';
				
				is_array( $fRws) or $fRws = array();
				foreach( $fRws as $frw)
				{
					//$activationCode |= $frw['code'];
					$activationCode .= $frw['code'] .',';
					
				}//End of foreach( $fRws as $frw);
				$activationCode = substr( $activationCode, 0, -1);
			
			//-->
			
			//<!-- Encrypt the activation code...
			
				$key = md5( strrev( $trackingNum .'.'. $pInf['key'] .'.'. $lockSerial));
				$encryptedCode = encrypt( $activationCode, $key);
				
				print( $encryptedCode);
				
				//printr( $encryptedCode);
				//printr( decrypt( $encryptedCode, $key));
				exit();
			
				//printr( $activationCode);
				//$key = md5( strrev( $trackingNum .'.'. $pInf['key'] .'.'. $lockSerial));

				//$b = mcrypt_create_iv( mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB), MCRYPT_RAND);
				//$encryptedCode = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( sha1( strrev( md5( $key)))), $activationCode, MCRYPT_MODE_CFB, $b));
				//$b = base64_encode( $b);

				//printr( 'iv: '. base64_encode( $b));
				//printr( 'ec: '. $encryptedCode);

				//$x = mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( sha1( strrev( md5( $key)))),base64_decode( $encryptedCode), MCRYPT_MODE_CFB,$b);

			//-->
		
		}// End of if( !empty( $_GET['upgradeAciveCode']));	
	
	/*------------------------------------------------------*/
	
	printr( $_POST);

	if( isset( $_POST['State'])) // Returned from Bank
    {

		$sb -> receiverParams( $_POST['ResNum'], $_POST['RefNum'], $_POST['State']);
        $msg = implode( '<br />', $sb -> getMsg());
        
    }//End of if( isset( $_POST['State']));
	
	/*------------------------------------------------------*/
	
	if( isset( $_POST['cancel']) || empty( $_POST))
	{
		unset( $_SESSION['upgrade']);

	}//End of if( isset( $_POST['cancel']));
	
	if( isset( $_POST['submit']))
	{
		while( $cols = $inpt -> getRow())
		{
			
			if( !empty( $_SESSION['upgrade']['totalCost'])) //need to go to bank
			{

				if( $sb -> saveStoreInfo( $_SESSION['upgrade']['totalCost']))
				{
				    $pmId = DB::load( 'SELECT `id` FROM `sbpayment` WHERE `res_num` = \''. $sb -> resNum .'\'');

					DB::update( array(
							'tableName' => Module::$name . '_main',
							'cols' 	=> array( 'tmpPaymentId' => $pmId[0]['id']),
							'where'	=> array(
								'lockSerial' => & $lockSerial,
								'productId'	 => $_cfg['product']['id'],
							),
						)
					);

				    $sb -> sendParams();

				    return;

				}else{
				
				    $msg = implode( '<br />', $sb -> getMsg());
					break;

				}//End of if( $sb -> saveStoreInfo( $cols['actvPrice']));
			
			}//End of if( !empty( $cols['actvPrice']));
			
			//<!-- Validate Security Captcah Code

				lib( array( 'Captcha'));
				$sImg = new Captcha( Module::$opt['id']);
	
				if( strtolower( $cols['captcha']) != $sImg -> GetCode())
				{
					$msg = Lang::getVal( 'wrongCaptcha');
					break;
				}
				$sImg -> Remove();//Remove the Sec-Code from System.
	
			//-->

			//<!-- Calculating the Total Cost...
			
				$totalCost = 0;
				$listOfFeatureIds = '';
				
				isset( $cols['featureGroupIds']) or $cols['featureGroupIds'] = array();
				foreach( $cols['featureGroupIds'] as $grId => $val)
				{
					
					$gRws = DB::load(
							array( 
								'tableName' => 'products_features_groups',
								'where' => array(
									'id' => $grId,
								),
							)
						);
						
					$totalCost += $gRws[0]['price'];
					$listOfFeatureIds .= $gRws[0]['featureIds'] .',';
				
				}// End of foreach( $cols['featureGroupIds'] as $grId => $val);

				isset( $cols['featureIds']) or $cols['featureIds'] = array();
				foreach( $cols['featureIds'] as $fId => $val)
				{
					
					$fRws = DB::load(
							array( 
								'tableName' => 'products_features',
								'where' => array(
									'id' => $fId,
								),
							)
						);
						
					$totalCost += $fRws[0]['price'];
					$listOfFeatureIds .= $fId .',';
				
				}// End of foreach( $cols['featureGroupIds'] as $grId => $val);				
				
				$listOfFeatureIds = substr( $listOfFeatureIds, 0, -1);
				unset( $gRws, $fRws);
				
				$_SESSION['upgrade'] = array(
					'featureIds'	=>	$listOfFeatureIds,
					'totalCost'		=>	$totalCost,
				);

				break; // Show the Activation form...

			//-->

		}//End of while( $cols = $inpt -> getRow());

	}//End of if( isset( $_POST['submit']));
	
	/*------------------------------------------------------*/
	
	//<!-- Calculate the price of activation...
		
		if( isset( $_POST['State']) && !isset( $done)) // Returned from bank... Need pay for activation...
		{
			//<!-- Fetch the payment info...

				$SQL = "SELECT *
						FROM
							`sbpayment`
						WHERE
							`ref_num` = '{$_POST['RefNum']}'
							AND
								`res_num` = '{$_POST['ResNum']}'";
				$pRws = DB::load( $SQL);
				$pRw = & $pRws[0];

			//-->
			
			$pRw['id'] and $rws = DB::load(
				array( 
					'tableName' => Module::$name . '_main',
					'where' => array(
						'tmpPaymentId'	=>	$pRw['id'],
						'productId'		=>	$_cfg['product']['id'],
					),
				)
			);
			
			$mRw = & $rws[0]; //may be never used.

			if( isset( $pRw) && $pRw['payment'] == $_SESSION['upgrade']['totalCost'])
			{
			
				//customer's info must be loaded into $rws[0].
		
				require( dirname( __FILE__) .'/../kgn/key.'. $_cfg['product']['id'] .'.inc.php');
				$actvKey = keygen( $rws[0]['lockSerial']);
		
				if( empty( $actvKey))
				{
					msgDie( Lang::getVal( 'unknownErr'), NULL, 0, 'error');
					return;
		
				}

				//<!-- Register some informations about new features...
		
					$iCols = array();

					$iCols['cuId'] 			= $mRw['rltdId'];
					$iCols['paymentId'] 	= $pRw['id'];
					$iCols['buyTime'] 		= time();
					$iCols['ip'] 			= & $_SERVER['REMOTE_ADDR'];
					$iCols['verId']			= $verId;
					$iCols['featureIds']	= $_SESSION['upgrade']['featureIds'];
					$iCols['trackingNum']	= abs( crc32( sha1( $pRw['id'] .'-'. microtime()))) .'-'. abs( crc32( sha1( microtime() .'.'. $mRw['rltdId'] .'-'. $_SESSION['upgrade']['featureIds'])));

					DB::insert( array(
							'tableName' => Module::$name . '_features',
							'cols' 	=> & $iCols,
						)
					);

				//-->
				
				unset( $_SESSION['upgrade']['featureIds']);
				$_SESSION['upgrade']['trackingNum'] = $iCols['trackingNum'];
				
				$_SESSION['upgrade']['done'] = 1;

				$msg = '';//Lang::getVal( 'PaiedDownloadIt');
				
				
			}//End of if( isset( $pRw) && $pRw['payment'] == $_SESSION['upgrade']['totalCost']);

		}//End of if( ( isset( $_POST['State']) || isset( $rws)) && !isset( $done));

	//End of Calculate the price of activation. -->
	
	/*------------------------------------------------------*/
	
	//<!-- Fetch The Record By Name

		//$_GET['name'] = preg_replace("([^0-9a-zA-Z]*)", '', $_GET['name']);

		$pRws = DB::load(
			array( 
				'tableName' => 'pages_main',
				'where'	=> array( 
					'name'	=> 'upgrade',
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
		
		'SUBMIT' => Lang::getVal( 'calcPrice'),
		'CANCEL' => Lang::getVal( 'cancel'),
		//'PRINT'	 => Lang::getVal( 'print'),
		
		//'RETURN_URL' => '?md='. Module::$name,
		
		'ACTION_TITLE' => Lang::getVal( 'upgrade'),
		
		//<!-- Static Page..
		
			//'ITEM_TITLE'=>	Lang::getval( $rw['name']),
			'ITEM_BODY'		=>	& $pgRw['body'],
			'ITEM_IMG_SRC'	=>	$_cfg['URL'] . Img::get( $file -> getPth( $pgRw['rltdId']), array( 'h' => 250, 'w' => 250)),

		//-->

		)
	);
	
	/*------------------------------------------------------*/

	//<!-- First visit this form...

		// The parameters are gathered at the top of this page
		
		$rws = DB::load(
			array( 
				'tableName' => Module::$name . '_main',
				'where' => array(
					'lockSerial' => & $lockSerial,
				),
			)
		);

		$mRw = & $rws[0];
		unset( $form);
		
		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'customerInfo'));

			$form[] = $inpt -> html( 'firstName', ( $mRw['gender'] == 1 ? Lang::getVal( 'maleTitle') : Lang::getVal( 'femaleTitle')). ' <b>'. $mRw['firstName'] .' '. $mRw['lastName']) .'</b>';
			$form[] = $inpt -> html( 'coTitle', '<b>'. $mRw['coTitle'] .'</b>');

		$form[] = $inpt -> fldSetClos();

		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'saleInfo'));
		
			$form[] = $inpt -> html( 'version', '<b>'. $version .'</b>');
			$form[] = $inpt -> html( 'lockSerial', '<b>'. $lockSerial .'</b>');
			$form[] = $inpt -> html( 'saleTime', '<b>'. Lang::numFrm( Date::get( 'D d M Y', $mRw[ 'saleTime'])) .'</b>');
			
		$form[] = $inpt -> fldSetClos();

		//<!-- Preparing the features list...

			//<!-- List of featureGroups...
				
				$ftGrpRws = DB::load(
					array( 
						'tableName' => 'products_features_groups',
						'where' => array(
							//'verId' => $rw[ 'verId'],
						),
					)
				);

				foreach( $ftGrpRws as $rowId => $tmpRw)
				{
					$ftGrpRws[ $rowId ][ 'featureIds'] = explode( ',', $ftGrpRws[ $rowId ][ 'featureIds']);
				}
			
			//-->			
		
			//<!-- Fetch the bought feature Ids...

				$fRws = DB::load(
					array( 
						'tableName' => Module::$name . '_features',
						'where' => array(
							'cuId'	=>	$mRw['rltdId'],
							//'verId' =>	$verId,
						),
					 )
				);
				
				$btFeatureIds = '';
				is_array( $fRws) or $fRws = array();
				foreach( $fRws as $rowId => $fRw)
				{
					$btFeatureIds .= $fRws[ $rowId ][ 'featureIds'] .',';
					$fRws[ $rowId ][ 'featureIds'] = explode( ',', $fRws[ $rowId ][ 'featureIds']);
				}
				$btFeatureIds .= '0';

				$form[] = $inpt -> fldSetOpn( Lang::getVal( 'boughtFeatures'));
				
					$htmlList = '<tr><td colspan="2"><ul>';
					foreach( $fRws as $fRw) //Printing...
					{
						$htmlList .= '<li>[ '. Lang::numFrm( Date::get( 'D d M Y - G:i', $fRw['buyTime'])) .' ] <ul>';

						$SQL = 'SELECT
								`f`.*
							FROM
								`products_features`		AS	`f`
							WHERE
								`f`.`id` IN ( 0'. implode( ',', $fRw[ 'featureIds']) .')
							GROUP BY 
								`f`.`id`';

						$itmRws = DB::load( $SQL );
						
						//printr( $itmRws);
						
						//<!-- Groups...
							
							$alreadyAddedGr = array();
							foreach( $itmRws as $rowId => $irw)
							{
								foreach( $ftGrpRws as $grRw)
								{
									if( in_array( $irw['id'], $grRw[ 'featureIds']))
									{
										if( !isset( $alreadyAddedGr[ $grRw['id'] ]))
										{
											$itmRws[ $rowId ] = array(
													'title' =>  '<b>'. $grRw['title'] .'</b>',
													'price' =>	$grRw['price'],
											);
											$alreadyAddedGr[ $grRw['id'] ] = true;
										
										}else{
										
											unset( $itmRws[ $rowId ]);
											
										}//End of if( !isset( $alreadyAddedGr[ $grRw['id'] ]));

									}//End of if( in_array( $irw['id'], $grRw[ 'featureIds']));

								}//End of foreach( $ftGrpRws as $grRw);
							
							}//End of foreach( $itmRws as $rowId => $irw)
						
						//-->
				
						is_array( $itmRws) or $itmRws = array();
						foreach( $itmRws as $irw)
						{
							$htmlList .= '<li><span class="bghtFtr">'. 
												$irw['title'] //.' ( verId: '. $irw['verId'] .' ) '
												. ( empty( $irw['price']) ? '' : ( ' ('. Lang::numFrm( number_format( $irw['price'])) .' '. Lang::getVal( 'rials') .') '))
											.'</span></li>';

						}// End of foreach( $itmRws as $irw);
						
						$htmlList .= '</ul></li>';
						
					}//End of foreach( $fRws as $fRw) //Printing...;
					
					$htmlList .= '</ul></td></tr>';
					$form[] = $htmlList;
				
				$form[] = $inpt -> fldSetClos();

			//-->
			
			$SQL = 'SELECT
								`f`.*
							FROM
								`products_features`		AS	`f`
							WHERE 1';
			
			$form[] = $inpt -> fldSetOpn( Lang::getVal( 'newFeatures'));
			
				$SQL .= ' AND `f`.`verId` = '. $verId;
				$itmRws = DB::load( $SQL .' AND `f`.`id` NOT IN ('. $btFeatureIds .')');

				$items = array();
				is_array( $itmRws) or $itmRws = array();
				foreach( $itmRws as $irw)
				{
					if( !$irw['price']) continue;
					$items[ $irw['id'] ] = '&nbsp;'. $irw['title'] . ( empty( $irw['price']) ? '' : ( ' ('. Lang::numFrm( number_format( $irw['price'])) .' '. Lang::getVal( 'rials') .') '));
			
				}// End of foreach( $itmRws as $irw);
			
				$form[] = $inpt -> chkBxGrp( 'featureIds', 0, array( 
																'items'	=> & $items,
																//'dir'	=> Lang::$info['dir'],
																//'align'	=> Lang::$info['align'],
																//'values' 	=> explode( ',', $inpt -> getVal( 'optionsIds')),
																'chkAll' 	=> !isset( $_SESSION['upgrade']),
																'delimiter'	=> '&nbsp;<br />',
																'disabled'	=>	isset( $_SESSION['upgrade']),
														),
														false //template...
												);														

			$form[] = $inpt -> fldSetClos();
			
			//<!-- Features Groups...
			
				$form[] = $inpt -> fldSetOpn( Lang::getVal( 'featuresGroups'));
			
					$gRws = DB::load(
						array( 
							'tableName' => 'products_features_groups',
							'where' => array(
								'verId' =>	$verId,
							),
						)
					);
					
					$items = array();
					foreach( $gRws as $gRw)
					{
					
						$items[ $gRw['id'] ] = '<b>&nbsp;'. $gRw['title'] .' ('. Lang::numFrm( number_format( $gRw['price'])) .' '. Lang::getVal( 'rials') .')</b>';

						$itmRws = DB::load( $SQL .' AND `f`.`id` IN ('. $gRw['featureIds'] .')');

						foreach( $itmRws as $irw)
						{
							$items[ $gRw['id'] ] .= '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'. $irw['title'] . ( empty( $irw['price']) ? '' : ( ' ('. Lang::numFrm( number_format( $irw['price'])) .' '. Lang::getVal( 'rials') .') '));
			
						}// End of foreach( $itmRws as $irw);
						
						$items[ $gRw['id'] ] .= '<br />&nbsp;';//<br />&nbsp;';
			
					}// End of foreach( $fRws as $fRw);
					
					$form[] = $inpt -> chkBxGrp( 'featureGroupIds', 0, array( 
																	'items'	=> & $items,
																	//'dir'	=> Lang::$info['dir'],
																	//'align'	=> Lang::$info['align'],
																	//'values' 	=> explode( ',', $inpt -> getVal( 'optionsIds')),
																	//'chkAll' 	=> 1,
																	'delimiter'	=> '&nbsp;<br />',
																	'disabled'	=>	isset( $_SESSION['upgrade']),
															),
															false //template...
													);
					

				$form[] = $inpt -> fldSetClos();				
			
			
			//-->

		//End of Preparing the features list-->
		
		isset( $_SESSION['upgrade']) or $form[] = $inpt -> captcha( 'captcha', 0);
		
	// End of the first visit.-->
	
	//<!-- Total Cost...

		if( isset( $_SESSION['upgrade']))
		{
			$form[] = $inpt -> html( 'totalCost', '<span class="price"><b><font size="3">'. Lang::numFrm( number_format( $_SESSION['upgrade']['totalCost'])) .' '. Lang::getVal( 'rials') .'</font></b></span>');
			$form[] = $inpt -> hidden( 'actvPrice', 0, $_SESSION['upgrade']['totalCost']);

			$tpl -> assign_vars( array(

					'SUBMIT' => Lang::getVal( 'goToBank'),
					'MESSAGE'=> Lang::getVal( 'clickToBuy'),

				)
			);
			
		}// End of if( isset( $_SESSION['upgrade']));

	//-->

	if( isset( $_POST['State']) && isset( $_SESSION['upgrade']['done']) && $_SESSION['upgrade']['done']) // Paied, Show the Download form...
	{
		unset( $form);
		
		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'customerInfo'));

			$form[] = $inpt -> html( 'firstName', ( $mRw['gender'] == 1 ? Lang::getVal( 'maleTitle') : Lang::getVal( 'femaleTitle')). ' <b>'. $mRw['firstName'] .' '. $mRw['lastName'] .'</b>');
			$form[] = $inpt -> html( 'coTitle', '<b>'. $mRw['coTitle'] .'</b>');

		$form[] = $inpt -> fldSetClos();

		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'saleInfo'));
		
			$form[] = $inpt -> html( 'version', '<b>'. $version .'</b>');
			$form[] = $inpt -> html( 'lockSerial', '<b>'. $lockSerial .'</b>');
			$form[] = $inpt -> html( 'saleTime', '<b>'. Lang::numFrm( Date::get( 'D d M Y', $mRw[ 'saleTime'])) .'</b>');
			
			
		$form[] = $inpt -> fldSetClos();
		
		$form[] = $inpt -> hidden( 'actvPrice', 0, $_SESSION['upgrade']['totalCost']);
		
		$form[] = $inpt -> html( 'totalPaied', Lang::numFrm( number_format( $_SESSION['upgrade']['totalCost'])) .' '. Lang::getVal( 'rials'));
		
		//$form[] = $inpt -> html( NULL, Lang::getVal( 'upgradeNotice'));
		$form[] = $inpt -> html( 'trackingNum', '<b><font size="3">'. $_SESSION['upgrade']['trackingNum'] .'</font></b>');

		$tpl -> assign_vars( array(

				'SUBMIT' => NULL, // Lang::getVal( 'download'),
				'CANCEL' => NULL, // Do not display this button
				'MESSAGE'=> Lang::getVal( 'writeTrckNum'),

			)
		);
	
	}//End of if( ( isset( $_POST['State']) ...;
	
	/*------------------------------------------------------*/
	
	$form[0] = '<tr><td><a href="#submit">'. Lang::getVal( 'goSubmit') .'</a></td></tr>'. $form[0];
	$form[] = '<tr><td><a name="submit"></a></td></tr>';
	
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
