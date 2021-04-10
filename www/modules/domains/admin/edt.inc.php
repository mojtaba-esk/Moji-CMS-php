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

	//<!-- Insert new rows
	
		if( $_GET['mod'] == 'new' && isset( $_POST['submit']))
		{
			$rltdId = $frstLngId = 0;
			while( $cols = $inpt -> getRow())
			{
				//IF The Required fields are empty, ignore the insertion action.
				if( empty( $cols['name']) || empty( $cols['title'])) continue;
				isset( $cols[ 'pblishTime']) and $iCols[ 'pblishTime'] = Date::mkTime( $cols[ 'pblishTime']);
				
				$iCols['title']	= $inpt -> dbClr( $cols['title']);
				$iCols['name']	= str_replace( 'www.', '', strtolower( $cols['name']));
				$iCols['lngId'] = $cols['lngId'];
				
				$iCols['tmpId']		= intval( $cols['tmpId']);
				$iCols['parkedOn']	= intval( $cols['parkedOn']);
				$iCols['statusId']	= intval( $cols['statusId']);
				$iCols['planId']	= intval( $cols['planId']);
				$iCols['ownerId']	= intval( $cols['ownerId']);

				//<!-- Preaper Related Id

					/* For each Row ( rws) reset the Related Id*/
					$frstLngId or $frstLngId = $cols['lngId'];
					$frstLngId == $cols['lngId'] and $rltdId = 0;
					$rltdId and $cols[ 'rltdId'] = $rltdId;

				//End of Preaper Related Id-->
				
				if( $cols['planId'])
				{
					$iCols['options'] = '';
					
					$pRws = DB::load(
						array( 
							'tableName' => Module::$name . '_plans',
							'cols'	=> array( 'quota'),
							'where' => array(
								'rltdId'	=> intval( $cols['planId']),
							),
						)
					);					
					$iCols['quotaLimit'] = $pRws[0]['quota'] * 1024; //Converting MB to KB

					unset( $pRws);
				
				}else{				

					//<!-- Module options...
				
						$options = array();
						foreach( $_POST['md'] as $mdId => $tmpVl)
						{
							$options['md'][ $mdId ] = array();
							$options['md'][ $mdId ][ 'options'][ 'id'] = $mdId;
							for( $i = 0; 1; $i++)
							{
								if( !isset( $cols['options_m'. $mdId .'_'. $i])) break;
								$options['md'][ $mdId ][ 'options'][ $cols['options_m'. $mdId .'_'. $i] ] = $cols['values_m'. $mdId .'_'. $i];

							}//End of for( $i = 0; 1; $i++);
					
						}//End of foreach( $_POST['md'] as $mdId => $tmpVl)

						$iCols['options'] = serialize( $options);

					//End of Module options-->

				}//End of if( $uCols['planId']);

				//$cols[ 'domId'] = $_cfg['domain']['id'];

				$cols[ 'insrtTime'] = time();
				DB::insert( array(
						'tableName' => Module::$name . '_main',
						'cols' => & $iCols,
					)
					,  Module::$name /* Cache Prefix*/
				);
				Cache::clean( 'modules');

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

					sLog( array(
								'itemId'	=> $rltdId,
								'desc'		=> & $cols['title'],
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
					$srch -> setIndexes( $cols['title'] .' '. $cols['name'] .' '. $cols['ownerName'], $rltdId, $cols['lngId']);
					
				//End of Search-->

			}//End of while( $cols = $inpt -> getRow());
			
			msgDie( Lang::getVal( 'inserted'), './?md='. Module::$name, 1);
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
				isset( $cols[ 'pblishTime']) and $uCols[ 'pblishTime'] = Date::mkTime( $cols[ 'pblishTime']);
				
				//$cols[ 'special'] = intval( @$cols[ 'special']);

				$uCols['title']	= $inpt -> dbClr( $cols['title']);
				$uCols['name']	= str_replace( 'www.', '', strtolower( $cols['name']));
				//$uCols['quota']	=	intval( $cols['quota']);
				$uCols['lngId']	=	$cols['lngId'];
				
				$uCols['tmpId']		= intval( $cols['tmpId']);
				$uCols['parkedOn']	= intval( $cols['parkedOn']);
				$uCols['statusId']	= intval( $cols['statusId']);
				$uCols['planId']	= intval( $cols['planId']);
				$uCols['ownerId']	= intval( $cols['ownerId']);
				
				$uCols['quotaLimit']= intval( $cols['quotaLimit']) * 1024;//Converting MB to KB
				
				if( $uCols['planId'])
				{
					$uCols['options'] = '';
				
				}else{
				
					//<!-- Module options...
				
						$options = array();
						foreach( $_POST['md'] as $mdId => $tmpVl)
						{
							$options['md'][ $mdId ] = array();
							$options['md'][ $mdId ][ 'options'][ 'id'] = $mdId;
							for( $i = 0; 1; $i++)
							{
								if( !isset( $cols['options_m'. $mdId .'_'. $i])) break;
								$options['md'][ $mdId ][ 'options'][ $cols['options_m'. $mdId .'_'. $i] ] = $cols['values_m'. $mdId .'_'. $i];

							}//End of for( $i = 0; 1; $i++);
					
						}//End of foreach( $_POST['md'] as $mdId => $tmpVl)

						$uCols['options'] = serialize( $options);

					//End of Module options-->

				}//End of if( $uCols['planId'])

				$uCols[ 'updteTime'] = time();
				$updRws = DB::update( array(
						'tableName' => Module::$name . '_main',
						'cols' 	=> & $uCols,
						'where'	=> array(
							'id'	=> $cols[ 'id'],
							//'domId'	=> $_cfg['domain']['id'],
						),
					)
					, Module::$name /* Cache Prefix*/
				);
				Cache::clean( 'modules');
				
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

				sLog( array(
							'itemId'	=> $cols[ 'id'],
							'desc'		=> & $uCols['title'],
						)
					);
				
				if( !$cols[ 'id'] && $cols[ 'title'])
				{
					$uCols[ 'insrtTime'] = time();
					$uCols[ 'rltdId'] 	= $rltdId;
					//$cols[ 'domId'] = $_cfg['domain']['id'];

					unset( $cols[ 'updteTime']);
					DB::insert( array(
							'tableName' => Module::$name . '_main',
							'cols' => & $uCols,
						)
						, Module::$name /* Cache Prefix*/
					);

					sLog( array(
							'itemId'	=> DB::insrtdId(),
							'desc'		=> & $uCols['title'],
							'action'	=> 'new',
						)
					);

				}//End of if( !$cols[ 'id'] && $cols[ 'title']);
				
				//<!-- Save the Search key words... ( Indexing)

					isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
					$srch -> setIndexes( $uCols['title'] .' '. $uCols['name'] .' '. $cols['ownerName'], $rltdId, $cols['lngId']);

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
					//'domId'		=> $_cfg['domain']['id'],
				),
			)
		);

		$rws[0]['quotaLimit'] /= 1024;//Converting KB to MB

		$inpt -> setVals( $rws);//Call for Each Data Instance;

	}//End of if( $_GET['mod'] == 'edt');

	// $msg = 'Gholi kochooolooo';
	
	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'submit'),
		'CANCEL' => Lang::getVal( 'cancel'),
		
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

		$form[] = $inpt -> hidden( 'id', $lngsIds /* can send an array of languages Ids*/);
		//$form[] = $inpt -> hidden( 'domId', 0, $_cfg['domain']['id']);
		
		Module::$opt['categoryMod'] and $form[] = $inpt -> dropDown( 'catId', 0, array( 
																		'items'	=> getCats( Module::$name, Lang::viewId()), 
																		'dir'	=> & Lang::$info['dir'],
																		'align'	=> & Lang::$info['align']
																)
														);
														
		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'tecInfo'));
			//$form[] = $inpt -> chkBx( 'special', 0, array( 'value' => 1));
			$form[] = $inpt -> text( 'name', 0, array( 'class' => 'ltr', 'size' => 40));
		
			//<!-- Template...
		
				//<!-- Fetch template name...
				
					$tplRws = DB::load(
								array( 
									'tableName' => 'templates_main',
									'cols'	=>	array( 'title', 'ownerId', 'isPublic'),
									'where' => array(
										'rltdId'	=> $inpt -> getVal( 'tmpId'),
										'lngId'		=> Lang::viewId(),
									),
								)
							);
					$tplRw = & $tplRws[0];
			
				//-->
			
				empty( $tplRw['title']) and $tplRw['title'] = Lang::getVal( 'nothing');

				$form[] = $inpt -> hidden( 'tmpId', 0);
				$form[] = $inpt -> html( 'template', '<span id="tmpTitleId">'. $tplRw['title'] .'</span>' .' &nbsp; [ '. $inpt -> popup( 'selectTpl', 0, array(
																				'inpt'	=>	'tmpId', // Typically, a hidden input which is used by target page.
																				'url'	=>	'?md=templates&mod=pop&prntElmId=tmpTitleId', // Target page will be opened in popup.
																				'w'		=>	700, // Popup window's width in pixel
																				'h'		=>	500, // Popup window's height in pixel
																			)
																		) . ' ]'
																	);
			
				unset( $tplRws);

			//-->
			
			//<!-- Parked domain...

				//<!-- Fetch required info...
				
					$pDmRws = DB::load(
								array( 
									'tableName' => Module::$name .'_main',
									'cols'	=>	array( 'name'),
									'where' => array(
										'rltdId'	=> $inpt -> getVal( 'parkedOn'),
									),
								)
							);
					$pDmRw = & $pDmRws[0];
			
				//-->
			
				empty( $pDmRw['name']) and $pDmRw['name'] = Lang::getVal( 'nothing');

				$form[] = $inpt -> hidden( 'parkedOn', 0);
				$form[] = $inpt -> hidden( 'rltdId', 0);
				$form[] = $inpt -> html( 'parkedOn', '<span id="parkedOnId">'. $pDmRw['name'] .'</span>' .' &nbsp; [ '. $inpt -> popup( 'selectParkedOn', 0, array(
																				'inpt'	=>	array( 'parkedOn', 'rltdId'), // Typically, a hidden input which is used by target page.
																				'url'	=>	'?md='. Module::$name .'&mod=pop&pmd='. Module::$name .'&prntElmId=parkedOnId', // Target page will be opened in popup.
																				'w'		=>	700, // Popup window's width in pixel
																				'h'		=>	500, // Popup window's height in pixel
																			)
																		) . ' ]'
																	);
			
				unset( $pDmRws);

			//-->
		
			$form[] = $inpt -> dropDown( 'statusId', 0, array( 
								'items'	=> getDomStatus( Lang::viewId()), 
								'class'	=> & Lang::$info['dir'],
						)
				);

			$form[] = $inpt -> dropDown( 'planId', 0, array(
								'items'	=> getDomPlans( Lang::viewId()),
								'class'	=> & Lang::$info['dir'],
						)
				);
				
			if( $_GET['mod'] == 'edt')
			{
				$form[] = $inpt -> text( 'quotaLimit', 0, array( 'class' => 'ltr', 'size' => 20));

			}//End of if( $_GET['mod'] == 'edt');

		$form[] = $inpt -> fldSetClos();
		
		//--------------------
		
		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'regInfo'));

			//<!-- Owner...
		
				//<!-- Fetch owner's Info
				
					$usrRws = DB::load(
						array( 
							'tableName' => 'admin_users_main',
							'where' => array(
								'id' => $inpt -> getVal( 'ownerId'),
							),
						)
					);
					$usrRw = & $usrRws[0];
			
				//-->
			
				$uLnk = empty( $usrRw) ? Lang::getVal( 'nothing') : '<a href="?md=users&mod=view&id='. $inpt -> getVal( 'ownerId') .'">'. $usrRw['firstName'] .' '. $usrRw['lastName'] .'</a>';

				$form[] = $inpt -> hidden( 'ownerId', 0);
				$form[] = $inpt -> hidden( 'ownerName', 0);// This is used for indexing and searching purposes...
				$form[] = $inpt -> html( 'ownerId', '<span id="ownerIdName">'. $uLnk .'</span>' .' &nbsp; [ '. $inpt -> popup( 'selectOwner', 0, array(
																				'inpt'	=>	array( 'ownerId', 'ownerName'), // Typically, a hidden input which is used by target page.
																				'url'	=>	'?md=users&mod=pop&prntElmId=ownerIdName', // Target page will be opened in popup.
																				'w'		=>	700, // Popup window's width in pixel
																				'h'		=>	500, // Popup window's height in pixel
																			)
																		) . ' ]'
																	);

				unset( $usrRws);

			//-->

		
			//<!-- Info for user who registered this domain...
		
				if( $_GET['mod'] == 'edt')
				{
					$usrRws = DB::load(
						array( 
							'tableName' => 'admin_users_main',
							'where' => array(
								'id' => $inpt -> getVal( 'regById'),
							),
						)
					);

					$uLnk = '<a href="?md=users&mod=view&id='. $inpt -> getVal( 'regById') .'">'. $usrRws[0]['firstName'] .' '. $usrRws[0]['lastName'] .'</a>';
					$form[] = $inpt -> html( 'registerBy', $inpt -> getVal( 'regById') ? $uLnk : Lang::getVal( 'system'));

					unset( $usrRws);

				}//End of if( $_GET['mod'] == 'edt');

			//-->

			if( $_GET['mod'] == 'edt')
			{
				$form[] = $inpt -> html( 'insrtTime', Lang::numFrm( Date::get( 'D d M Y G:i', $inpt -> getVal( 'insrtTime'))));
				$form[] = $inpt -> html( 'updteTime', $inpt -> getVal( 'updteTime') ? Lang::numFrm( Date::get( 'D d M Y G:i', $inpt -> getVal( 'updteTime'))) : Lang::getVal( 'never'));
			}			

		$form[] = $inpt -> fldSetClos();
		
		//--------------------
		
		//<!-- Prepare the Languages Tabs ...

			$tab = new Tab( $lngsTitle, Lang::viewId());
			$form[] = '<tr><td colspan="2">'. $tab -> bar();

			foreach( $lngs as $lng)
			{
				$form[] = $tab -> opn( $lng);
				$form[] = $inpt -> text( 'title', $lng[ 'id'], array( 'dir' => & $lng['dir'], 'align' => & $lng['align'], 'size' => 40));

				$form[] = $tab -> clos();

			}//End of foreach( $lngs as $lng);

			$form[] = '</td></tr>';

		//End of Prepare the Languages Tabs-->

		//<!-- Website Options....
		
			$form[] = $inpt -> fldSetOpn( Lang::getVal( 'customOpts'), true, 'closed' /*closed or opened*/);
			
				$options = unserialize( $inpt -> getVal( 'options'));
				
				//<!-- Fetch all modules...
			
					$mdRws = DB::load(
						array( 
							'tableName' => 'modules',
						)
					);

				//-->

				foreach( $mdRws as $rw)
				{
					$form[] = $inpt -> chkBx(
								$rw['name'],
								0,
								array(
									'name'	=> 'md['. $rw['id'] .']',
									'value'	=> 1,
									'checked' => isset( $options['md'][ $rw['id'] ]) ? 'checked' : NULL,
								)
							);
							
					$form[] = $inpt -> fldSetOpn( Lang::getVal( 'moduleOptions'), true, 'closed' /*closed or opened*/);

						//<!-- Module Options...

							$rw['options']	= explode( ',', @$rw['options']);
							$rw['values']	= explode( ',', @$rw['values']);

							$sizeOf = sizeof( $rw['options']);
							for( $id = 0; $id != $sizeOf; $id++)
							{
								$opt = $inpt -> hidden( 'options_m'. $rw['id'] .'_'. $id, 0, $rw['options'][$id]);

								$optValue = isset( $options['md'][ $rw['id'] ]['options'][ $rw['options'][$id] ]) ? $options['md'][ $rw['id'] ]['options'][ $rw['options'][$id] ] : $rw['values'][$id];
								$val = $inpt -> text( 'values_m'. $rw['id'] .'_'. $id, 0, array( 'value' => $optValue, 'class' => 'ltr', 'size' => 20), false);
								$form[] = $inpt -> tmplt( $rw['options'][$id], $opt .' : '. $val);

							}//End of for( $id = 0; $id <= $sizeOf; $id++);

						//End of Module Options-->
						
					$form[] = $inpt -> fldSetClos();

				}//End of foreach( $rws as $rw);			
			
			$form[] = $inpt -> fldSetClos();

		//-->

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
							)
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
