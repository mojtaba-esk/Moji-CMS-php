<?php
/**
* @author Ghasem Babaie
* @since 2013-01-19
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


	//<!-- Insert new rows
	
		if( $_GET['mod'] == 'new' && isset( $_POST['submit']))
		{
			$rltdId = $frstLngId = 0;
			while( $cols = $inpt -> getRow())
			{
				//IF The Required fields are empty, ignore the insertion action.
				if( !$cols['title'] || !$cols['question']) continue;
				isset( $cols[ 'pblishTime']) and $cols[ 'pblishTime'] = Date::mkTime( $cols[ 'pblishTime']);
				isset( $cols[ 'startTime']) and $cols[ 'startTime'] = Date::mkTime( $cols[ 'startTime']);
				isset( $cols[ 'endTime']) and $cols[ 'endTime'] = Date::mkTime( $cols[ 'endTime']);
				
				$cols['title']		= $inpt -> dbClr( $cols['title']);
				$cols['question']	= $inpt -> dbClr( $cols['question']);
				$cols['desc']		= $inpt -> dbClr( $cols['desc']);

				//<!-- Preaper Related Id

					/* For each Row ( rws) reset the Related Id*/
					$frstLngId or $frstLngId = $cols['lngId'];
					$frstLngId == $cols['lngId'] and $rltdId = 0;
					$rltdId and $cols[ 'rltdId'] = $rltdId;

				//End of Preaper Related Id-->


				//<!-- Check for Validation...
					
					if( ($cols[ 'startTime'] > $cols[ 'endTime']) || ($cols[ 'pblishTime'] > $cols[ 'startTime']))
					{
						$msg = Lang::getVal( 'conflictDateErr');
						break;					
					}
					
				
				//-->


				$cols[ 'domId'] = $_cfg['domain']['id'];

				$cols[ 'insrtTime'] = time();
				DB::insert( array(
						'tableName' => Module::$name . '_main',
						'cols' => & $cols,
					)
					,  Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
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
					$srch -> setIndexes( $cols['title'] .' '. $cols['question'] .' '. @$cols['desc'], $rltdId, $cols['lngId']);
					
				//End of Search-->

			}//End of while( $cols = $inpt -> getRow());
			
			if( empty( $msg))
			{
				msgDie( Lang::getVal( 'inserted'), './?md='. Module::$name, 1);
				return;

			}//End of if( empty( $msg));			

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
				isset( $cols[ 'pblishTime']) and $cols[ 'pblishTime'] = Date::mkTime( $cols[ 'pblishTime']);
				isset( $cols[ 'startTime']) and $cols[ 'startTime'] = Date::mkTime( $cols[ 'startTime']);
				isset( $cols[ 'endTime']) and $cols[ 'endTime'] = Date::mkTime( $cols[ 'endTime']);				
				
				//$cols[ 'special'] = intval( @$cols[ 'special']);
				//$cols[ 'typeWrt'] = intval( @$cols[ 'typeWrt']);//Typewriter news

				$cols['title']		= $inpt -> dbClr( $cols['title']);
				$cols['question']	= $inpt -> dbClr( $cols['question']);
				$cols['desc']		= $inpt -> dbClr( $cols['desc']);

				$cols[ 'updteTime'] = time();
				$updRws = DB::update( array(
						'tableName' => Module::$name . '_main',
						'cols' 	=> & $cols,
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
					$srch -> setIndexes( $cols['title'] .' '. $cols['question'] .' '. @$cols['desc'], $rltdId, $cols['lngId']);					

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

		$inpt -> setVals( $rws);//Call for Each Data Instance;

	}//End of if( $_GET['mod'] == 'edt');
	
	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'submit'),
		'CANCEL' => Lang::getVal( 'cancel'),
		
		'RETURN_URL' => '?md='. Module::$name,

		)
	);
	
	//<!-- Prepare Form Elements...

		$form[] = $inpt -> hidden( 'id', $lngsIds /* can send an array of languages Ids*/);
		//$form[] = $inpt -> hidden( 'domId', 0, $_cfg['domain']['id']);
			
		if( $_GET['mod'] == 'edt')
		{
			$form[] = $inpt -> html( 'insrtTime', Lang::numFrm( Date::get( 'D d M Y G:i', $inpt -> getVal( 'insrtTime'))));
			$form[] = $inpt -> html( 'updteTime', $inpt -> getVal( 'updteTime') ? Lang::numFrm( Date::get( 'D d M Y G:i', $inpt -> getVal( 'updteTime'))) : Lang::getVal( 'never'));
		}			
			
		$form[] = $inpt -> date( 'pblishTime', 0 /*Language Id*/, 
					array( 
					'elmnts' => 'd,M,Y,G,i',
					//'type' => 'jalali',
					//'value' => 'now',
					'defVal'	=> $_GET['mod'] == 'new' ? 'now' : NULL,
					'difY'		=> array( -10, 1),
					'attribs' => array( 
						'dir' => Lang::$info['dir'],
						'align' => Lang::$info['align']
				)
			)
		);		
	
		$form[] = $inpt -> chkBx( 'active', 0, array( 'value' => 1, 'defChecked' => true));
		$form[] = $inpt -> chkBx( 'private', 0, array( 'value' => 1, 'defChecked' => false));
		$form[] = $inpt -> chkBx( 'onVote', 0, array( 'value' => 1, 'defChecked' => true));
		$form[] = $inpt -> chkBx( 'admAccComment', 0, array( 'value' => 1, 'defChecked' => false));			
		$form[] = $inpt -> dropDown( 'type', 0, array( 
													'items'	=> arrStLang( $tmp = enumItems( Module::$name.'_main', 'type')), 
													'dir'	=> Lang::$info['dir'],
													'align'	=> Lang::$info['align']
													
											)
									);																	
		$form[] = $inpt -> date( 'startTime', 0 /*Language Id*/, 
					array( 
					'elmnts' => 'd,M,Y,G,i',
					//'type' => 'jalali',
					//'value' => 'now',
					'defVal'	=> $_GET['mod'] == 'new' ? 'now' : NULL,
					'difY'		=> array( -10, 1),
					'attribs' => array( 
						'dir' => & Lang::$info['dir'],
						'align' => & Lang::$info['align']
						)
					)
				);
		$form[] = $inpt -> date( 'endTime', 0 /*Language Id*/, 
					array( 
					'elmnts' => 'd,M,Y,G,i',
					//'type' => 'jalali',
					//'value' => 'now',
					'defVal'	=> $_GET['mod'] == 'new' ? time() + (30 * 24 * 60 * 60) : NULL,
					'difY'		=> array( -10, 1),
					'attribs' => array( 
						'dir' => & Lang::$info['dir'],
						'align' => & Lang::$info['align']
						)
					)
				);			
														
		//<!-- Prepare the Languages Tabs ...
		
			$tab = new Tab( $lngsTitle, Lang::viewId());
			$form[] = '<tr><td colspan="2">'. $tab -> bar();

			foreach( $lngs as $lng)
			{
				$form[] = $tab -> opn( $lng);
				$form[] = $inpt -> text( 'title', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'size' => 40));
				$form[] = $inpt -> text( 'question', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'size' => 40));
				$form[] = $inpt -> textArea( 'desc', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'cols' => '60', 'rows' => '6'));
					
				$form[] = $tab -> clos();
			}
			$form[] = '</td></tr>';

		//End of Prepare the Languages Tabs-->
		
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
