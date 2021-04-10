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
	
	$_GET['typeId'] = intval( $_GET['typeId']);
	
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

	//<!-- Insert new rows
	
		if( $_GET['mod'] == 'new' && isset( $_POST['submit']))
		{
			$rltdId = $frstLngId = 0;
			unset( $fRltdIds);
			while( $cols = $inpt -> getRow())
			{
				//IF The Required fields are empty, ignore the insertion action.
				if( !$cols['title']) continue;
				
				$iCols['title']		= $inpt -> dbClr( $cols['title']);
				$iCols['body']		= $inpt -> dbClr( $cols['body']);
				$iCols['niceUrl']	= $inpt -> dbClr( $cols['niceUrl']);
				$iCols['price']		= intval( $cols['price']);
				$iCols['lngId']		= $cols['lngId'];
				$iCols['typeId']	= $_GET['typeId'];

				//<!-- Preaper Related Id

					/* For each Row ( rws) reset the Related Id*/
					$frstLngId or $frstLngId = $cols['lngId'];
					$frstLngId == $cols['lngId'] and $rltdId = 0;
					$rltdId and $cols[ 'rltdId'] = $rltdId;

				//End of Preaper Related Id-->

				$iCols[ 'domId'] = $_cfg['domain']['id'];

				$iCols[ 'insrtTime'] = time();
				DB::insert( array(
						'tableName' => Module::$name . '_main',
						'cols' => & $iCols,
					)
					,  Module::$name . $_cfg['domain']['id']/* Cache Prefix*/
				);
				
				$itemId = DB::insrtdId();
				
				//<!-- Process the custom fields...
				
					foreach( $cols as $fldId => $fldVal)
					{
						if( !is_numeric( $fldId)) continue; // ignore other fields
						
						unset( $fCols);
						$fCols['itemId']	= $itemId;
						$fCols['fldId']		= $fldId;
						$fCols['domId']		= $_cfg['domain']['id'];
						$fCols['lngId']		= $cols['lngId'];
						
						if( is_array( $fldVal))
						{
							if( isset( $fldVal['Y'])) // Date or DateTime object...
							{
								$fCols['numVal'] = Date::mkTime( $fldVal);

							}else{ //multiple choise (checkbox group)

								// Add a [,] in order to realise checkbox group in future
								$fCols['txtVal'] = ','. implode( ',', array_keys( $fldVal)); 

							}//End of if( isset( $fldVal['Y']));

						}else{

							$fCols['numVal'] = intval( $fCols['txtVal'] = $inpt -> dbClr( $fldVal));

						}//End of if( is_array( $fldVal));

						empty( $fRltdIds[ $fldId ]) or $fCols['rltdId']	= $fRltdIds[ $fldId ];
						DB::insert( array(
								'tableName' => Module::$name . '_fields_values',
								'cols' => & $fCols,
							)
							,  Module::$name . $_cfg['domain']['id'] .'_fields_values' /* Cache Prefix*/
						);

						if( empty( $fRltdIds[ $fldId ]))
						{
							$fRltdIds[ $fldId ] = DB::insrtdId();
							DB::update( array(
									'tableName' => Module::$name . '_fields_values',
									'cols' 	=> array( 'rltdId' => $fRltdIds[ $fldId ]),
									'where'	=> array(
										'id' => $fRltdIds[ $fldId ],
									),
								)
							);

						}//End of if( empty( $fRltdIds[ $fldId ]));

					}//End of foreach( $cols as $fldId => $fldVal);

				//-->
				
				if( !$rltdId)
				{
					//Only for first rows.
					$rltdId = $itemId;
					DB::update( array(
							'tableName' => Module::$name . '_main',
							'cols' 	=> array( 'rltdId' => $rltdId),
							'where'	=> array(
								'id' => $itemId,
							),
						)
					);

					sLog( array(
								'itemId'	=> $rltdId,
								'desc'		=> & $iCols['title'],
							)
						);

					//<!-- Upload The image ...
					
						$files = $inpt -> getFiles();
						empty( $files) or $file -> save( $rltdId, $files[ 'image'][ 'tmp_name']);
					
					//End of Upload The image-->
					
					//<!-- Upload The Attachements ...

						if( Module::$opt['attchmnt'])
						{
							require( 'attchmnt.inc.php');
						
						}//End of if( Module::$opt['attchmnt']);

					//End of Upload The Attachements-->

				}//End of if( !$rltdId)
				
				//<!-- Save the Search key words... ( Indexing)
					
					isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
					$srch -> setIndexes( $iCols['title'] .' '. $iCols['body'], $rltdId, $iCols['lngId']);

				//End of Search-->

			}//End of while( $cols = $inpt -> getRow());

			msgDie( Lang::getVal( 'inserted'), './'. URL::get( array( 'mod')));
			return;

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End of Insert new rows -->

	//<!-- Update rows

		if( $_GET['mod'] == 'edt' && isset( $_POST['submit']))
		{
			$rltdId = intval( $_GET['id']);
			
			//<!-- Upload The Attachements ...

				if( Module::$opt['attchmnt'])
				{
					require( 'attchmnt.inc.php');
				
				}//End of if( Module::$opt['attchmnt']);

			//End of Upload The Attachements-->

			while( $cols = $inpt -> getRow())
			{
				$uCols['title']		= $inpt -> dbClr( $cols['title']);
				$uCols['body']		= $inpt -> dbClr( $cols['body']);
				$uCols['niceUrl']	= $inpt -> dbClr( $cols['niceUrl']);
				$uCols['lngId']	= $cols['lngId'];
				$uCols['price']	= intval( $cols['price']);
				$uCols['typeId']= $_GET['typeId'];

				$uCols[ 'updteTime'] = time();
				$updRws = DB::update( array(
						'tableName' => Module::$name . '_main',
						'cols' 	=> & $uCols,
						'where'	=> array(
							'id'	=> $cols[ 'id'],
							'domId'	=> $_cfg['domain']['id'],
						),
					)
					, Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
				);
				
				if( $updRws)
				{
					//<!-- Upload The image ...
						
						if( isset( $cols[ 'del_image']))
						{
							$file -> delete( $rltdId);
						}
						$files = $inpt -> getFiles();
						$files['image']['name'] and $file -> save( $rltdId, $files[ 'image'][ 'tmp_name']);
						
					//End of Upload The image-->
				}//End of if( $updRws);
				
				//<!-- Process the custom fields...

					$itemId = $rltdId;

					//<!-- Removing old values...
					
						if( empty( $idDelCustm))
						{
							DB::delete( array(
									'tableName' => Module::$name .'_fields_values',
									'where'	=> array(
										'itemId'	=> $itemId,
										'domId'		=> $_cfg['domain']['id'],
									),
								)
								, Module::$name . $_cfg['domain']['id'] .'_fields_values'/* Cache Prefix*/
							);
							
							$idDelCustm = true; // Preventing from deleting in the second round.

						}//End of if( empty( $idDelCustm));
					
					//-->
					
					foreach( $cols as $fldId => $fldVal)
					{
						if( !is_numeric( $fldId)) continue; // ignore other fields
						
						unset( $fCols);
						$fCols['itemId']	= $itemId;
						$fCols['fldId']		= $fldId;
						$fCols['domId']		= $_cfg['domain']['id'];
						$fCols['lngId']		= $cols['lngId'];
						
						if( is_array( $fldVal))
						{
							if( isset( $fldVal['Y'])) // Date or DateTime object...
							{
								$fCols['numVal'] = Date::mkTime( $fldVal);

							}else{ //multiple choise (checkbox group)

								// Add a [,] in order to realise checkbox group in future
								$fCols['txtVal'] = ','. implode( ',', array_keys( $fldVal)); 

							}//End of if( isset( $fldVal['Y']));

						}else{

							$fCols['numVal'] = intval( $fCols['txtVal'] = $inpt -> dbClr( $fldVal));

						}//End of if( is_array( $fldVal));

						empty( $fRltdIds[ $fldId ]) or $fCols['rltdId']	= $fRltdIds[ $fldId ];
						DB::insert( array(
								'tableName' => Module::$name . '_fields_values',
								'cols' => & $fCols,
							)
							,  Module::$name . $_cfg['domain']['id'] .'_fields_values' /* Cache Prefix*/
						);

						if( empty( $fRltdIds[ $fldId ]))
						{
							$fRltdIds[ $fldId ] = DB::insrtdId();
							DB::update( array(
									'tableName' => Module::$name . '_fields_values',
									'cols' 	=> array( 'rltdId' => $fRltdIds[ $fldId ]),
									'where'	=> array(
										'id' => $fRltdIds[ $fldId ],
									),
								)
							);

						}//End of if( empty( $fRltdIds[ $fldId ]));

					}//End of foreach( $cols as $fldId => $fldVal);

				//-->				

				sLog( array(
							'itemId'	=> $cols[ 'id'],
							'desc'		=> & $cols['title'],
						)
					);
				
				if( !$cols[ 'id'] && $cols[ 'title'])
				{
					$cols[ 'insrtTime'] = time();
					$cols[ 'rltdId'] 	= $rltdId;
					$cols[ 'domId'] = $_cfg['domain']['id'];

					unset( $cols[ 'updteTime']);
					DB::insert( array(
							'tableName' => Module::$name . '_main',
							'cols' => & $cols,
						)
						, Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
					);

					sLog( array(
							'itemId'	=> DB::insrtdId(),
							'desc'		=> & $cols['title'],
							'action'	=> 'new',
						)
					);

				}//End of if( !$cols[ 'id'] && $cols[ 'title']);
				
				//<!-- Save the Search key words... ( Indexing)

					isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
					$srch -> setIndexes( $cols['title'] .' '. $cols['body'] .' '. @$cols['shortDesc'] .' '. @$cols['lead'], $rltdId, $cols['lngId']);

				//End of Search-->

			}//End of while( $cols = $inpt -> getRow());

			msgDie( Lang::getVal( 'updated'), './'. URL::get( array( 'mod', 'id')), 1);
			return;

		}//End of if( $_GET['mod'] == 'edt' && isset( $_POST));
	
	//End of Update rows -->

	$tpl -> set_filenames( array(
		'edit' => Module::$name .'.admin.edit',
		)
	);

	if( $_GET['mod'] == 'edt')
	{
		$rws = DB::load(
			array( 
				'tableName' => Module::$name . '_main',
				'where' => array(
					'rltdId'	=> intval( $_GET['id']),
					'domId'		=> $_cfg['domain']['id'],
				),
			)
		);
		
		//<!-- Handling custom fields...

			$fRws = DB::load(
				array( 
					'tableName' => Module::$name . '_fields_values',
					'where' => array(
						'itemId'	=> intval( $_GET['id']),
						'domId'		=> $_cfg['domain']['id'],
					),
				)
			);
		
			foreach( $fRws as $fRw)
			{
				foreach( $rws as $key => $rw)//Looking for `lngId`
				{
					if( $fRw['lngId'] != $rw['lngId']) continue;
				
					$rws[ $key ][ $fRw['fldId'] ] = empty( $fRw['txtVal']) ? $fRw['numVal'] : $fRw['txtVal'];
				
					if( $rws[ $key ][ $fRw['fldId'] ][0] == ',') //Handling checkbox groups
					{
						$rws[ $key ][ $fRw['fldId']] = array_flip( explode( ',', substr( $rws[ $key ][ $fRw['fldId'] ], 1)));

					}//End of if( $rws[ $key ][ $fRw['fldId'] ][0] == ',');
					break;

				}//End of foreach( $rws as $rw);
		
			}//End of foreach( $fRws as $fRw);
			unset( $fRws);

		//-->

		$inpt -> setVals( $rws);//Call for Each Data Instance;

	}//End of if( $_GET['mod'] == 'edt');

	// $msg = 'Gholi kochooolooo';
	
	//<!-- Fetch the Item title...
	
		$itmRws = DB::load(
			array( 
				'tableName' => Module::$name .'_types',
				'cols' => array( 'title'),
				'where' => array(
					'id' => $_GET['typeId'],
				),
			)
		);
		
	//-->	
	
	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'submit'),
		'CANCEL' => Lang::getVal( 'cancel'),
		
		'RETURN_URL' => '?md='. Module::$name .'&typeId='. $_GET['typeId'],
		'MODULE_NAME' => '<a href="?md='. Module::$name .'&sub=types">'. Lang::getVal( Module::$name) .'</a> (<a href="?md='. Module::$name .'&typeId='. $_GET['typeId'] .'">'. $itmRws[0]['title'] .'</a>)',

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

		$form[] = $inpt -> hidden( 'id', $lngsIds /* can send an array of languages Ids*/);
		//$form[] = $inpt -> hidden( 'domId', 0, $_cfg['domain']['id']);
			
		if( $_GET['mod'] == 'edt')
		{
			$form[] = $inpt -> html( 'insrtTime', Lang::numFrm( Date::get( 'D d M Y G:i', $inpt -> getVal( 'insrtTime'))));
			$form[] = $inpt -> html( 'updteTime', $inpt -> getVal( 'updteTime') ? Lang::numFrm( Date::get( 'D d M Y G:i', $inpt -> getVal( 'updteTime'))) : Lang::getVal( 'never'));
		}
		
		Module::$opt['categoryMod'] and $form[] = $inpt -> dropDown( 'catId', 0, array( 
																		'items'	=> getCats( Module::$name, Lang::viewId()), 
																		'dir'		=> Lang::$info['dir'],
																		'align'	=> Lang::$info['align']
																)
														);
														
		//$form[] = $inpt -> chkBx( 'special', 0, array( 'value' => 1));

		Module::$opt['niceUrl'] and $form[] = $inpt -> text( 'niceUrl', 0, array( 'class' => 'ltr', 'size' => 40));
		Module::$opt['hasPrice'] and $form[] = $inpt -> text( 'price', 0, array( 'class' => 'ltr', 'size' => 40));

		//<!-- Prepare the Languages Tabs ...
		
			$tab = new Tab( $lngsTitle, Lang::viewId());
			$form[] = '<tr><td colspan="2">'. $tab -> bar();

			foreach( $lngs as $lng)
			{
				$form[] = $tab -> opn( $lng);
				$form[] = $inpt -> text( 'title', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'size' => 40));

				$form[] = $inpt -> textArea( 'body', $lng[ 'id'], array( 
								'dir'	 => $lng['dir'],
								'align' => $lng['align'],
								'cols'	 => '60',
								'rows' => '15',
								'fck'	=> array(
									'lang'	=> $lng['shortName'],
									//'skin'		=> 'default' | 'office2003' | 'silver',
									//'toolBar'	=>'Default' | 'Basic',
								),
							)
					);

				$form[] = $tab -> clos();
			}
			$form[] = '</td></tr>';

		//End of Prepare the Languages Tabs-->

		//<!-- Custom fields...
		
			$form[] = $inpt -> fldSetOpn( Lang::getVal( 'fields'));

				$fldInfo = require( 'fldInfo.inc.php');

				//<!-- fetch the required fields...

					$SQL = 'SELECT * 
							FROM
								`'. Module::$name . '_fields`
							WHERE
								`lngId` = '. Lang::viewId().'
								AND
									`domId` = '. $_cfg['domain']['id'] .'
								AND
									`typeId` = '. $_GET['typeId'] .'
							ORDER BY
								`ordrId` ASC,
								`id` ASC';
					$fdRws = DB::load( $SQL, Module::$name . $_cfg['domain']['id'] .'_fields');
					
					$inpt -> lblLang = false;

					is_array( $fdRws) or $fdRws = array();
					foreach( $fdRws as $fdRw)
					{
						unset( $items);
						if( $fldInfo[ $fdRw['fldType']]['type'] == 'multi')
						{
							//<!-- Fetch the fileds params...
							
								$SQL = 'SELECT
											`rltdId`,
											`title`
										FROM
											`'. Module::$name . '_fields_params`
										WHERE
											`lngId` = '. Lang::viewId().'
											AND
												`domId` = '. $_cfg['domain']['id'] .'
											AND
												`fldId` = '. $fdRw['rltdId'] .'
										ORDER BY
											`ordrId` ASC,
											`id` ASC';
								$pRws = DB::load( $SQL, Module::$name . $_cfg['domain']['id'] .'_fields_params');
								
								$items = array();
								foreach( $pRws as $pRw)
								{
									$items[ $pRw['rltdId'] ] = $pRw['title'];
								
								}//End of foreach( $pRws as $pRw);
								unset( $pRws);

							//-->

						}// End of if( $fldInfo[ $fdRw['fldType']]['type'] == 'multi');

						if( $fdRw['fldType'] == 'multiSelect')
						{
							$form[] = $inpt -> fldSetOpn( $fdRw['title']);
								
								$form[] = $inpt -> chkBxGrp( $fdRw['rltdId'], 0, array( 
														'items'	=> & $items,
														'class'	=> & Lang::$info['dir'],
														//'align'	=> Lang::$info['align'],
														//'values' 	=> explode( ',', $inpt -> getVal( 'optionsIds')),
														'chkAll' 	=> 1,
														'delimiter'	=> '&nbsp;<br />',
														//'disabled'	=>	isset( $_SESSION['upgrade']),
												),
												false //template...
											);
								unset( $items);

							$form[] = $inpt -> fldSetClos();

						}//End of if( $fdRw['fldType'] == 'multiSelect');

						elseif( $fdRw['fldType'] == 'singleSelect'){
						
							$form[] = $inpt -> tmplt( $fdRw['title'], $inpt -> dropDown( $fdRw['rltdId'], 0, array( 
																				'items'	=> & $items,
																				'class'	=> & Lang::$info['dir'],
																		), false
																)
													);
							unset( $items);
						
						}//End of elseif( $fdRw['fldType'] == 'singleSelect');

						elseif( $fdRw['fldType'] == 'number'){
						
							$form[] = $inpt -> tmplt( $fdRw['title'], $inpt -> text( $fdRw['rltdId'], 0, array( 'class' => 'ltr', 'size' => 30), false));
						
						}//End of elseif( $fdRw['fldType'] == 'number');
						
						elseif( $fdRw['fldType'] == 'text'){
						
							//<!-- Prepare the Languages Tabs ...
		
								$tab = new Tab( $lngsTitle, Lang::viewId());
								$form[] = '<tr><td colspan="2">'. $tab -> bar();

									foreach( $lngs as $lng)
									{
										$form[] = $tab -> opn( $lng);

											$form[] = $inpt -> tmplt( $fdRw['title'], $inpt -> text( $fdRw['rltdId'], $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'size' => 50), false));

										$form[] = $tab -> clos();

									}//End of foreach( $lngs as $lng);

								$form[] = '</td></tr>';
						
						}//End of elseif( $fdRw['fldType'] == 'text');

						elseif( $fdRw['fldType'] == 'checkbox'){
						
							$form[] = $inpt -> tmplt( $fdRw['title'], $inpt -> chkBx( $fdRw['rltdId'], 0, array( 'value' => 1), false));
						
						}//End of elseif( $fdRw['fldType'] == 'checkbox');

						elseif( $fdRw['fldType'] == 'date'){

							$form[] = $inpt -> tmplt( $fdRw['title'], $inpt -> date( $fdRw['rltdId'], 0 /*Language Id*/, array( 
																			'elmnts' => 'd,M,Y',
																			//'type' => 'jalali',
																			//'value' => 'now',
																			//'defVal'	=> $_GET['mod'] == 'new' ? 'now' : NULL,
																			'difY'		=> array( -10, 1),
																			'attribs' => array( 
																				'class' => & Lang::$info['dir'],
																		)
																	), 
																	false
																)
													);
						
						}//End of elseif( $fdRw['fldType'] == 'date');

						elseif( $fdRw['fldType'] == 'dateTime'){
						
							$form[] = $inpt -> tmplt( $fdRw['title'], $inpt -> date( $fdRw['rltdId'], 0 /*Language Id*/, array( 
																			'elmnts' => 'd,M,Y,G,i',
																			//'type' => 'jalali',
																			//'value' => 'now',
																			//'defVal'	=> $_GET['mod'] == 'new' ? 'now' : NULL,
																			'difY'		=> array( -10, 1),
																			'attribs' => array( 
																				'class' => & Lang::$info['dir'],
																		)
																	), 
																	false
																)
													);
						
						}//End of elseif( $fdRw['fldType'] == 'dateTime');

					}//End of foreach( $fdRws as $fdRw);

					$inpt -> lblLang = true;

				//-->
			
			// Must come from another place, with a type Id...
			
			$form[] = $inpt -> fldSetClos();
		
		//End of custom fields-->

		//<!-- Prepare the Attachements Form . . .
		
			if( Module::$opt['attchmnt'])
			{
				//<!-- Load the informations
				
					if( $_GET['mod'] == 'edt')
					{
						$atchRws = DB::load(
							array( 
								'tableName' => Module::$name . '_attachments',
								'where' => array(
									'itemId' => intval( $_GET['id']),
									//'lngId'	=> Language Id,
								),
							), 
							Module::$name . $_cfg['domain']['id'] . '_attachments'
						);
					
					}//End of if( $_GET['mod'] == 'edt');
				
				//End of Load the informations-->
				
				$form[] = '<tr><td colspan="2"><fieldset><legend><img src="../etc/icn/attach.png" /> '. Lang::getVal( 'attachements') .' </legend><table>';

				for( $i = 0; $i != Module::$opt['attchmnt']; $i++)
				{
					$adbl -> add( array( 
							'id'			=> @$atchRws[ $i ][ 'id' ],
							//'lngId'		=> @$atchRws[ $i ][ 'lngId' ],
							'attchmnt'	=> @$atchRws[ $i ][ 'fileName' ],
							'sp'			=> '<tr><td colspan="2"><hr /></td></tr>',
						)
					);

				}//End of for( $i = 0; $i != Module::$opt['attchmnt']; $i++);

				$form[] = $adbl -> getHTML();
				$form[] = '</table></fieldset></td><tr>';

			}//End of if( Module::$opt['attchmnt']);

		//End of Prepare the Attachements Form-->

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
