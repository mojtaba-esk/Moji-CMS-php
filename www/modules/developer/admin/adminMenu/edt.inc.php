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
				if( !$cols['title']) continue;
				
				unset( $cols['lngId']);
				DB::insert( array(
						'tableName' => 'admin_menu',
						'cols' => & $cols,
					)
					//, 'tpl_'. $cols['name'] /* Cache Prefix*/
				);
				break;

			}//End of while( $cols = $inpt -> getRow());
			
			Cache::clean( 'admin_menu' /* Cache Prefix*/, '');

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
				$cols[ 'inHomePg'] = intval( @$cols[ 'inHomePg']);//Das ist ein Check Box;

				DB::update( array(
						'tableName' => 'admin_menu',
						'cols' 	=> & $cols,
						'where'	=> array(
							'id' => $_GET[ 'id'],
						),
					)
					//, 'tpl_'. $cols['name'] /* Cache Prefix*/
				);
				
			}//End of while( $cols = $inpt -> getRow());
			
			Cache::clean( 'admin_menu' /* Cache Prefix*/, '');

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
				'tableName' => 'admin_menu',
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

		$form[] = $inpt -> text( 'title', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 40));
		$form[] = $inpt -> text( 'link', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 40));
		
		//<!-- Modules Drop Down List...
		
			$SQL = 'SELECT `id`, `name` FROM `modules`';
			$rws = DB::load( $SQL);

			$itms = array();
			$itms[ 0 ] = 'developer';

			$rws or $rws = array();
			foreach( $rws as $rw)
			{
				$itms[ $rw[ 'id']] = $rw[ 'name'];
			}

			$form[] = $inpt -> dropDown( 'mdId', 0, array( 
												'items'	=> $itms,
												'dir'	=> 'ltr',
												'align'	=> 'left'
										)
								);

		//End of Modules Drop Down List.-->
		
		//<!-- Parents Drop Down List...
		
			$SQL = '
				SELECT
					`id`,
					`title`
				FROM
					`admin_menu` 
				WHERE
					`parentId` = 0
				ORDER BY `orderId` ASC';
			$rws = DB::load( $SQL);

			$itms = array();
			$itms[ 0 ]	= 'No Parent';
			$itms[ -1 ]	= 'developer';

			$rws or $rws = array();
			foreach( $rws as $rw)
			{
				$itms[ $rw[ 'id']] = $rw[ 'title'];
			}

			$form[] = $inpt -> dropDown( 'parentId', 0, array( 
												'items'	=> $itms,
												'dir'	=> 'ltr',
												'align'	=> 'left'
										)
								);

		//End of Parents Drop Down List.-->
		
		$form[] = $inpt -> chkBx( 'inHomePg', 0, array( 'value' => 1));
		
		$form[] = $inpt -> text( 'orderId', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 10));

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
