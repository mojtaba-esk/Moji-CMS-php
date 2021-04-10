<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( empty( $_REQUEST[ 'itemId']))
	{
		msgDie( 'The item is not selected!', 0,0, 'error');
		return;
	}
	$_REQUEST[ 'itemId'] = intval( $_REQUEST[ 'itemId']);

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

	//<!-- Prepare Image File 

		lib( array( 'File', 'Img'));

		$file = new File( Module::$name);
		Img::setPrfx( Module::$name);
	
	//End of Prepare Image File -->

	//<!-- Insert new rows
	
		if( $_GET['mod'] == 'new' && isset( $_POST['submit']))
		{
			$rltdId = $frstLngId = 0;
			while( $cols = $inpt -> getRow())
			{

				//isset( $cols[ 'pblishTime']) and $cols[ 'pblishTime'] = Date::mkTime( $cols[ 'pblishTime']);

				//<!-- Preaper Related Id

					/* For each Row ( rws) reset the Related Id*/
					$frstLngId or $frstLngId = $cols['lngId'];
					$frstLngId == $cols['lngId'] and $rltdId = 0;
					$rltdId and $cols[ 'rltdId'] = $rltdId;

				//End of Preaper Related Id-->

				//IF The Required fields are empty, ignore the insertion action.
					//if( $rltdId && !$cols['title']) continue;
					if( !$rltdId) $files = $inpt -> getFiles();
					if( empty( $files[ 'image'][ 'name']))continue;
				
				 $cols[ 'itemId']	= $_REQUEST[ 'itemId'];
				 $cols[ 'domId']	= $_cfg['domain']['id'];
				 $cols[ 'ordrId'] = 65535;//Biggest Number in two bytes.
				//$cols[ 'insrtTime'] = time();
				DB::insert( array(
						'tableName' => Module::$name .'_'. $_GET['sub'],
						'cols' => & $cols,
					)
					, Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
				);
				
				//printr( array( 'cols' => $cols, '$files' => $files));
				
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
					
					//<!-- Upload The image ...
					
						isset( $files) or $files = $inpt -> getFiles();
						$file -> save( $rltdId, $files[ 'image'][ 'tmp_name'], 'img.'. $_GET['sub'] .'.');
						unset( $files);
					
					//End of Upload The image-->
					
				}//End of if( !$rltdId)
				
			}//End of while( $cols = $inpt -> getRow());
			
			msgDie( Lang::getVal( 'inserted'), './'. URL::get( array( 'mod')), 1);
			return;

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End of Insert new rows -->

	//<!-- Update rows
	
		if( $_GET['mod'] == 'edt' && isset( $_POST['submit']))
		{
			$rltdId = intval( $_GET['id']);

			while( $cols = $inpt -> getRow())
			{
				//<!-- Upload The image ...
						
					if( isset( $cols[ 'del_image']))
					{
						$file -> delete( $rltdId);
					}
					$files = $inpt -> getFiles();
					$files['image']['name'] and $file -> save( $rltdId, $files[ 'image'][ 'tmp_name'], 'img.'. $_GET['sub'] .'.');
					
					unset( $cols[ 'del_image']);

				//End of Upload The image-->

				//isset( $cols[ 'pblishTime']) and $cols[ 'pblishTime'] = Date::mkTime( $cols[ 'pblishTime']);

				//$cols[ 'updteTime'] = time();
				DB::update( array(
						'tableName' => Module::$name . '_'. $_GET['sub'],
						'cols' 	=> & $cols,
						'where'	=> array(
							'id' => $cols[ 'id'],
							'domId' => $_cfg['domain']['id'],
						),
					)
					, Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
				);
				
				if( !$cols[ 'id'] && $cols[ 'title'])
				{
					//$cols[ 'insrtTime'] = time();
					$cols[ 'rltdId'] 	= $rltdId;
					unset( $cols[ 'updteTime']);
					DB::insert( array(
							'tableName' => Module::$name .'_'. $_GET['sub'],
							'cols' => & $cols,
						)
						, Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
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
					'rltdId' => intval( $_GET['id']),
					'domId' => $_cfg['domain']['id'],
				),
			)
		);

		$inpt -> setVals( $rws);//Call for Each Data Instance;

	}//End of if( $_GET['mod'] == 'edt');

	//<!-- Fetch the Item Title...
		
		$SQL = 'SELECT `title` FROM `'. Module::$name .'_main` WHERE `rltdId` = '. $_REQUEST[ 'itemId'] . ' AND `lngId` = '. Lang::viewId();
		$itemTitle = DB::load( $SQL, 0, 1);
		$itemTitle = $itemTitle[0];
	
	//End of Fetch the Item Title -->

	// $msg = 'Gholi kochooolooo';
	
	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'submit'),
		'CANCEL' => Lang::getVal( 'cancel'),
		
		'SUB_NAME' => Lang::getVal( $_GET['sub']),
		
		'RETURN_URL'	=> '?md='. Module::$name .'&sub='. $_GET['sub'] .'&itemId='. $_REQUEST[ 'itemId'],
		'MODULE_URL'	=> '?md='. Module::$name,
		'ITEM_TITLE'	=> & $itemTitle,
		)
	);
	
	//<!-- Prepare Form Elements...
	
		$multiRows = $_GET['mod'] == 'new' ? 10 : 1;
		
		for( $i = 0; $i != $multiRows; $i++)
		{

			$imgSrc = $file -> getPth( intval( @$_REQUEST['id']), 0, 'img.'. $_GET['sub'] .'.');
			$imgSrc and $imgSrc = '../'. Img::get( $imgSrc, array( 'h' => 120 ));
			$form[] = $inpt -> imgUpld( 'image', 0, $imgSrc);

			$form[] = $inpt -> hidden( 'id', $lngsIds /* can send an array of languages Ids*/);
			
			//<!-- Prepare the Languages Tabs ...
		
				/*$tab = new Tab( $lngsTitle, Lang::viewId());
				$form[] = '<tr><td colspan="2">'. $tab -> bar();

				foreach( $lngs as $lng)
				{
					$form[] = $tab -> opn( $lng);
					$form[] = $inpt -> text( 'title', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'size' => 40));

					//$form[] = $inpt -> textArea( 'body', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'cols' => '60', 'rows' => '15'));
				
					$form[] = $tab -> clos();
				}
				$form[] = '</td></tr>';
				/**/

			//End of Prepare the Languages Tabs-->
		
			$inpt -> incNum();

		}//End of for( $i = 0; $i != $multiRows; $i++);

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
