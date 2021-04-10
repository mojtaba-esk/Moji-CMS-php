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

	//<!-- saveOrder
		
		if( isset( $_REQUEST[ 'saveOrder']))
		{
			include( 'saveOrder.inc.php');
			return;

		}//End of if( isset( $_REQUEST[ 'saveOrder']));

	//End of saveOrder-->

	$tpl -> set_filenames( array(
		'body' => $_GET['sub'] .'.sub.admin.list',
		)
	);
	
	//<!-- Search 

		//$srchSQL = include( 'srch.inc.php');

	//End of Search-->

	$sortSQL = isset( $_GET['sort']) ? "ORDER BY `{$_GET['sort']}` {$_GET['srtType']} " : '';
	$_GET['parentId'] = intval( @$_GET['parentId']);

	$SQL = '
		SELECT * 
		FROM
			`admin_menu` 
		WHERE
			`parentId` = '. $_GET['parentId'] .'
		ORDER BY `orderId` ASC';
		
	$pging = new Paging( array(
				'SQL' 		=> $SQL,
				'perPage'	=> 20,
				//'cachePrfx'	=> Module::$name .'_'. $_GET['sub'],
				//'excldVars'	=> array( 'vLng', 'rws[0][1][cat]'),
			)
		);
	
	$SQL = $pging -> getSQL();
	$rws = DB::load( $SQL);
	$rws[] = array(
		'id'	=> -1,
		'title'	=> 'developer',
	);
	
	$parentName = NULL;
	if( $_GET['parentId'])
	{
		$rwP = DB::load(
			array(
				'tableName' => 'admin_menu',
				'cols' 	=> array( 'title'),
				'where'	=> array(
					'id' => $_GET['parentId'],
				),
			),
			0,
			1
		);

		$parentName = Lang::getVal( $rwP[0]);

	}//End of if( $_GET['parentId']);
	
	//printr( $SQL);
	
	$tpl -> assign_vars( array(

		'L_SEARCH' => Lang::getVal( 'search'),
		'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),
		'L_SAVE_ORDER'	=> Lang::getVal( 'saveOrder'),

		'L_TOOLS'	=> Lang::getVal( 'tools'),
		'L_NAME'	=> Lang::getVal( 'name'),
		'L_TITLE'	=> Lang::getVal( 'title'),
		
		'PARENT_NAME'=> $parentName,
		
		'L_NEW'	=> Lang::getVal( 'new'),
		'NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
		//'LANGUAGE_SELECTOR' => lngSelctr(),
		'DATA_EXIST'	=> sizeof( $rws),

		'L_DELETE'				=> Lang::getVal( 'delete'),
		'L_EDIT'				=> Lang::getVal( 'edit'),
		'L_CHILDS'				=> Lang::getVal( 'childs'),
		'L_DELETE_ALL_LANGS'	=> Lang::getVal( 'deleteFromAllLangs'),
		'ARE_YOU_SURE_DELETE_SELECTED'	=> Lang::getVal( 'areYouSureToDeleteSelected'),
		'ARE_YOU_SURE_DELETE_THIS'		=> Lang::getVal( 'areYouSureToDeleteThis'),

		'SUB_NAME'	=> '<a href="?md='. Module::$name .'&sub='. $_GET['sub'] .'">'. Lang::getVal( $_GET['sub']) .'</a>',
		
		'DEL_URL'	=> URL::get(),
		'EDIT_URL'	=> URL::get( array( 'mod', 'chk')),

		'ARE_YOU_SURE_DELETE_SELECTED' => Lang::getVal( 'areYouSureToDeleteSelected'),
		'ARE_YOU_SURE_DELETE_THIS' => Lang::getVal( 'areYouSureToDeleteThis'),

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
				
				'NAME'	=> $rw['title'],
				'TITLE' => Lang::getVal( $rw['title']),
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
