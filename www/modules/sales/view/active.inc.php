<?php
/*
* Author: Mojtaba Eskandari
* Started at 2011-04-02
* Active mode for the products.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	lib( array(
			'Input',
			'Session',
			'nusoap',
			'SBPayment',
		)
	);

    $sb = new SBPayment( $_cfg['Bank']['MID'], $_cfg['Bank']['pass']);
    $sb -> redirectURL = $_cfg['URL'] . 'activation';

	$tpl -> display( 'header');

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
	
	/*------------------------------------------------------*/

	if( isset( $_POST['State'])) // Returned from Bank
    {

		$sb -> receiverParams( $_POST['ResNum'], $_POST['RefNum'], $_POST['State']);
        $msg = implode( '<br />', $sb -> getMsg());

    }//End of if( isset( $_POST['State']));
	
	/*------------------------------------------------------*/
	
	if( isset( $_POST['submit']))
	{
		while( $cols = $inpt -> getRow())
		{
			//Clear the input serial...
			$cols['lockSerial'] = preg_replace( '([^0-9\-]*)', '', $cols['lockSerial']);
			
			if( !empty( $cols['actvPrice'])) //need to go to bank
			{

				if( $sb -> saveStoreInfo( $cols['actvPrice']))
				{
				    $pmId = DB::load( 'SELECT `id` FROM `sbpayment` WHERE `res_num` = \''. $sb -> resNum .'\'');

					DB::update( array(
							'tableName' => Module::$name . '_main',
							'cols' 	=> array( 'tmpPaymentId' => $pmId[0]['id']),
							'where'	=> array(
								'lockSerial' => & $cols['lockSerial'],
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

			//<!-- Load the Lock Serial and generate the activation key...
			
				$rws = DB::load(
					array( 
						'tableName' => Module::$name . '_main',
						'where' => array(
							'lockSerial'=> & $cols['lockSerial'],
							'productId'	=> $_cfg['product']['id'],
						),
					)
				);
				
				$mRw = & $rws[0];
				
				//printr( $mRw);
				
				if( empty( $rws))
				{
					msgDie( Lang::getVal( 'serialNotFound'), URL::rw( '?md='. Module::$name) , 3, 'error');
					return;
				
				}//End of if( empty( $rws));

				if( !$rws[0]['enableActv'])
				{
					msgDie( Lang::getVal( 'serialDisabled'), URL::rw( '?md='. Module::$name), 10, 'error');
					return;

				}//End of if( !$rws[0]['enableActv']);
				
				//<!-- Fetch the first activation...

					$SQL = 'SELECT *
							FROM
								`'. Module::$name .'_activation`
							WHERE
								`cuId` = '. $rws[0]['rltdId'] .'
							ORDER BY
								`id` ASC
							LIMIT 1';
					$sRw = DB::load( $SQL);

				//-->

				//1 day = 79800 seconds
				$daysAftrActv = ( time() - @$sRw[0]['actvTime']) / 79800;

				if( $pInf['firstPrice'] == 0 && 
					( empty( $sRw) || $daysAftrActv < $pInf['firstPeriod']))
				{
					$done = true;
					break;

				}//End of if( empty( $sRw) || $pInf['firstPric...;
				
				break; // Show the Activation form...

			//-->

		}//End of while( $cols = $inpt -> getRow());

	}//End of if( isset( $_POST['submit']));
	
	/*------------------------------------------------------*/
	
	//<!-- Calculate the price of activation...

		if( ( isset( $_POST['State']) || isset( $rws)) && !isset( $done)) // Need pay for activation...
		{
		
			if( !isset( $rws[0]['rltdId']))//if returned from bank
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

			}//End of if( !isset( $rws[0]['rltdId']));
			
			//<!-- Fetching the first activation...

				$SQL = 'SELECT *
							FROM
								`'. Module::$name .'_activation`
							WHERE
								`cuId` = '. $rws[0]['rltdId'] .'
							ORDER BY
								`id` ASC
							LIMIT 1';
				$sRw = DB::load( $SQL); // first activation...

			//-->
			
			$daysAftrActv = ( time() - @$sRw[0]['actvTime']) / 79800;
			
			$_SESSION['actvn']['anchorPoint'] = 0; // Should be set first.
			
			$actvPrice = 0;
			if( empty( $sRw) || $daysAftrActv < $pInf['firstPeriod'])
			{
				$actvPrice = & $pInf['firstPrice'];
			
			}else{
				
				if( $daysAftrActv < $pInf['secondPeriod'])
				{
					$actvPrice = & $pInf['secondPrice'];
				
				}else{
					//Checking recent anual recent point
				
					//<!-- Fetching the recent long activation...

						$SQL = 'SELECT *
									FROM
										`'. Module::$name .'_activation`
									WHERE
										`cuId` = '. $rws[0]['rltdId'] .'
										AND
											`anchorPoint` = 1
									ORDER BY
										`id` DESC
									LIMIT 1';
						$sRw = DB::load( $SQL); // recent long (390 days) activation...

					//-->
					
					$daysAftrActv = ( time() - $sRw[0]['actvTime']) / 79800;					
					
					if( !empty( $sRw) && $daysAftrActv < $pInf['secondPeriod'])
					{
						$actvPrice = & $pInf['secondPrice'];
					
					}else{
					
						$actvPrice = & $pInf['finalPrice'];
						$_SESSION['actvn']['anchorPoint'] = 1; // Reset the activation, for annual payment...
					
					}// End of if( $daysAftrActv < $pInf['secondPeriod']);
					
				}//End of if( $daysAftrActv < $pInf['secondPeriod']);

			}//End of if( empty( $sRw) || $daysAftrActv < $pInf['firstPeriod']);
			
			if( isset( $pRw) && $pRw['payment'] == $actvPrice || $actvPrice == 0)
			{
				$done = true; // everything is ok
			}

		}//End of if( ( isset( $_POST['State']) || isset( $rws)) && !isset( $done));

	//End of Calculate the price of activation. -->
	
	/*------------------------------------------------------*/
	
	if( isset( $done) && $done)
	{
		//customer's info must be loaded into $rws[0].
		
		require( dirname( __FILE__) .'/../kgn/key.'. $_cfg['product']['id'] .'.inc.php');
		$actvKey = keygen( $rws[0]['lockSerial']);
		
		if( empty( $actvKey))
		{
			msgDie( Lang::getVal( 'unknownErr'), NULL, 0, 'error');
			return;
		
		}
		
		//<!-- Register some informations of activation...
		
			$iCols['cuId'] 			= $rws[0]['rltdId'];
			$iCols['activatorId'] 	= 0;
			$iCols['actvTime'] 		= time();
			$iCols['ip'] 			= & $_SERVER['REMOTE_ADDR'];
			$iCols['anchorPoint']	= $_SESSION['actvn']['anchorPoint'];
			$iCols['paymentId']		= $rws[0]['tmpPaymentId'];

			$isOk = DB::insert( array(
					'tableName' => Module::$name . '_activation',
					'cols' 	=> & $iCols,
				)
			);
			
			if( $isOk) //Clear the temporary PaymentId
			{
				DB::update( array(
						'tableName' => Module::$name . '_main',
						'cols' 	=> array( 'tmpPaymentId' => 0),
						'where'	=> array(
							'rltdId' => $rws[0]['rltdId'],
						),
					)
				);

			}// End of if( $isOk);

		//-->

		$msg = Lang::getVal( 'keygenDone');	

	}//End of if( isset( $done) && $done);
	
	/*------------------------------------------------------*/
	
	//<!-- Fetch The Record By Name

		//$_GET['name'] = preg_replace("([^0-9a-zA-Z]*)", '', $_GET['name']);

		$pRws = DB::load(
			array( 
				'tableName' => 'pages_main',
				'where'	=> array( 
					'name'	=> 'activation',
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
		
		'SUBMIT' => isset( $done) ? NULL : Lang::getVal( 'doActive'),
		'PRINT'	 => Lang::getVal( 'print'),
		
		//'RETURN_URL' => '?md='. Module::$name,
		
		'ACTION_TITLE' => Lang::getVal( 'activation'),
		
		//<!-- Static Page..
		
			//'ITEM_TITLE'=>	Lang::getval( $rw['name']),
			'ITEM_BODY'		=>	& $pgRw['body'],
			'ITEM_IMG_SRC'	=>	$_cfg['URL'] . Img::get( $file -> getPth( $pgRw['rltdId']), array( 'h' => 250, 'w' => 250)),

		//-->

		)
	);
	
	/*------------------------------------------------------*/

	$getLockSerial = preg_replace( '([^0-9\-]*)', '', $_GET['lockSerial']);
	$form[] = $inpt -> text( 'lockSerial', 0, array( 'class' => 'ltr', 'size' => 40, 'autocomplete' => 'off', 'value' => & $getLockSerial));
	$form[] = $inpt -> html( NULL, Lang::getVal( 'lockSerialHint'));
	
	$form[] = $inpt -> html( 'actvnDate', Lang::numFrm( Date::get( 'D d M Y', time())));
	$form[] = $inpt -> captcha( 'captcha', 0);
	
	if( isset( $done))
	{
		unset( $form);
		
		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'customerInfo'));

			$form[] = $inpt -> html( 'firstName', ( $mRw['gender'] == 1 ? Lang::getVal( 'maleTitle') : Lang::getVal( 'femaleTitle')). ' '. $mRw['firstName'] .' '. $mRw['lastName']);
			$form[] = $inpt -> html( 'coTitle', $mRw['coTitle']);

		$form[] = $inpt -> fldSetClos();

		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'saleInfo'));
		
			$form[] = $inpt -> html( 'saleTime', Lang::numFrm( Date::get( 'D d M Y', $mRw[ 'saleTime'])));
			
		$form[] = $inpt -> fldSetClos();

		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'serialInfo'));
														
			$form[] = $inpt -> text( 'lockSerial', 0, array( 'class' => 'ltr', 'readonly' => 'readonly', 'value' => & $mRw['lockSerial'], 'size' => 50));
			$form[] = $inpt -> text( 'actvKey', 0, array( 'class' => 'ltr', 'readonly' => 'readonly', 'value' => & $actvKey, 'size' => 50));
			$form[] = $inpt -> html( 'actvnDate', Lang::numFrm( Date::get( 'D d M Y - G:i', time())));

		$form[] = $inpt -> fldSetClos();

	}//End of if( isset( $done));
	
	/*------------------------------------------------------*/
	
	
	if( ( isset( $_POST['State']) || isset( $rws)) && !isset( $done)) // Need pay for activation...
	{

		unset( $form);
		
		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'customerInfo'));

			$form[] = $inpt -> html( 'firstName', ( $mRw['gender'] == 1 ? Lang::getVal( 'maleTitle') : Lang::getVal( 'femaleTitle')). ' '. $mRw['firstName'] .' '. $mRw['lastName']);
			$form[] = $inpt -> html( 'coTitle', $mRw['coTitle']);

		$form[] = $inpt -> fldSetClos();

		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'saleInfo'));
		
			$form[] = $inpt -> html( 'saleTime', Lang::numFrm( Date::get( 'D d M Y', $mRw[ 'saleTime'])));
			
		$form[] = $inpt -> fldSetClos();

		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'serialInfo'));
														
			$form[] = $inpt -> text( 'lockSerial', 0, array( 'class' => 'ltr', 'readonly' => 'readonly', 'value' => & $mRw['lockSerial'], 'size' => 50));
			$form[] = $inpt -> html( 'actvPrice', Lang::numFrm( number_format( $actvPrice)) .' '. Lang::getVal( 'rials'));
			
		$form[] = $inpt -> fldSetClos();
		
		$form[] = $inpt -> hidden( 'actvPrice', 0, $actvPrice);
		$tpl -> assign_vars( array(

				'SUBMIT' => Lang::getVal( 'goToBank'),

			)
		);
	
	}//End of if( ( isset( $_POST['State']) || isset( $rws)) && !isset( $done));
	
	/*------------------------------------------------------*/
	
	for( $i = 0; $i != sizeof( $form); $i++)
	{
		$tpl -> assign_block_vars( 'myblck',  array(
				'INPUT' => & $form[ $i]
			)
		);
	}

	$tpl -> display( 'body');
?>
