<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	lib( array( 'Tab'));

	//<!-- Preaper Input Object ...
	
		$lngs = Lang::getAll();
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$lngsIds[] = $lngs[ $i ][ 'id' ];
			$lngsTitle[ $lngs[ $i ][ 'id' ] ] = $lngs[ $i ][ 'title'];
		}
		$inpt = new Input( $lngsIds);

	//End of Preaper Input Object -->

	if( Module::$opt[ $_GET['sub'] .'ImageFile'])
	{
		lib( array( 'File', 'Img'));

		$file = new File( Module::$name);
		Img::setPrfx( Module::$name);

	}//End of if( Module::$opt[ $_GET['sub'] .'ImageFile']);

	//<!-- Insert new rows
	
		if( $_GET['mod'] == 'new' && isset( $_POST['submit']))
		{
			$rltdId = $frstLngId = 0;
			while( $cols = $inpt -> getRow())
			{
				//IF The Required fields are empty, ignore the insertion action.
				if( !$cols['title']) continue;
				//isset( $cols[ 'pblishTime']) and $cols[ 'pblishTime'] = Date::mkTime( $cols[ 'pblishTime']);

				//<!-- Preaper Related Id

					/* For each Row ( rws) reset the Related Id*/
					$frstLngId or $frstLngId = $cols['lngId'];
					$frstLngId == $cols['lngId'] and $rltdId = 0;
					$rltdId and $cols[ 'rltdId'] = $rltdId;

				//End of Preaper Related Id-->
				
				$iCols['title']		=	$inpt -> dbClr( $cols['title']);
				$iCols['regFee']	=	intval( $cols['regFee']);
				$iCols['annualFee']	=	intval( $cols['annualFee']);
				$iCols['quota']		=	intval( $cols['quota']);
				$iCols['lngId']		=	$cols['lngId'];
				
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
				
				//$cols[ 'insrtTime'] = time();
				DB::insert( array(
						'tableName' => Module::$name .'_'. $_GET['sub'],
						'cols' => & $iCols,
					)
					, Module::$name /* Cache Prefix*/
				);
				Cache::clean( 'modules');
				
				if( !$rltdId)
				{

					//Only for first rows.
					$rltdId = DB::insrtdId();
					DB::update( array(
							'tableName' => Module::$name . '_'. $_GET['sub'],
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
						$file -> save( $rltdId, $files[ 'image'][ 'tmp_name'], 'img.'. $_GET['sub'] .'.');
					
					//End of Upload The image-->

				}//End of if( !$rltdId)
				
			}//End of while( $cols = $inpt -> getRow());
			
			msgDie( Lang::getVal( 'inserted'), './?md='. Module::$name .'&sub='. $_GET['sub'], 1);
			return;

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End of Insert new rows -->

	//<!-- Update rows
	
		if( $_GET['mod'] == 'edt' && isset( $_POST['submit']))
		{
			$rltdId = intval( $_GET['id']);

			while( $cols = $inpt -> getRow())
			{
				//isset( $cols[ 'pblishTime']) and $cols[ 'pblishTime'] = Date::mkTime( $cols[ 'pblishTime']);
				
				$uCols['title']		=	$inpt -> dbClr( $cols['title']);
				$uCols['regFee']	=	intval( $cols['regFee']);
				$uCols['annualFee']	=	intval( $cols['annualFee']);
				$uCols['quota']		=	intval( $cols['quota']);
				$uCols['lngId']		=	$cols['lngId'];
				
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

				//printr( $uCols);return;
				$cols[ 'updteTime'] = time();
				$updtRws = DB::update( array(
						'tableName' => Module::$name . '_'. $_GET['sub'],
						'cols' 	=> & $uCols,
						'where'	=> array(
							'id' => $cols[ 'id'],
							//'domId' => $_cfg['domain']['id'],
						),
					)
					, Module::$name /* Cache Prefix*/
				);
				
				Cache::clean( 'modules');
				
				//<!-- Upload The image ...

					if( $updtRws)
					{
						if( isset( $cols[ 'del_image']))
						{
							$file -> delete( $rltdId);
						}
						$files = $inpt -> getFiles();
						$files['image']['name'] and $file -> save( $rltdId, $files[ 'image'][ 'tmp_name'], 'img.'. $_GET['sub'] .'.');

					}//End of if( $updtRws);

				//End of Upload The image-->				

				sLog( array(
							'itemId'	=> $cols[ 'id'],
							'desc'		=> & $cols['title'],
						)
					);
				
				if( !$cols[ 'id'] && $cols[ 'title'])
				{
					//$cols[ 'insrtTime'] = time();
					$cols[ 'rltdId'] 	= $rltdId;
					//$cols[ 'domId']		= $_cfg['domain']['id'];
					
					unset( $cols[ 'updteTime']);
					DB::insert( array(
							'tableName' => Module::$name .'_'. $_GET['sub'],
							'cols' => & $cols,
						)
						, Module::$name /* Cache Prefix*/
					);

					sLog( array(
							'itemId'	=> DB::insrtdId(),
							'desc'		=> & $cols['title'],
							'action'	=> 'new',
						)
					);

				}//End of if( !$cols[ 'id'] && $cols[ 'title']);
				
			}//End of while( $cols = $inpt -> getRow());

			msgDie( Lang::getVal( 'updated'), './'. URL::get( array( 'mod', 'id')), 1);
			return;

		}//End of if( $_GET['mod'] == 'edt' && isset( $_POST));
	
	//End of Update rows -->

	$tpl -> set_filenames( array(
		'edit' => $_GET['sub'] .'.sub.admin.edit',
		)
	);

	if( $_GET['mod'] == 'edt')
	{
		$rws = DB::load(
			array( 
				'tableName' => Module::$name .'_'. $_GET['sub'],
				'where' => array(
					'rltdId'	=> intval( $_GET['id']),
					//'domId'		=> $_cfg['domain']['id'],
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
		
		'SUB_NAME' => Lang::getVal( $_GET['sub']),
		
		'RETURN_URL'	=> '?md='. Module::$name .'&sub='. $_GET['sub'],
		'MODULE_URL'	=> '?md='. Module::$name,
		)
	);
	
	//<!-- Prepare Form Elements...

		if( Module::$opt[ $_GET['sub'] .'ImageFile'])
		{
			$imgSrc = $file -> getPth( intval( @$_REQUEST['id']), 0, 'img.'. $_GET['sub'] .'.');
			
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
			$imgStrtTime = microtime();
			
			$imgSrc and $imgSrc = '../'. Img::get( $imgSrc, array( 'h' => 120 ));
			
			$imgTimeLnt = microtime() - $imgStrtTime;
			//printr( 'imgTimeLnt: '. $imgTimeLnt);
			
			$form[] = $inpt -> imgUpld( 'image', 0, $imgSrc);
		
		}//End of if( Module::$opt['imageFile']);

		$form[] = $inpt -> hidden( 'id', $lngsIds /* can send an array of languages Ids*/);
		
		$form[] = $inpt -> text( 'regFee', 0, array( 'class' => 'ltr', 'size' => 20));
		$form[] = $inpt -> text( 'annualFee', 0, array( 'class' => 'ltr', 'size' => 20));
		$form[] = $inpt -> text( 'quota', 0, array( 'class' => 'ltr', 'size' => 20));
		
		//<!-- Key... for developer only...

			if( defined( 'DEVELOPER_MODE'))
			{
				//$form[] = $inpt -> text( 'key', 0, array( 'class' => 'ltr', 'size' => 20));
		
			}else{
		
				//$form[] = $inpt -> hidden( 'key');
			}

		//-->
		
		//options;
		
			
		//<!-- Prepare the Languages Tabs ...
		
			$tab = new Tab( $lngsTitle, Lang::viewId());
			$form[] = '<tr><td colspan="2">'. $tab -> bar();

			foreach( $lngs as $lng)
			{
				$form[] = $tab -> opn( $lng);
				$form[] = $inpt -> text( 'title', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'size' => 40));

				//$form[] = $inpt -> textArea( 'body', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'cols' => '60', 'rows' => '15'));
				
				$form[] = $tab -> clos();
			}
			$form[] = '</td></tr>';

		//End of Prepare the Languages Tabs-->

		//<!-- Website Options....
		
			$form[] = $inpt -> fldSetOpn( Lang::getVal( 'customOpts'));
			
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
