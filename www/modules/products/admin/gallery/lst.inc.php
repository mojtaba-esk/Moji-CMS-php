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

	//<!-- Delete The Records
		
		if( isset( $_REQUEST[ 'saveOrder']))
		{
			include( 'saveOrder.inc.php');
			return;

		}//End of if( isset( $_REQUEST[ 'saveOrder']));

	//End of Delete The Records-->

	$tpl -> set_filenames( array(
		'body' => $_GET['sub'] .'.sub.admin.list',
		)
	);
	
	//<!-- Search 

		$srchSQL = include( 'srch.inc.php');

	//End of Search-->
	
	$srchSQL .= ' AND `domId` = '. $_cfg['domain']['id'];

	//$sortSQL = isset( $_GET['sort']) ? "ORDER BY `{$_GET['sort']}` {$_GET['srtType']} " : '';

	$SQL = 'SELECT * FROM `'. Module::$name . '_'. $_GET['sub'] .'` 
		WHERE 
			`lngId` = '. Lang::viewId() .' AND 
			`itemId` = '. $_REQUEST[ 'itemId'] .' AND 
			'. $srchSQL .' ORDER BY `ordrId` ASC, `id` ASC';
		
	$pging = new Paging( array(
				'SQL' 		=> $SQL,
				'perPage'	=> Module::$opt['admnLstLmt'],
				'cachePrfx'	=> Module::$name .'_'. $_GET['sub'],
				//'excldVars'	=> array( 'vLng', 'rws[0][1][cat]'),
			)
		);
	
	$SQL = $pging -> getSQL();
	$rws = DB::load( $SQL, Module::$name . $_cfg['domain']['id'] .'_'. $_GET['sub']);
	
	//printr( $SQL);
	
	//<!-- Fetch the Item Title...
		
		$SQL = 'SELECT `title` FROM `'. Module::$name .'_main` WHERE `rltdId` = '. $_REQUEST[ 'itemId'] . ' AND `lngId` = '. Lang::viewId();
		$itemTitle = DB::load( $SQL, 0, 1);
		$itemTitle = $itemTitle[0];
	
	//End of Fetch the Item Title -->
	
	//<!-- Fetch the Type Title...
	
		$itmRws = DB::load(
			array( 
				'tableName' => Module::$name .'_types',
				'cols' => array( 'title'),
				'where' => array(
					'id' => intval( $_GET['typeId']),
				),
			)
		);
		
	//-->	
	
	$tpl -> assign_vars( array(

		'L_SEARCH' => Lang::getVal( 'search'),
		'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),

		'L_TOOLS'	=> Lang::getVal( 'tools'),
		'L_TITLE'	=> rwTitle( 'title'),
		'L_NEW'	=> Lang::getVal( 'new'),
		'NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
		'LANGUAGE_SELECTOR' => lngSelctr(),
		'DATA_EXIST'	=> sizeof( $rws),

		'L_DELETE'				=> Lang::getVal( 'delete'),
		'L_EDIT'				=> Lang::getVal( 'edit'),
		'L_DELETE_ALL_LANGS'	=> Lang::getVal( 'deleteFromAllLangs'),
		'ARE_YOU_SURE_DELETE_SELECTED'	=> Lang::getVal( 'areYouSureToDeleteSelected'),
		'ARE_YOU_SURE_DELETE_THIS'		=> Lang::getVal( 'areYouSureToDeleteThis'),

		'SEARCH_DISPLAY'=> isset( $_GET['srch']) ? 'block' : 'none',
		
		'SUB_NAME'		=> Lang::getVal( $_GET['sub']),
		'ITEM_TITLE'	=> & $itemTitle,
		
		'SORTABLE'		=>	Module::$opt['gallerySortable'] && !isset( $_GET['srch']),
		'L_SAVE_ORDER'	=>	Lang::getVal( 'saveOrder'),

		'DEL_URL'	=> URL::get(),
		'EDIT_URL'	=> URL::get( array( 'mod', 'chk')),

		'MODULE_NAME' => '<a href="?md='. Module::$name .'&sub=types">'. Lang::getVal( Module::$name) .'</a> (<a href="?md='. Module::$name .'&typeId='. intval( $_GET['typeId']) .'">'. $itmRws[0]['title'] .'</a>)',

		)
	);
	
	//<!-- Prepare Image File 

		lib( array( 'File', 'Img'));

		$file = new File( Module::$name);
		Img::setPrfx( Module::$name);
	
	//End of Prepare Image File -->


	if( is_array( $rws))
	{
		foreach( $rws as $key => $rw)
		{
			$tpl -> assign_block_vars( 'myblck',  array(

				'RW' => Lang::numFrm( $pging -> prms['strt'] + $key + 1),
				'RWID' => $key,
				'RW_ODD' => $key & 1,
				'ID' => $rw['rltdId'],

				'TITLE' => $rw['title'],
				'IMG_SRC'	=> '../'. Img::get( $file -> getPth( $rw['rltdId'], 0, 'img.'. $_GET['sub'] .'.'), array( 'h' => 80, 'w' => 120)),

				)
			);

		}//End of foreach( $rws as $key => $rw);
	
	}//End of if( sizeof( $rws));
	
	$pging -> makeLnks();
	$tpl -> assign_vars( array(

			'PAGING'	=> $pging -> lnks[ 'totlPgs'] > 1,
			'PG_LINKS'	=> & $pging -> lnks[ 'all'],
			'PG_NEXT'	=> & $pging -> lnks[ 'nxt'],
			'PG_PREV'	=> & $pging -> lnks[ 'prv'],
			'PG_FIRST'	=> & $pging -> lnks[ 'frst'],
			'PG_LAST'	=> & $pging -> lnks[ 'last'],
			
			'FIRST_PG'	=> Lang::getVal( 'firstPage'),
			'PREV_PG'	=> Lang::getVal( 'previousPage'),
			'NEXT_PG'	=> Lang::getVal( 'nextPage'),
			'LAST_PG'	=> Lang::getVal( 'lastPage'),

		)
	);

	$tpl -> display( 'body');
?>