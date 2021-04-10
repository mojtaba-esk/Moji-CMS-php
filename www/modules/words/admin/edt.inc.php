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
				if( !$cols['key'] || !$cols['value']) continue;
				
				$cols['mds'] = isset( $cols['loadInAllModules']) ? ',0,' : '';
				unset( $cols['loadInAllModules']);
				$cols[ 'updteTime'] = time();

				DB::insert( array(
						'tableName' => Module::$name . '_main',
						'cols' => & $cols,
					)
					,  Module::$name /* Cache Prefix*/
				);
				
				sLog( array(
							'itemId'	=> 1,
							'desc'		=> & $cols['value'],
						)
					);
				

			}//End of while( $cols = $inpt -> getRow());
			
			msgDie( Lang::getVal( 'inserted'), './?md='. Module::$name, 1);
			return;

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End of Insert new rows -->

	//<!-- Update rows
	
		if( $_GET['mod'] == 'edt' && isset( $_POST['submit']))
		{
			while( $cols = $inpt -> getRow())
			{
				$SQL = "SELECT COUNT(*) FROM `". Module::$name ."_main` WHERE `key` = '{$_GET[ 'id']}' AND `lngId` = '{$cols[ 'lngId']}'";
				$exst = DB::load( $SQL, NULL, 1);

				$cols['mds'] = isset( $cols['loadInAllModules']) ? ',0,' : '';
				unset( $cols['loadInAllModules']);
				$cols[ 'updteTime'] = time();
				
				if( !$exst[ 0] && !empty( $cols['value']))
				{
					DB::insert( array(
							'tableName' => Module::$name . '_main',
							'cols' => & $cols,
						)
						, Module::$name /* Cache Prefix*/
					);
					
					sLog( array(
								'itemId'	=> 1,
								'desc'		=> & $cols['value'],
								'action'	=> 'new',
							)
						);


				}else{

					//unset( $cols['lngId'], $cols['key']);
					DB::update( array(
							'tableName' => Module::$name . '_main',
							'cols' 	=> & $cols,
							'where'	=> array(
								'key' => $_GET[ 'id'],
								'lngId' => $cols['lngId'],
							),
						)
						, Module::$name /* Cache Prefix*/
					);
					
					sLog( array(
							'itemId'	=> 1,
							'desc'		=> & $cols['value'],
						)
					);

				
				}//End of if( !$exst[ 0]);

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
					'key'	=> $_GET['id'],
					'domId'	=> 0,
				),
			)
		);
		
		$rws or $rws[0] = array( 'key' => $_GET['id']);
		if( strpos( @$rws[0]['mds'], ',0,') !== false) $rws[0]['loadInAllModules'] = 1;

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
	
	//<!-- Prepare Form Elements, and sent to Template

		//$form[] = $inpt -> hidden( 'id', $lngsIds /* can send an array of languages Ids*/);
		$form[] = $inpt -> text( 'key', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 30));
		
		$form[] = $inpt -> chkBx( 'loadInAllModules', 0, array( 'value' => 1));
			
		//<!-- Prepare the Languages Tabs ...
		
			$tab = new Tab( $lngsTitle, Lang::viewId());
			$form[] = '<tr><td colspan="2">'. $tab -> bar();

			foreach( $lngs as $lng)
			{
				$form[] = $tab -> opn( $lng);
				
				$form[] = $inpt -> text( 'value', $lng[ 'id'], array( 'dir' => $lng['dir'], 'align' => $lng['align'], 'size' => 40, 'focus' => 1));

				$form[] = $tab -> clos();
			}
			$form[] = '</td></tr>';

		//End of Prepare the Languages Tabs-->
		
		//<!-- List of modules which use this word...

			$mds = preg_replace( '/[,,]+/', ',', $inpt -> getVal( 'mds'));

			if( !empty( $mds))
			{
				$SQL = 'SELECT
							`name`
						FROM
							`modules`
						WHERE
							`id` IN ( 0'. $mds .'0)';
				$mdRws = DB::load( $SQL, 0, true);
				$mdRws = arrStLang( $mdRws);
				
				$form[] = $inpt -> fldSetOpn( Lang::getVal( 'usedInMds'));
				
					//$form[] = $inpt -> html( NULL, implode( '<br />', $mdRws));
					$form[] = $inpt -> html( NULL, '<ol><li>'. implode( '</li><li>', $mdRws). '</li></ol>');
				
				$form[] = $inpt -> fldSetClos();
				
			}//End of if( !empty( $mds));

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
