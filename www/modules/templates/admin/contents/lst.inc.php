<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. List The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	//<!-- Delete The Records
		
		if( isset( $_REQUEST[ 'del']) || isset( $_REQUEST[ 'delAllLngs']))
		{
			include( 'del.inc.php');
			return;

		}//End of if( isset( $_REQUEST[ 'del']));

	//End of Delete The Records-->


	$tpl -> set_filenames( array(
		'body' => $_GET['sub'] .'.sub.'. Module::$name .'.admin.list',
		)
	);
	
	//<!-- Search 

		$srchSQL = include( 'srch.inc.php');
		$srchSQL .= ' AND `tmpId` = '. intval( $_GET['itemId']);

	//End of Search-->

	$sortSQL = isset( $_GET['sort']) ? "ORDER BY `{$_GET['sort']}` {$_GET['srtType']} " : '';

	$SQL = 'SELECT `name` AS `id` FROM `'. Module::$name .'_'. $_GET['sub'] .'` 
		WHERE '. $srchSQL .' '. $sortSQL;

	$pging = new Paging( array(
				'SQL' 		=> $SQL,
				'perPage'	=> 20,
				//'cachePrfx'	=> Module::$name .'_'. $_GET['sub'],
				//'excldVars'	=> array( 'vLng', 'rws[0][1][cat]'),
			)
		);
	
	$SQL = $pging -> getSQL();
	$rws = DB::load( $SQL);
	
	//printr( $SQL);
	
	//<!-- Fetch the Item title...
	
		$itmRws = DB::load(
			array( 
				'tableName' => Module::$name .'_main',
				'cols' => array( 'title'),
				'where' => array(
					'id' => intval( $_GET['itemId']),
				),
			)
		);
		
	//-->
	
	$tpl -> assign_vars( array(

		'L_SEARCH' => Lang::getVal( 'search'),
		'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),

		'L_TOOLS'	=> Lang::getVal( 'tools'),
		'L_TITLE'	=> rwTitle( 'name'),
		'L_NEW'	=> Lang::getVal( 'new'),
		'NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
		//'LANGUAGE_SELECTOR' => lngSelctr(),
		'DATA_EXIST'	=> sizeof( $rws),

		'L_DELETE'			=> Lang::getVal( 'delete'),
		'L_EDIT'				=> Lang::getVal( 'edit'),
		'L_DELETE_ALL_LANGS'	=> Lang::getVal( 'deleteFromAllLangs'),
		'ARE_YOU_SURE_DELETE_SELECTED'	=> Lang::getVal( 'areYouSureToDeleteSelected'),
		'ARE_YOU_SURE_DELETE_THIS'		=> Lang::getVal( 'areYouSureToDeleteThis'),

		'SUB_NAME'	=> Lang::getVal( $_GET['sub']),
		'ITEM_TITLE'=> & $itmRws[0]['title'],
		
		'DEL_URL'	=> URL::get(),
		'EDIT_URL'	=> URL::get( array( 'mod', 'chk')),

		'ARE_YOU_SURE_DELETE_SELECTED' => Lang::getVal( 'areYouSureToDeleteSelected'),
		'ARE_YOU_SURE_DELETE_THIS' => Lang::getVal( 'areYouSureToDeleteThis'),
		
		'SRCH_CANCEL_URL'	=> URL::get( array( 'srch', 'srh')),

		)
	);

	if( is_array( $rws))
	{
		foreach( $rws as $key => $rw)
		{
			$tpl -> assign_block_vars( 'myblck',  array(

				'RW' => Lang::numFrm( $pging -> prms['strt'] + $key + 1),
				'RWID' => $key,
				'RW_ODD' => $key & 1,
				'ID' => $rw['id'],
				
				'TITLE' => $rw['id'],
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
