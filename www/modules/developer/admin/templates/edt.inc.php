<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

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
			while( $cols = $inpt -> getRow())
			{
				//IF The Required fields are empty, ignore the insertion action.
				if( !$cols['name']) continue;
				
				$cols['content'] = str_replace( '\\"', '"', $cols['content']);
				$cols['content'] = str_replace( "\\'", "'", $cols['content']);
				$cols['content'] = $inpt -> dbClr( $cols['content']);
				
				unset( $cols['lngId']);
				DB::insert( array(
						'tableName' => $_GET['sub'],
						'cols' => & $cols,
					)
					//, 'tpl_'. $cols['name'] /* Cache Prefix*/
				);
				break;

			}//End of while( $cols = $inpt -> getRow());
			
			msgDie( Lang::getVal( 'inserted'), './?md='. Module::$name .'&sub='. $_GET['sub'], 1);
			return;

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End of Insert new rows -->

	//<!-- Update rows
	
		if( $_GET['mod'] == 'edt' && isset( $_POST['submit']))
		{
			while( $cols = $inpt -> getRow())
			{
				unset( $cols['lngId']);
				
				$cols['content'] = str_replace( '\\"', '"', $cols['content']);
				$cols['content'] = str_replace( "\\'", "'", $cols['content']);
				$cols['content'] = $inpt -> dbClr( $cols['content']);

				DB::update( array(
						'tableName' => & $_GET['sub'],
						'cols' 	=> & $cols,
						'where'	=> array(
							'name' => $_GET[ 'id'],
						),
					)
					//, 'tpl_'. $cols['name'] /* Cache Prefix*/
				);
				
			}//End of while( $cols = $inpt -> getRow());
			
			Cache::clean( 'tpl_'. $cols['name'] /* Cache Prefix*/, '');

			msgDie( Lang::getVal( 'updated'), './'. URL::get(), 0);
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
				'tableName' => $_GET['sub'],
				'where' => array(
					'name' => $_GET['id'],
				),
			)
		);
		
		//$rws[0]['content'] = str_replace( "\\'", "'", $rws[0]['content']);
		//$rws[0]['content'] = str_replace( '\\"', '"', $rws[0]['content']);
		$rws[0]['content'] = htmlspecialchars( $rws[0]['content']);
		
		//printr( $rws[0]['content']);

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

		$form[] = $inpt -> text( 'name', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 40));
		$form[] = $inpt -> textArea( 'content', 0, array( 'dir' => 'ltr', 'align' => 'left', 'cols' => '120', 'rows' => '45', 'focus' => 1));

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
