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
	
	//<!-- Save the new order...
		
		if( isset( $_REQUEST[ 'saveOrder']))
		{
			include( 'saveOrder.inc.php');
			return;

		}//End of if( isset( $_REQUEST[ 'saveOrder']));

	//End of saving the new order-->	

	$tpl -> set_filenames( array(
		'body' => $_GET['sub'] .'.sub.'. Module::$name .'.admin.list',
		)
	);
	
	//<!-- Search 

		$srchSQL = include( 'srch.inc.php');
		$srchSQL .= ' AND `domId` = '. $_cfg['domain']['id'];
		$srchSQL .= ' AND `typeId` = '. intval( $_GET['typeId']);

	//End of Search-->

	//$sortSQL = isset( $_GET['sort']) ? "ORDER BY `{$_GET['sort']}` {$_GET['srtType']} " : '';
	$sortSQL = 'ORDER BY `ordrId` ASC, `id` ASC';

	$SQL = 'SELECT * FROM `'. Module::$name . '_'. $_GET['sub'] .'` 
		WHERE `lngId` = '. Lang::viewId().' AND '. $srchSQL .' '. $sortSQL;
		
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
	
	//<!-- Fetch the Item title...
	
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

		'L_DELETE'			=> Lang::getVal( 'delete'),
		'L_EDIT'				=> Lang::getVal( 'edit'),
		'L_DELETE_ALL_LANGS'	=> Lang::getVal( 'deleteFromAllLangs'),
		'ARE_YOU_SURE_DELETE_SELECTED'	=> Lang::getVal( 'areYouSureToDeleteSelected'),
		'ARE_YOU_SURE_DELETE_THIS'		=> Lang::getVal( 'areYouSureToDeleteThis'),

		'SEARCH_DISPLAY'	=> isset( $_GET['srch']) ? 'block' : 'none',
		
		'SUB_NAME'	=> Lang::getVal( $_GET['sub']),
		'ITEM_TITLE'=> & $itmRws[0]['title'],
		
		'SORTABLE'	=>	Module::$opt[ $_GET['sub'] .'Sortable'] && !isset( $_GET['srch']),
		'L_SAVE_ORDER'	=> Lang::getVal( 'saveOrder'),
		
		'DEL_URL'	=> URL::get(),
		'EDIT_URL'	=> URL::get( array( 'mod', 'chk')),
		
		'RETURN_URL'	=> '?md='. Module::$name .'&sub=types',
		'RETURN_NAME'	=>	Lang::getVal( 'types'),
		
		)
	);

	$fldInfo = require( dirname( __FILE__). '/../fldInfo.inc.php');
	
	if( is_array( $rws))
	{
		foreach( $rws as $key => $rw)
		{
			$tpl -> assign_block_vars( 'myblck',  array(

				'RW'	=> Lang::numFrm( $pging -> prms['strt'] + $key + 1),
				'RWID'	=> $key,
				'RW_ODD' => $key & 1,
				'ID'	=> $rw['rltdId'],

				'TITLE_P'	=> $rw['title'],
				'TITLE'		=> $fldInfo[ $rw['fldType'] ]['type'] == 'multi' ? ('<a href="?md=products&sub=types&sub=fields_params&typeId='. $_GET['typeId'] .'&fldId='. $rw['rltdId'] .'">'. $rw['title'] .'</a>') : $rw['title'],

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
