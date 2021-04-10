<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. List The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	Module::$opt['admnLstLmt'] = 40;//List limit...
	
	if( empty( $_REQUEST[ 'itemId']))
	{
		msgDie( 'The item is not selected!', 0,0, 'error');
		return;
	}
	$_REQUEST[ 'itemId'] = intval( $_REQUEST[ 'itemId']);
	
	//<!-- Delete The Records
		
		if( isset( $_REQUEST[ 'del']) || isset( $_REQUEST[ 'delAllLngs']))
		{
			include( 'del.inc.php');
			return;

		}//End of if( isset( $_REQUEST[ 'del']));

	//End of Delete The Records-->

	$tpl -> set_filenames( array(
		'body' => $_GET['sub'] .'.sub.admin.list',
		)
	);
	
	//<!-- Fetch the Item Title...
		
		$SQL = 'SELECT * FROM `'. Module::$name .'_main` WHERE `id` = '. $_REQUEST[ 'itemId'];
		$item = DB::load( $SQL, 1);
		//printr( $product);
		$item = & $item[0];
	
	//End of Fetch the Item Title -->
	
	//<!-- Fetch the list of files...
		
		$rws = glob( dirname( __FILE__) .'/../../../../ext/tpl/'. $_REQUEST[ 'itemId'] .'.*');
		//printr( $rws);
	
	//-->
	
	$tpl -> assign_vars( array(

		'L_TOOLS'	=> Lang::getVal( 'tools'),
		'L_TITLE'	=> Lang::getVal( 'title'),
		'L_URL'		=> Lang::getVal( 'address'),
		'L_NEW'		=> Lang::getVal( 'new'),
		'NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
		//'LANGUAGE_SELECTOR' => lngSelctr(),
		'DATA_EXIST'	=> sizeof( $rws),

		'L_DELETE'				=> Lang::getVal( 'delete'),
		'L_EDIT'				=> Lang::getVal( 'edit'),
		'L_DELETE_ALL_LANGS'	=> Lang::getVal( 'deleteFromAllLangs'),
		'ARE_YOU_SURE_DELETE_SELECTED'	=> Lang::getVal( 'areYouSureToDeleteSelected'),
		'ARE_YOU_SURE_DELETE_THIS'		=> Lang::getVal( 'areYouSureToDeleteThis'),

		'SUB_NAME'	=> Lang::getVal( $_GET['sub']),
		'ITEM_TITLE'=> & $item['title'],
		
		'DEL_URL'	=> URL::get(),
		//'EDIT_URL'	=> URL::get( array( 'mod', 'chk')),

		)
	);
	
	is_array( $rws) or $rws = array();
	foreach( $rws as $key => $rw)
	{
		$fileName = basename( $rw);
		$tpl -> assign_block_vars( 'myblck',  array(

				'RW'	=> Lang::numFrm( $key + 1),
				'RWID'	=> $key,
				'RW_ODD' => $key & 1,
				'ID'	=> $fileName,
				'TITLE'	=> $fileName,
				'URL' 	=> '{SITE_URL}ext/tpl/'. $fileName,
			)
		);

	}//End of foreach( $rws as $key => $rw);

	$tpl -> display( 'body');
?>
