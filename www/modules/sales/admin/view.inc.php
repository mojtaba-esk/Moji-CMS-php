<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	lib( array(
			'Tab',
			'File',
			'Img',
			'Addable',
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
		$file = new File( Module::$name);
		Module::$opt['imageFile']	and	Img::setPrfx( Module::$name);
		Module::$opt['attchmnt']	and	$adbl = new Addable( Lang::$info, array( 'id' => 'hidden', 'attchmnt' => 'fileUpld', 'sp' => 'html'));
	}
	
	//<!-- Update the features...

		if( isset( $_POST['fSubmit']))
		{
			while( $cols = $inpt -> getRow())
			{
				if( empty( $cols['id'])) break; // There is no records need to update, so go to new one...
			
				//<!-- find the FeatureIds list...
				
					$SQL = 'SELECT
								`f`.`id`
							FROM
								`products_features`		AS	`f`
							WHERE
								`f`.`verId`	= 0'. $cols['verId'] .'
								AND
									`f`.`activationCode`	IN	( 0'. $cols['featureCodes'] .')';
					
					$fRws = DB::load( $SQL, 'products_features', true);
					$featureIds = implode( ',', $fRws);
				
				//-->
				
				//<!-- Save some informations about new features...
	
					DB::update( array(
							'tableName' => Module::$name . '_features',
							'cols' 	=> array( 'featureIds' => & $featureIds),
							'where'	=> array(
								'id' => $cols['id'],
							),
						)
					);										

				//-->
			
			}// End of while( $cols = $inpt -> getRow());
			
			if( !empty( $cols['newFeatureCodes']))
			{
			
				//<!-- find the FeatureIds list...
				
					$SQL = 'SELECT
								`f`.`id`
							FROM
								`products_features`		AS	`f`
							WHERE
								`f`.`verId`	= 0'. $cols['newVerId'] .'
								AND
									`f`.`activationCode`	IN	( 0'. $cols['newFeatureCodes'] .')';
					
					$fRws = DB::load( $SQL, 'products_features', true);
					$featureIds = implode( ',', $fRws);
				
				//-->
				
				if( !empty( $featureIds))
				{
			
					//<!-- Add new information...
			
						$iCols = array();

						$iCols['cuId'] 			= intval( $_GET['id']);
						$iCols['paymentId'] 	= 0;
						$iCols['buyTime'] 		= time();
						$iCols['ip'] 			= & $_SERVER['REMOTE_ADDR'];
						$iCols['verId']			= & $cols['newVerId'];
						$iCols['featureIds']	= & $featureIds;
						$iCols['userId']		= & Session::$userId;	// Register By...

						DB::insert( array(
								'tableName' => Module::$name . '_features',
								'cols' 	=> & $iCols,
							)
						);

					//-->

				}// End of if( !empty( $featureIds));

			}// End of if( !empty( $cols['newFeatureCodes']));
			
			msgDie( Lang::getVal( 'updated'), './'. URL::get(), 1);
			return;

		}// End of if( isset( $_POST['fSubmit']));

	//-->

	//<!-- Activation...
	
		if( isset( $_POST['submit']))
		{
			$cols = $inpt -> getRow();

			$iCols = array();

			$iCols['cuId'] 			= intval( $_GET['id']);
			$iCols['activatorId'] 	= & Session::$userId;
			$iCols['actvTime'] 		= time();
			$iCols['ip'] 			= & $_SERVER['REMOTE_ADDR'];
			$iCols['anchorPoint']	= intval( $cols['anchorPoint']); // For the first time it must be set to 1
			$iCols['desc']			= $inpt -> dbClr( $cols['actvDesc']);

			DB::insert( array(
					'tableName' => Module::$name . '_activation',
					'cols' 	=> & $iCols,
				)
			);

			//<!-- Load the Lock Serial and generate the activation key...
			
				//<!-- Permission Check...

					$whereArr = array( 'rltdId' => intval( $_GET['id']));
					if( !empty( Module::$opt['permission'][ 'ownDataOnly' ]))
					{
						$whereArr[ 'userId'] = Session::$userId;
					}

				//-->			

				$rws = DB::load(
					array( 
						'tableName' => Module::$name . '_main',
						'cols' => array( 'lockSerial'),
						'where' => & $whereArr,
					)
				);
				
				require( dirname( __FILE__) .'/../kgn/key.'. $_SESSION['pId'] .'.inc.php');
				$actvKey = keygen( $rws[0]['lockSerial']);

			//-->
			
			$msg = Lang::getVal( 'actvKey');
			$msg .= ' <input type="text" name="key" size="40" class="ltr" readonly="readonly" value="'. $actvKey .'" /><br />';
			
			msgDie( $msg, './?md='. Module::$name, 1000);
			return;

		}//End of if( $_GET['mod'] == 'edt' && isset( $_POST));
	
	//End of Update rows -->

	$tpl -> set_filenames( array(
		'edit' => Module::$name .'.admin.view',
		)
	);
	
	//<!-- Permission Check...

		$whereArr = array( 'rltdId' => intval( $_GET['id']));
		if( !empty( Module::$opt['permission'][ 'ownDataOnly' ]))
		{
			$whereArr[ 'userId'] = Session::$userId;
		}

	//-->

	$rws = DB::load(
		array( 
			'tableName' => Module::$name . '_main',
			'where' => & $whereArr,
		)
	);

	//$inpt -> setVals( $rws);//Call for Each Data Instance;
	$mRw = & $rws[0];

	// $msg = 'Gholi kochooolooo';
	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT'	=> Lang::getVal( 'activate'),
		'F_SUBMIT'	=> Lang::getVal( 'featuresUpdate'),
		'CANCEL'	=> Lang::getVal( 'return'),
		
		'RETURN_URL' => '?md='. Module::$name,

		)
	);
	
	//<!-- Prepare Form Elements...

		if( Module::$opt['imageFile'])
		{
			$imgSrc = $file -> getPth( intval( @$_REQUEST['id']));
			
			//set_time_limit(0);
			
	/* 		$imgSrc and $imgSrc = '../'. 
				Img::get( $imgSrc, 
					array( 
						'h' => 400, //Height
						'w' => 200, //Width
						'wtrMrk' => array( //WaterMark
							'img' => $_cfg[ 'path'] .'etc/watermark.png'
							)
						)
					);
	 */		
			//$imgStrtTime = microtime();
			
			$imgSrc and $imgSrc = '../'. Img::get( $imgSrc, array( 'h' => 120 ));
			
			//$imgTimeLnt = microtime() - $imgStrtTime;
			//printr( 'imgTimeLnt: '. $imgTimeLnt);
			
			$form[] = $inpt -> imgUpld( 'image', 0, $imgSrc);
		
		}//End of if( Module::$opt['imageFile']);

		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'saleInfo'));
		
			$form[] = $inpt -> html( 'saleTime', Lang::numFrm( Date::get( 'D d M Y', $mRw[ 'saleTime'])));
			
			Module::$opt['categoryMod'] and $form[] = $inpt -> dropDown( 'catId', 0, array( 
																			'items'	=> getCats( Module::$name, Lang::viewId()),
																			'class'	=> & Lang::$info['dir'],
																	)
															);

			$salersLst = getCats( Module::$name, Lang::viewId(), 'salers');
			$form[] = $inpt -> html( 'salerId', $salersLst[ $mRw[ 'salerId'] ]);
			unset( $salersLst);
			
			//<!-- Version...

				$verRws = DB::load(
					array( 
						'tableName' => 'products_versions',
						'where' => array(
							'id' => $mRw[ 'verId'],
						),
					)
				);

				$form[] = $inpt -> html( 'version', empty( $verRws) ? '---' : $verRws[0]['title']);
				unset( $verRws);

			//-->
			
			$form[] = $inpt -> html( NULL);
			
			//<!-- User info...

				$usrRws = DB::load(
					array( 
						'tableName' => 'admin_users_main',
						'where' => array(
							'id' => $mRw[ 'userId'],
						),
					)
				);
				
				$uLnk = '<a href="?md=users&mod=view&id='. $mRw['userId'] .'">'. $usrRws[0]['firstName'] .' '. $usrRws[0]['lastName'] .'</a>';
				$form[] = $inpt -> html( 'registerBy', $mRw['userId'] ? $uLnk : Lang::getVal( 'user'));

				unset( $usrRws);

			//-->
			
			$form[] = $inpt -> html( 'insrtTime', Lang::numFrm( Date::get( 'D d M Y G:i', $mRw[ 'insrtTime'])));
			$form[] = $inpt -> html( 'updteTime', $inpt -> getVal( 'updteTime') ? Lang::numFrm( Date::get( 'D d M Y G:i', $mRw[ 'updteTime'])) : Lang::getVal( 'never'));

		$form[] = $inpt -> fldSetClos();

		//----------------------------------

		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'serialInfo'));
														
			$form[] = $inpt -> text( 'lockSerial', 0, array( 'class' => 'ltr', 'readonly' => 'readonly', 'value' => & $mRw['lockSerial'], 'size' => 40));
			$form[] = $inpt -> html( 'enableActv', $mRw['enableActv'] ? Lang::getVal( 'yes') : Lang::getVal( 'no'));
			
		$form[] = $inpt -> fldSetClos();

		//<!-- Prepare the Languages Tabs ...
		
			//$tab = new Tab( $lngsTitle, Lang::viewId());
			//$form[] = '<tr><td colspan="2">'. $tab -> bar();

			//foreach( $lngs as $lng)
			{
				//$form[] = $tab -> opn( $lng);
				
				$form[] = $inpt -> fldSetOpn( Lang::getVal( 'customerInfo'));
				
					$form[] = $inpt -> html( 'gender', $mRw['gender'] == 1 ? Lang::getVal( 'male') : Lang::getVal( 'female'));

					$form[] = $inpt -> html( 'firstName', $mRw['firstName']);
					$form[] = $inpt -> html( 'lastName', $mRw['lastName']);
					
					$form[] = $inpt -> html( 'nationalCode', empty( $mRw['nationalCode'])	? '---' : $mRw['nationalCode']);

					$form[] = $inpt -> html();
					
					$form[] = $inpt -> html( 'tel', 	empty( $mRw['tel'])		? '---' : $mRw['tel']);
					$form[] = $inpt -> html( 'mobile',	empty( $mRw['mobile'])	? '---' : $mRw['mobile']);
					$form[] = $inpt -> html( 'email',	empty( $mRw['email'])	? '---' : $mRw['email']);
					$form[] = $inpt -> html( 'website',	empty( $mRw['website'])	? '---' : $mRw['website']);
					$form[] = $inpt -> html( 'address',	empty( $mRw['address'])	? '---' : nl2br( $mRw['address']));
					
					$form[] = $inpt -> html();

					$form[] = $inpt -> html( 'coTitle', $mRw['coTitle']);
					$form[] = $inpt -> html( 'comments', empty( $mRw['comments']) ? '---' : nl2br( $mRw['comments']));
					
					$form[] = $inpt -> html();
					
					$form[] = $inpt -> html( 'viewCuLogs', '<a href="?md='. Module::$name .'&mod=logs&cuId='. $mRw['rltdId'] .'">'. Lang::getVal( 'viewCuLogs') .'</a>');

				$form[] = $inpt -> fldSetClos();

				//$form[] = $tab -> clos();
			}
			//$form[] = '</td></tr>';

		//End of Prepare the Languages Tabs-->
		
		
		$infoTab = new Tab( $tmp = array( Lang::getVal( 'actvInfo'), Lang::getVal( 'featureInfo'), Lang::getVal( 'updateLog')));
		$form[] = '<tr><td colspan="2">'. $infoTab -> bar();
		
		$form[] = $infoTab -> opn( $lng = array( 
													'id'	=> 0,
													'align'	=> & Lang::$info['align'],
													'dir'	=> & Lang::$info['dir']
												)
										);

			//<!-- Activation Information...
		
				$SQL = 'SELECT
							`a`.*,
							`u`.`id`		AS	`activatorId`,
							`p`.`payment`	AS	`paiedAmount`,
							`p`.`ref_num`	AS	`refNum`,
							CONCAT( `u`.`firstName`, \' \',  `u`.`lastName`) AS `activatorName`
						FROM
							`'. Module::$name .'_activation`	AS	`a`
							LEFT JOIN
								`admin_users_main`				AS	`u`
							ON
								`a`.`activatorId`	=	`u`.`id`

							LEFT JOIN 
								`sbpayment`						AS	`p`
							ON
								`a`.`paymentId`		=	`p`.`id`

						WHERE
							`a`.`cuId` = '. intval( $_GET['id']) .'
						ORDER BY
							`a`.`id` DESC';
				$rws = DB::load( $SQL);
				
				if( empty( $rws)) $form[] = $inpt -> hidden( 'anchorPoint', 0, 1);
			
				//$form[] = $inpt -> fldSetOpn( Lang::getVal( 'actvInfo'));
			
					is_array( $rws) or $rws = array();
					foreach( $rws as $key => $rw)
					{
						if( $key) $form[] = $inpt -> html( NULL, '<hr />');
					
						$form[] = $inpt -> html( 'actvTime', Lang::numFrm( Date::get( 'D d M Y G:i', $rw['actvTime'])));
						$form[] = $inpt -> html( 'ip', $rw['ip']);
					
						$uLnk = '<a href="?md=users&mod=view&id='. $rw['activatorId'] .'">'. $rw['activatorName'] .'</a>';
						$form[] = $inpt -> html( 'activatorName', $rw['activatorId'] ? $uLnk : Lang::getVal( 'user'));
					
						$rw['activatorId'] or $form[] = $inpt -> html( 'olPaiedAmount', Lang::numFrm( number_format( $rw['paiedAmount'])) .' '. Lang::getVal( 'rials'));
						$rw['activatorId'] or $form[] = $inpt -> html( 'olRefNum', $rw['refNum']);
						
						empty( $rw['desc']) or $form[] = $inpt -> html( 'actvDesc', $rw['desc']);

					}//End of foreach( $rws as $rw);

				//$form[] = $inpt -> fldSetClos();
		
			//End of Activation Information.-->
		
		$form[] = $infoTab -> clos();
		
		//----------
		
			$form[] = $infoTab -> opn( $lng = array( 
												'id'	=> 1,
												'align'	=> & Lang::$info['align'],
												'dir'	=> & Lang::$info['dir']
											)
									);

			//<!-- Features Buying Information...
		
				$SQL = 'SELECT
							`f`.*,
							`v`.`title`		AS	`version`,
							`v`.`id`		AS	`verId`,
							`p`.`payment`	AS	`paiedAmount`,
							`p`.`ref_num`	AS	`refNum`
						FROM
							`'. Module::$name .'_features`		AS	`f`
							LEFT JOIN 
								`sbpayment`						AS	`p`
							ON
								`f`.`paymentId`		=	`p`.`id`

							, `products_versions`				AS	`v`

						WHERE
							`f`.`cuId` = '. intval( $_GET['id']) .'
							AND
								`f`.`verId` = `v`.`id`
						ORDER BY
							`f`.`id` DESC';
				$rws = DB::load( $SQL);
				
				//printr( $rws);
			
				//$form[] = $inpt -> fldSetOpn( Lang::getVal( 'actvInfo'));
				
					is_array( $rws) or $rws = array();
					foreach( $rws as $key => $rw)
					{
						if( $key) $form[] = $inpt -> html( NULL, '<hr />');
					
						$form[] = $inpt -> html( 'buyTime', Lang::numFrm( Date::get( 'D d M Y G:i', $rw['buyTime'])));
						$form[] = $inpt -> html( 'ip', $rw['ip']);
					
						empty( $rw['paymentId']) or $form[] = $inpt -> html( 'olPaiedAmount', Lang::numFrm( number_format( $rw['paiedAmount'])) .' '. Lang::getVal( 'rials'));
						empty( $rw['paymentId']) or $form[] = $inpt -> html( 'olRefNum', $rw['refNum']);
						
						//printr( $rw);
						
						//<!-- User info...

							$usrRws = DB::load(
								array( 
									'tableName' => 'admin_users_main',
									'where' => array(
										'id' => $rw[ 'userId'],
									),
								)
							);
				
							$uLnk = '<a href="?md=users&mod=view&id='. $rw['userId'] .'">'. $usrRws[0]['firstName'] .' '. $usrRws[0]['lastName'] .'</a>';
							
							$form[] = $inpt -> html( 'registerBy', $rw['userId'] ? $uLnk : ( $rw['paymentId'] ? Lang::getVal( 'user') : Lang::getVal( 'system')));
							
							empty( $rw['trackingNum']) or $form[] = $inpt -> html( 'trackingNum', '<b>'. $rw[ 'trackingNum'] .'</b>');

							unset( $usrRws);

						//-->
						
						$form[] = $inpt -> fldSetOpn( Lang::getVal( 'features'));
						
						$form[] = $inpt -> html( 'version', $rw['version']);
						
						//<!-- List of bought features...
						
							//<!-- List of featureGroups...
								
								$ftGrpRws = DB::load(
									array( 
										'tableName' => 'products_features_groups',
										'where' => array(
											'verId' => $rw[ 'verId'],
										),
									)
								);

								foreach( $ftGrpRws as $rowId => $tmpRw)
								{
									$ftGrpRws[ $rowId ][ 'featureIds'] = explode( ',', $ftGrpRws[ $rowId ][ 'featureIds']);
								}
							
							//-->

							$SQL = 'SELECT
										`f`.`id`,
										`f`.`title`,
										`f`.`price`,
										`f`.`activationCode`	AS	`code`
									FROM
										`products_features`		AS	`f`
									WHERE
										`f`.`id`	IN	( 0'. $rw['featureIds'] .')';
							
							$fRws = DB::load( $SQL);

							$listOfFeatures = '<ul>';
							$featureCodes = '';
							foreach( $fRws as $irw)
							{
								$listOfFeatures .= '<li>';
								foreach( $ftGrpRws as $rowId => $tmpRw)
								{
									if( in_array( $irw['id'], $tmpRw[ 'featureIds']))
									{
										$listOfFeatures .= '<b>'. $tmpRw['title'] .' ('. Lang::numFrm( number_format( $tmpRw['price'])) .') </b> --&gt; &nbsp;';
									}

								}//End of foreach( $ftGrpRws as $rowId => $tmpRw);

								$listOfFeatures .= $irw['title'] .( empty( $irw['price']) ? '' : '('. Lang::numFrm( number_format( $irw['price'])) .')');
								$listOfFeatures .= '</li>';
								$featureCodes .= $irw['code'] .',';

							}// End of foreach( $itmRws as $irw);
							$featureCodes = substr( $featureCodes, 0, -1);
							$listOfFeatures .= '</ul>';
							
							$form[] = $inpt -> html( 'list', $listOfFeatures);
							
							$form[] = $inpt -> text( 'featureCodes', 0, array( 'class' => 'ltr', 'size' => 40, 'value' => & $featureCodes));
							$form[] = $inpt -> hidden( 'verId', 0, $rw['verId']);
							$form[] = $inpt -> hidden( 'id', 0, $rw['id']);
							$inpt -> incNum();

						//-->
						
						$form[] = $inpt -> fldSetClos();

					}//End of foreach( $rws as $rw);
					
					$form[] = $inpt -> text( 'newFeatureCodes', 0, array( 'class' => 'ltr', 'size' => 40));
					$form[] = $inpt -> hidden( 'newVerId', 0, $mRw[ 'verId']);
					

				//$form[] = $inpt -> fldSetClos();
		
			//End of Features Buying Information.-->
		
		$form[] = $infoTab -> clos();
		
		//----------
		
			$form[] = $infoTab -> opn( $lng = array( 
												'id'	=> 2,
												'align'	=> & Lang::$info['align'],
												'dir'	=> & Lang::$info['dir']
											)
									);
		
				//<!-- Update log Information...
		
					$SQL = 'SELECT
								`l`.*,
								`v`.`title`	AS	`version`
							FROM
								`'. Module::$name .'_update_logs`	AS	`l`,
								`products_versions`					AS	`v`

							WHERE
								`l`.`cuId`	= '. intval( $_GET['id']) .'
								AND
									`l`.`verId`	= `v`.`id`
							ORDER BY
								`l`.`id` DESC';
					$rws = DB::load( $SQL);

					//$form[] = $inpt -> fldSetOpn( Lang::getVal( 'actvInfo'));

						is_array( $rws) or $rws = array();
						foreach( $rws as $key => $rw)
						{
							if( $key) $form[] = $inpt -> html( NULL, '<hr />');

							$form[] = $inpt -> html( 'version', $rw['version']);
							$form[] = $inpt -> html( 'updateTime', Lang::numFrm( Date::get( 'D d M Y G:i', $rw['updateTime'])));
							$form[] = $inpt -> html( 'ip', $rw['ip']);

						}//End of foreach( $rws as $rw);

					//$form[] = $inpt -> fldSetClos();

				//End of Activation Information.-->
		
			$form[] = $infoTab -> clos();
		
		//-------------->
		
		$form[] = $inpt -> text( 'actvDesc', 0, array( 'class' => & Lang::$info['dir'], 'size' => 80));
		

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
