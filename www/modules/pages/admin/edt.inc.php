<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	isset( $_POST['mod']) and $_GET['mod'] = $_POST['mod'];

	lib( array( 
			'Tab',
			'File',
			'Img',
			'Addable'
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
				isset( $cols['name']) or $cols['name'] = $_GET['name'];

				//IF The Required fields are empty, ignore the insertion action.
				if( !$cols['name'] || !$cols['body']) continue;

				//<!-- Preaper Related Id

					/* For each Row ( rws) reset the Related Id*/
					$frstLngId or $frstLngId = $cols['lngId'];
					$frstLngId == $cols['lngId'] and $rltdId = 0;
					$rltdId and $cols[ 'rltdId'] = $rltdId;

				//End of Preaper Related Id-->
				
				$cols[ 'domId'] = $_cfg['domain']['id'];
				$cols[ 'insrtTime'] = time();
				DB::insert( array(
						'tableName' => Module::$name . '_main',
						'cols' => & $cols,
					)
					,  Module::$name /* Cache Prefix*/
				);
				
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
								'desc'		=> Lang::getVal( $cols[ 'name']) .' - '. briefStr( strip_tags( $cols['body']), 100),
							)
						);
					
					//<!-- Upload The image ...
					
						$files = $inpt -> getFiles();
						$file -> save( $rltdId, $files[ 'image'][ 'tmp_name']);
					
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
					$srch -> setIndexes( $cols['name'] .' '. $cols['body'], $rltdId, $cols['lngId']);
					
				//End of Search-->

			}//End of while( $cols = $inpt -> getRow());
			
			msgDie( Lang::getVal( 'inserted'), './?md='. Module::$name, 1);
			return;

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End of Insert new rows -->

	//<!-- Update rows
	
		if( $_GET['mod'] == 'edt' && isset( $_POST['submit']))
		{
			//$rltdId = intval( @$_GET['id']);
			
			//<!-- Fetch The Related Id By name
			
				/*$SQL = '
					SELECT `rltdId`
					FROM
						`'. Module::$name . '_main`
					WHERE
						`name` = '{$_GET['name']}'
				';
				
				$rw = DB::load( $SQL, 0, 1);
				$rltdId = $rw[0];/**/

			//-->

			while( $cols = $inpt -> getRow())
			{
				isset( $cols['name']) or $cols['name'] = $_GET['name'];
				$rltdId = $cols['rltdId'];

				//<!-- Upload The image ...
						
					if( isset( $cols[ 'del_image']))
					{
						$file -> delete( $rltdId);
					}
					$files = $inpt -> getFiles();
					$files['image']['name'] and $file -> save( $rltdId, $files[ 'image'][ 'tmp_name']);
						
				//End of Upload The image-->

				//<!-- Upload The Attachements ...

					if( Module::$opt['attchmnt'])
					{
						require( 'attchmnt.inc.php');
				
					}//End of if( Module::$opt['attchmnt']);

				//End of Upload The Attachements-->

				$cols[ 'updteTime'] = time();
				DB::update( array(
						'tableName' => Module::$name . '_main',
						'cols' 	=> & $cols,
						'where'	=> array(
							'id'	=> $cols[ 'id'],
							'domId' => $_cfg['domain']['id'],
						),
					)
					, Module::$name /* Cache Prefix*/
				);

				sLog( array(
							'itemId'	=> $cols[ 'id'],
							'desc'		=> Lang::getVal( $cols[ 'name']) .' - '. briefStr( strip_tags( $cols['body']), 100),
						)
					);

				if( !$cols[ 'id'] && $cols[ 'body'])
				{
					$cols[ 'insrtTime'] = time();
					$cols[ 'rltdId'] 	= $rltdId;
					$cols[ 'domId']		= $_cfg['domain']['id'];
					unset( $cols[ 'updteTime']);
					DB::insert( array(
							'tableName' => Module::$name . '_main',
							'cols' => & $cols,
						)
						, Module::$name /* Cache Prefix*/
					);

					sLog( array(
							'itemId'	=> DB::insrtdId(),
							'desc'		=> Lang::getVal( $cols[ 'name']) .' - '. briefStr( strip_tags( $cols['body']), 100),
							'action'	=> 'new',
						)
					);

				}//End of if( !$cols[ 'id'] && $cols[ 'name']);
				
				//<!-- Save the Search key words... ( Indexing)

					isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
					$srch -> setIndexes( $cols['name'] .' '. $cols['body'], $rltdId, $cols['lngId']);

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
					'name'	=> @$_GET['name'],
					'domId'	=> $_cfg['domain']['id'],
				),
			)
		);		

		$rws or $_GET['mod'] = 'new';

		$rws or $rws[0] = array( 'name' => @$_GET['name']);
		isset( $_GET['id']) or $_GET['id'] = @$rws[0]['rltdId'];

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

		defined( 'DEBUG_MODE') and $form[] = $inpt -> prValidate( 'form' /* HTML Form Id*/);
		
		if( Module::$opt['imageFile'])
		{
			$imgSrc = $file -> getPth( intval( @$_GET['id']));
			$imgSrc and $imgSrc = '../'. Img::get( $imgSrc, array( 'h' => 120 ));

			$form[] = $inpt -> imgUpld( 'image', 0, $imgSrc);
		
		}//End of if( Module::$opt['imageFile']);

		$form[] = $inpt -> hidden( 'id', $lngsIds /* can send an array of languages Ids*/);
		$form[] = $inpt -> hidden( 'rltdId', 0);
		
		$_REQUEST['mod'] = $_GET['mod'];
		$form[] = $inpt -> hiddenRqst( array( 'mod'));

		defined( 'DEBUG_MODE') and $form[] = $inpt -> text( 'name', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 40, 'validate' => 'required'));
		
		if( $_GET['mod'] == 'edt')
		{
			//$form[] = $inpt -> html( 'insrtTime', Lang::numFrm( Date::get( 'D d M Y G:i', $inpt -> getVal( 'insrtTime'))));
			$form[] = $inpt -> html( 'updteTime', $inpt -> getVal( 'updteTime') ? Lang::numFrm( Date::get( 'D d M Y G:i', $inpt -> getVal( 'updteTime'))) : Lang::getVal( 'never'));
		}
		
		//<!-- Prepare the Languages Tabs ...
		
			$tab = new Tab( $lngsTitle, Lang::viewId());
			$form[] = '<tr><td colspan="2">'. $tab -> bar();

			foreach( $lngs as $lng)
			{
				$form[] = $tab -> opn( $lng);
				
				$form[] = $inpt -> textArea( 'body', $lng[ 'id'], array( 
								'dir'	=> $lng['dir'],
								'align'	=> $lng['align'],
								'cols'	=> '60',
								'rows'	=> '15',
								'height'=>	550,
								'fck'	=> array(
									'lang'	=> $lng['shortName'],
									'skin'	=> isset( Module::$opt['editorSkin']) ? Module::$opt['editorSkin'] : 'default',// 'default' | 'office2003' | 'silver',
									'toolBar'	=>	isset( Module::$opt['editorToolBar']) ? Module::$opt['editorToolBar'] : 'Basic',// Default, Basic, 
								),
							)
					);
				
				$form[] = $tab -> clos();
			}
			$form[] = '</td></tr>';

		//End of Prepare the Languages Tabs-->

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
