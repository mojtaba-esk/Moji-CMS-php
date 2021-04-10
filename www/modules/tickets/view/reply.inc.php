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
			'Input',
			'Search',
		)
	);

	$tpl -> display( 'header');

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
	
	//<!-- Load the Ticket info based on key...
	
		$tRws = DB::load(
			array( 
				'tableName' => Module::$name . '_main',
				'where' => array(
					'key' => $_GET['key'],
				),
			)
		);
		$tRws = & $tRws[0];
		$_GET['id'] = & $tRws['id'];

		//printr( $tRws);

		if( !$_GET['id'])
		{
			notFound();
		}

	//-->

	//<!-- Insert new rows
	
		if( $_GET['mod'] == 'reply' && isset( $_POST['submit']))
		{
			$rltdId = $frstLngId = 0;
			while( $cols = $inpt -> getRow())
			{
				//IF The Required fields are empty, ignore the insertion action.
				if( !$cols['reply']) continue;

				$iCols['body']		= $inpt -> dbClr( $cols['reply']);

				$iCols['userId']	= 0; // user
				$iCols['ticketId']	= & $_GET['id'];
				$iCols['ip']		= & $_SERVER['REMOTE_ADDR'];
				$iCols[ 'insrtTime'] = time();

				DB::insert( array(
						'tableName' => Module::$name . '_posts',
						'cols' => & $iCols,
					)
					//,  Module::$name /* Cache Prefix*/
				);
				$rltdId = DB::insrtdId(); // will be use by attachement subsystem

				DB::update( array(
						'tableName' => Module::$name . '_main',
						'cols' 	=> array( 'unread' => 1),
						'where'	=> array(
							'id' => & $_GET['id'],
						),
					)
					//, Module::$name /* Cache Prefix*/
				);

				//<!-- Upload The Attachements ...

					if( Module::$opt['attchmnt'])
					{
						require( 'attchmnt.inc.php');
					
					}//End of if( Module::$opt['attchmnt']);

				//End of Upload The Attachements-->

				//<!-- Save the Search key words... ( Indexing)
					
					isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
					$srch -> setIndexes( $iCols['body'], $_GET['id'], $cols['lngId']);

				//End of Search-->

				break;

			}//End of while( $cols = $inpt -> getRow());
			
			msgDie( Lang::getVal( 'inserted'), URL::rw( URL::get()), 1);
			return;

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End of Insert new rows -->

	$tpl -> set_filenames( array(
		'edit' => Module::$name .'.view.reply',
		)
	);

	// $msg = 'Gholi kochooolooo';
	
	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'submit'),
		'CANCEL' => Lang::getVal( 'cancel'),
		
		'RETURN_URL' => '?md='. Module::$name,
		'ACTION_TITLE' => Lang::getVal( $_GET['mod']),

		)
	);
	
	//<!-- Prepare Form Elements...

		//$form[] = $inpt -> hidden( 'id', $lngsIds /* can send an array of languages Ids*/);
		$form[] = $inpt -> html( 'title', $tRws[ 'title']);
		$form[] = $inpt -> html( 'insrtTime', Lang::numFrm( Date::get( 'D d M Y G:i', $tRws[ 'insrtTime'])));

		//<!-- Prepare the Languages Tabs ...
		
			//$tab = new Tab( $lngsTitle, Lang::viewId());
			//$form[] = '<tr><td colspan="2">'. $tab -> bar();

			//foreach( $lngs as $lng)
			{
				//$form[] = $tab -> opn( $lng);
				//$form[] = $inpt -> text( 'title', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'size' => 40));
				//$form[] = $inpt -> text( 'shortDesc', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'size' => 60));

				$form[] = $inpt -> textArea( 'reply', 0, array( 
								'class'	=> Lang::$info['dir'],
								'cols'	=> '80',
								'rows'	=> '10',
								//'fck'	=> array(
									//'lang'	=> $lng['shortName'],
									//'skin'		=> 'default' | 'office2003' | 'silver',
									//'toolBar'	=>'Default' | 'Basic',
								//),
							)
					);
				
				//$form[] = $tab -> clos();
			}
			//$form[] = '</td></tr>';

		//End of Prepare the Languages Tabs-->

		//<!-- Prepare the Attachements Form . . .
		
			if( Module::$opt['attchmnt'])
			{
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

	//<!-- List of posts...
	
		$SQL = 'SELECT
					`p`.*,
					CONCAT( `u`.`firstName`, \' \', `u`.`lastName`) AS `writer`,
					`a`.`id`	AS	`atchId`,
					`a`.`fileName`
				FROM
					`'. Module::$name . '_posts`	AS	`p`
					LEFT JOIN
					`admin_users_main`				AS	`u`
					ON
						`p`.`userId` = `u`.`id`
					LEFT JOIN
						`'. Module::$name . '_attachments`	AS	`a`
					ON
						`a`.`itemId` = `p`.`id`
				WHERE
					`p`.`ticketId` = '. intval( $_GET['id']) .'
				ORDER BY
					`p`.`id` ASC';

		$rws = DB::load( $SQL);
		
		//printr( $rws);

		is_array( $rws) or $rws = array();
		$total = sizeof( $rws);
		for( $i = 0; $i != $total; $i++)
		{
			$tpl -> assign_block_vars( 'postsblck',  array(

				'RW' => Lang::numFrm( $i + 1),
				'RWID' => $i,
				'RW_ODD' => intval( $rws[ $i ]['userId'] != 0), // supporters have different color
				'ID' => & $rws[ $i ]['id'],

				//'IP' 	 => & $rws[ $i ]['ip'],
				'WRITER' => $rws[ $i ]['userId'] ? $rws[ $i ]['writer'] : Lang::getVal( 'user'),
				'BODY'	 => nl2br( $rws[ $i ]['body']),
				'PUBLISH_TIME' => Lang::numFrm( Date::get( 'D d M Y - G:i', $rws[ $i ][ 'insrtTime'])),

				'EXTRA_CLASS'	=> '',
				
				'ATTCHMNT'		=> $rws[ $i ]['atchId'] ? ( '[ <a href="'. URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&mod=download&key='. $_GET['key'] .'&id='. $rws[ $i ][ 'atchId']) .'" title="'. $rws[ $i ]['fileName'] .'">'. Lang::getVal( 'attchmnt') .'</a> ]') : '',

				)
			);

		}//End of foreach( $rws as $key => $rw);

	//-->

	$tpl -> display( 'edit');
?>
