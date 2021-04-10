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
	
	$_GET['id'] = $inpt -> dbClr( $_GET['id']);

	//<!-- Update rows
	
		if( $_GET['mod'] == 'edt' && isset( $_POST['submit']))
		{
			while( $cols = $inpt -> getRow())
			{
				if( empty( $cols['value'])) continue;
				
				$cols['mds']	= $inpt -> dbClr( $cols['mds']);
				$cols['value']	= $inpt -> dbClr( $cols['value']);
				$cols['lngId']	= intval( $cols['lngId']);
				$cols['frqUsed']= intval( $cols['frqUsed']);
				$cols['key']	= $_GET['id'];
				$cols['updteTime'] = time();

				$SQL = "REPLACE INTO 
							`words_main`
						SET
							`key`		= '{$cols['key']}',
							`domId`		= {$_cfg['domain']['id']},
							`lngId` 	= '{$cols['lngId']}',
							`value`		= '{$cols['value']}',
							`frqUsed` 	= '{$cols['frqUsed']}',
							`mds` 		= '{$cols['mds']}',
							`updteTime`	= '{$cols['updteTime']}'
						";

				DB::exec( $SQL);
				Cache::clean( 'words'. $_cfg['domain']['id']);
				Cache::clean( 'admin_menu_'. $_cfg['domain']['id']);

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
				'tableName' => 'words_main',
				'where' => array(
					'key'	=>	$_GET['id'],
					'domId'	=>	$_cfg['domain']['id']
				),
			)
		);
		
		if( empty( $rws))
		{
		
			$rws = DB::load(
				array( 
					'tableName' => 'words_main',
					'where' => array(
						'key'	=>	$_GET['id'],
						'domId'	=>	0,
					),
				)
			);

		}//End of if( empty( $rws));

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
		
		$form[] = $inpt -> hidden( 'mds', 0);
		$form[] = $inpt -> hidden( 'frqUsed', 0);

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
