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
				
				$uCols['name'] = $cols['name'];
				
				//<!-- options...

					$uCols['options'] = $uCols['values'] = '';
					for( $i = 0; 1; $i++)
					{
						if( !isset( $cols['options_'. $i]) || empty( $cols['options_'. $i])) break;
					
						$uCols['options']	.= $cols['options_'. $i]	.',';
						$uCols['values']	.= $cols['values_'. $i]		.',';
				
					}//End of for( $i = 0; 1; $i++);
				
					$uCols['options'] = trim( $uCols['options'], ',');
					$uCols['values'] = trim( $uCols['values'], ',');

				//End of options-->					
				
				DB::insert( array(
						'tableName' => $_GET['sub'],
						'cols' => & $uCols,
					)
					,$_GET['sub'] /* Cache Prefix*/
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
				$uCols['name'] = $cols['name'];
				
				//<!-- options...

					$uCols['options'] = $uCols['values'] = '';
					for( $i = 0; 1; $i++)
					{
						if( !isset( $cols['options_'. $i]) || empty( $cols['options_'. $i])) break;
					
						$uCols['options']	.= $cols['options_'. $i]	.',';
						$uCols['values']	.= $cols['values_'. $i]		.',';
				
					}//End of for( $i = 0; 1; $i++);
				
					$uCols['options'] = trim( $uCols['options'], ',');
					$uCols['values'] = trim( $uCols['values'], ',');

				//End of options-->					

				DB::update( array(
						'tableName' => $_GET['sub'],
						'cols' 	=> & $uCols,
						'where'	=> array(
							'id' => $_GET[ 'id'],
						),
					)
					, $_GET['sub'] /* Cache Prefix*/
				);
				
			}//End of while( $cols = $inpt -> getRow());
			
			//Cache::clean( $_GET['sub'] /* Cache Prefix*/, '');

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
					'id' => $_GET['id'],
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

		$form[] = $inpt -> text( 'name', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 40));
		
		
		//<!-- Module Options...

			$form[] = $inpt -> html( 'moduleOptions');
			
			$rws[0]['options']	= explode( ',', @$rws[0]['options']);
			$rws[0]['values']	= explode( ',', @$rws[0]['values']);
		
			$rws[0]['options'][] = $rws[0]['values'][] = '';
		
			$sizeOf = sizeof( $rws[0]['options']);
			for( $id = 0; $id != $sizeOf; $id++)
			{
			
				$opt = $inpt -> text( 'options_'. $id, 0, array( 'value' => $rws[0]['options'][$id], 'dir' => 'ltr', 'align' => 'left', 'size' => 20), false);
				$val = $inpt -> text( 'values_'. $id, 0, array( 'value' => $rws[0]['values'][$id], 'dir' => 'ltr', 'align' => 'left', 'size' => 20), false);
				$form[] = $inpt -> tmplt( NULL, $opt .' : '. $val);

			}//End of for( $id = 0; $id <= $sizeOf; $id++);

		//End of Module Options-->
		
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
