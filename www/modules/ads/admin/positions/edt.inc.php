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
				if( !$cols['key']) continue;
				
				//$cols[ 'insrtTime'] = time();
				unset($cols['lngId'],$cols['id']);
//printr($cols);
				DB::insert( array(
						'tableName' => Module::$name .'_'. $_GET['sub'],
						'cols' => & $cols,
					)
					, Module::$name /* Cache Prefix*/
				);
				sLog( array(
								'itemId'	=> DB::insrtdId(),
								'desc'		=> & $cols['key'],
							)
						);
				break;
				
				/*if( !$rltdId)
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
					
					
					
					//<!-- Upload The image ...
					
						$files = $inpt -> getFiles();
						$file -> save( $rltdId, $files[ 'image'][ 'tmp_name'], 'img.'. $_GET['sub'] .'.');
					
					//End of Upload The image-->

				}//End of if( !$rltdId)/**/
				
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
				//$cols['title'] = $inpt -> dbClr( $cols['title']);
//printr($cols);
//printr($rltdId);
				//$cols[ 'updteTime'] = time();
				unset($cols['lngId']);
				unset($cols['id']);

//printr($cols);				
				$updtRws = DB::update( array(
						'tableName' => Module::$name . '_'. $_GET['sub'],
						'cols' 	=> & $cols,
						'where'	=> array(
							'id' => $rltdId,
							
						),
					)
					, Module::$name /* Cache Prefix*/
				);
				
			

				sLog( array(
							'itemId'	=> $rltdId ,
							'desc'		=> & $cols['key'],
						)
					);
				

				
			}//End of while( $cols = $inpt -> getRow());
			
//printr($cols);
//die;
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
					'id'	=> intval($_GET['id']),
				//	'rltdId'	=> intval( $_GET['id']),
				//	'domId'		=> $_cfg['domain']['id'],
				),
			)
		);
//printr($rws);
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
		
		//<!-- Key... for developer only...

			//if( defined( 'DEVELOPER_MODE'))
			{
				$form[] = $inpt -> text( 'key', 0, array( 'class' => 'ltr', 'size' => 19));
			    //$SQL='SELECT `id`,`name` FROM module'
				$mdRws = DB::load(
					array( 
						'tableName' => 'modules',
						'cols' => array( 'id', 'name'),
					)
				);		
//printr($mdRws);
				unset( $mdNewRws);
				for($i=0;$i<sizeof($mdRws);$i++)
				{
					$mdNewRws[ $mdRws[ $i]['id'] ] = Lang::getVal( $mdRws[$i]['name']);				
				}

				$form[] = $inpt -> dropDown( 'mdId', 0, array( 
															'items'	=> & $mdNewRws, 
															'dir'	=> Lang::$info['dir'],
															'align'	=> Lang::$info['align']
															
													)
											);
		
			//}else{
		
				//$form[] = $inpt -> hidden( 'key');
			}

		//-->
		
			
		/*//<!-- Prepare the Languages Tabs ...
		
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

		//End of Prepare the Languages Tabs-->/**/

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
