<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. List The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	$tpl -> set_filenames( array(
		'body' => $_GET['sub'] .'.sub.admin.list',
		)
	);
	
	//<!-- Search 

		$srchSQL = include( 'srch.inc.php');

	//End of Search-->

	$sortSQL = 'ORDER BY';
	$sortSQL .= isset( $_GET['sort']) ? " `{$_GET['sort']}` {$_GET['srtType']}, " : '';
	$sortSQL .= ' `l`.`id` DESC';

	$SQL = '
		SELECT
			`l`.*,
			CONCAT( `u`.`firstName`, \' \', `u`.`lastName`) AS `userTitle`,
			`m`.`name`	AS	`mdName`
		FROM
			`config_admin_logs`	AS	`l`,
			`admin_users_main`	AS	`u`,
			`modules`			AS	`m`
		WHERE
			`l`.`userId` = `u`.`id`
			AND
				`l`.`mdId` = `m`.`id`
			AND
				'. $srchSQL .' '. $sortSQL;

	$pging = new Paging( array(
				'SQL' 		=> $SQL,
				'perPage'	=> Module::$opt['admnLstLmt'],
				//'cachePrfx'	=> Module::$name .'_'. $_GET['sub'],
				//'excldVars'	=> array( 'vLng', 'rws[0][1][cat]'),
			)
		);
	
	$SQL = $pging -> getSQL();
	$rws = DB::load( $SQL);
	
	//printr( $SQL);
	
	$tpl -> assign_vars( array(

		'L_SEARCH' => Lang::getVal( 'search'),
		'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),

		'L_NAME'	=> Lang::getVal( 'name'),
		'L_MD_NAME'	=> rwTitle( 'mdId', 'mdName'),
		'L_ACTION'	=> rwTitle( 'action'),
		'L_TIME'	=> rwTitle( 'logTime', 'time'),
		'L_ITEM_ID'	=> rwTitle( 'itemId'),
		'L_DESC'	=> rwTitle( 'desc', 'description'),
		'L_IP'		=> rwTitle( 'ip'),
		
		'NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
		'LANGUAGE_SELECTOR' => '',//lngSelctr(),
		'DATA_EXIST'	=> sizeof( $rws),

		'SEARCH_DISPLAY'	=> isset( $_GET['srch']) ? 'block' : 'none',
		
		'SUB_NAME'	=> Lang::getVal( $_GET['sub']),
		
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
				
				'USER_TITLE'	=> $rw['userTitle'],
				'USER_ID'		=> $rw['userId'],
				
				'MD_NAME'	=> Lang::getVal( $rw['mdName']) . ( $rw['sub'] ? ' - '. Lang::getVal( $rw['sub']) : ''),
				'ACTION'	=> $rw['action'] ? Lang::getVal( $rw['action']) : '---',
				'TIME'		=> Lang::numFrm( Date::get( 'D d M Y - G:i', $rw['logTime'])),
				'ITEM_ID'	=> $rw['itemId'],
				'DESC'		=> briefHint( $rw['desc'], 80),
				'IP'		=> $rw['ip'],

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
