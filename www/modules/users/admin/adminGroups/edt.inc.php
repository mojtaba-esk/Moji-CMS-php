<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( isset( $grInf))
	{
		msgDie( Lang::getVal( 'accessDenied'), NULL, 0, 'error');
		return;
	}

	lib( array(
			
//			'Tab',
			'File',
			'Img',
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
	
	require( 'functions.inc.php'); //[ $inpt ] object is needed for this inclusion.

	//<!-- Insert new rows
	
		if( $_GET['mod'] == 'new' && isset( $_POST['submit']))
		{
			while( $cols = $inpt -> getRow())
			{
				$uCols['title'] = $cols['title'];
				$uCols['permission'] = serialize( $_POST['permission']);
				$uCols['productId'] = $cols['productId'];
				
				DB::insert( array(
						'tableName' => 'admin_users_groups',
						'cols' => & $uCols,
					)
					, Module::$name /* Cache Prefix*/
				);
				
				sLog( array(
							'itemId'	=> DB::insrtdId(),
							'desc'		=> & $uCols['title'],
						)
					);
				
				msgDie( Lang::getVal( 'inserted'), './?md='. Module::$name .'&sub='. $_GET['sub'], 1);
				return;

			}//End of while( $cols = $inpt -> getRow());
			
		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End of Insert new rows -->

	//<!-- Update rows
	
		if( $_GET['mod'] == 'edt' && isset( $_POST['submit']))
		{
			while( $cols = $inpt -> getRow())
			{
				$uCols['title'] = $cols['title'];
				$uCols['permission'] = serialize( $_POST['permission']);
				$uCols['productId'] = $cols['productId'];
				
				DB::update( array(
						'tableName' => 'admin_users_groups',
						'cols' 	=> & $uCols,
						'where'	=> array(
							'id' => $_GET['id'],
						),
					), Module::$name /* Cache Prefix*/
				);
				
				sLog( array(
							'desc'	=> & $uCols['title'],
						)
					);

				Cache::clean( 'admin_menu' /* Cache Prefix*/, '');

				msgDie( Lang::getVal( 'updated'), './'. URL::get( array( 'id', 'mod')), 1);
				return;

			}//End of while( $cols = $inpt -> getRow());

		}//End of if( $_GET['mod'] == 'edt' && isset( $_POST));
	
	//End of Update rows -->

	$tpl -> set_filenames( array(
		'edit' => $_GET['sub'] .'.sub.admin.edit',
		)
	);

	if( $_GET['mod'] == 'edt' && !isset( $_POST['submit']))
	{
		$rws = DB::load(
			array( 
				'tableName' => 'admin_users_groups',
				'where' => array(
					'id' => intval( $_GET['id']),
				),
			)
		);
		

		$permission = unserialize( $rws[0]['permission']);

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

		$form[] = $inpt -> text( 'title', 0, array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'size' => 30));

		//<!-- Permissions...
		
			$form[] = $inpt -> html( 'permission');

			$SQL = '
				SELECT
					`id`,
					`name` 
				FROM
					`modules` 
				';
			$rws = DB::load( $SQL);
			
			$rws[] = array( 'id' => -1, 'name' => 'adminPanel');
			foreach( $rws as $rw)
			{
				$form[] = $inpt -> chkBx( 
							$rw['name'],
							0,
							array( 
								'name'	=> 'permission['. $rw['id'] .']',
								'value'	=> 1,
								'checked' => @$permission[ $rw['id'] ] ? 'checked' : NULL,
							)
						);
						
				$mdPermissionsArr = @include( dirname( __FILE__) .'/../../../'. $rw['name'] .'/admin/permission.inc.php' );
				if( !is_array( $mdPermissionsArr)) continue;
				
				$form[] = $inpt -> fldSetOpn( Lang::getVal( $rw['name']));
				$form[] = mkPermissionChks( $mdPermissionsArr, $rw['id'], $permission);
				$form[] = $inpt -> fldSetClos();
				
			}//End of foreach( $rws as $rw);
			
		//End of Permissions.-->
		
		$form[] = $inpt -> html();
		
		$form[] = $inpt -> fldSetOpn( Lang::getVal( 'productPermission'));
		
		//<!-- List of products...
		
			$rws = DB::load( array( 'tableName' => 'products_main'));

			$products = array();
			$products[ 0 ] = Lang::getVal( 'all');
			foreach( $rws as $rw)
			{
				$products[ $rw['id'] ] = $rw['title'];
		
			}// End of foreach( $rws as $rw);
		
			$form[] = $inpt -> dropDown( 'productId', 0, array( 
														'items'	=> & $products,
														'dir'	=> & Lang::$info['dir'],
														'align'	=> & Lang::$info['align']
												)
										);
		//-->
		
		$form[] = $inpt -> fldSetClos();
		
		$form[] = $inpt -> html();
		
		for( $i = 0; $i != sizeof( $form); $i++)
		{
			$tpl -> assign_block_vars( 'myblck',  array(
					'INPUT' => & $form[ $i]
				)
			);
		}

	//End of Prepare Form Elements, and sent to Template-->

	$tpl -> display( 'edit');
	
	/*---------------------------------------------------------------*/

?>
