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

	//<!-- Update rows
	
		if( $_GET['mod'] == 'edt' && isset( $_POST['submit']))
		{
			if( $cols = $inpt -> getRow())
			{
				unset( $cols['lngId']);
				$cols['body'] = addslashes( $cols['body']);

				if( $cols['id'])
				{
					DB::update( array(
							'tableName' => $_GET['sub'],
							'cols' 	=> & $cols,
							'where'	=> array( 
								'id' => $cols['id'],
							),
						)
						, true /* Cache Prefix*/
					);
				
				}else{
					
					$_GET['q']['body'] = & $cols['body'];
					DB::insert( array(
							'tableName' => $_GET['sub'],
							'cols' => & $_GET['q'],
						)
						, true /* Cache Prefix*/
					);
				
				}//End of if( $cols['id']);
				
			}//End of while( $cols = $inpt -> getRow());

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
		$whr = isset( $_GET['id']) ? array( 'id' => $_GET['id']) : $_GET['q'];
		$rws = DB::load(
			array( 
				'tableName' => $_GET['sub'],
				'where' => & $whr,
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

		$form[] = $inpt -> hidden( 'id');
		$form[] = $inpt -> textArea( 'body', 0, array( 'class' => Lang::$info['dir'], 'cols' => '90', 'rows' => '5', 'focus' => 1));

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
