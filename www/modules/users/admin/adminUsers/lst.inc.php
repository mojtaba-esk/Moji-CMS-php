<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. List The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	if( isset( $grInf))//Only Admins can see this page...
	{
		msgDie( Lang::getVal( 'accessDenied'), NULL, 0, 'error');
		return;
	}

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
	
	//<!-- Search 

		$srchSQL = include( 'srch.inc.php');

	//End of Search-->

	$sortSQL = isset( $_GET['sort']) ? "ORDER BY `{$_GET['sort']}` {$_GET['srtType']} " : '';

	$SQL = '
		SELECT
			`u`.`id`,
			`u`.`username`,
			`u`.`active`,
			`u`.`firstName`,
			`u`.`lastName`,
			`g`.`title`	AS `groupTitle`
		FROM
			`admin_users_main`		AS `u`,
			`admin_users_groups`	AS `g`
		WHERE
				`u`.`id` != '. Session::$userId .'
			AND
				`u`.`groupId` = `g`.`id`
			AND
				'. $srchSQL .' '. $sortSQL;
		
	$pging = new Paging( array(
				'SQL' 		=> $SQL,
				'perPage'	=> Module::$opt['admnLstLmt'],
				'cachePrfx'	=> Module::$name .'_'. $_GET['sub'],
				//'excldVars'	=> array( 'vLng', 'rws[0][1][cat]'),
			)
		);
	
	$SQL = $pging -> getSQL();
	$rws = DB::load( $SQL, Module::$name .'_'. $_GET['sub']);
	
	//printr( $SQL);
	
	$tpl -> assign_vars( array(

		'L_SEARCH' => Lang::getVal( 'search'),
		'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),

		'L_TOOLS'	=> Lang::getVal( 'tools'),
		'L_TITLE'	=> rwTitle( 'lastName', 'title'),
		'L_USERNAME'=> rwTitle( 'username'),
		'L_VIEW'	=> Lang::getVal( 'view'),
		
		'L_GROUP'	=> rwTitle( 'groupId', 'group'),
		'L_ACTIVE'	=> rwTitle( 'active', 'activeStatus'),
		
		'L_DAILY_REPORT' => Lang::getVal( 'dailyReport'),
		
		'L_NEW'	=> Lang::getVal( 'new'),
		'NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
		'LANGUAGE_SELECTOR' => '',//lngSelctr(),
		'DATA_EXIST'	=> sizeof( $rws),

		'L_DELETE'			=> Lang::getVal( 'delete'),
		'L_EDIT'				=> Lang::getVal( 'edit'),
		'L_DELETE_ALL_LANGS'	=> Lang::getVal( 'deleteFromAllLangs'),
		'ARE_YOU_SURE_DELETE_SELECTED'	=> Lang::getVal( 'areYouSureToDeleteSelected'),
		'ARE_YOU_SURE_DELETE_THIS'		=> Lang::getVal( 'areYouSureToDeleteThis'),

		'SEARCH_DISPLAY'	=> isset( $_GET['srch']) ? 'block' : 'none',
		
		'SUB_NAME'	=> Lang::getVal( $_GET['sub']),
		
		'DEL_URL'		=> URL::get(),
		'EDIT_URL'	=> URL::get( array( 'mod', 'chk')),

		)
	);

	if( is_array( $rws))
	{
		$today['Y'] = Date::get( 'Y', time());
		$today['m'] = Date::get( 'm', time());
		$today['d'] = Date::get( 'd', time());

		foreach( $rws as $key => $rw)
		{
			$dailyReport = '<a href="?md=reports&mod=lstFull&srh[0][0][consultorId]='. $rw['id'] .'&srh[0][1][reportDateBgn][Y]='. $today['Y'] .'&srh[0][1][reportDateBgn][M]='. $today['m'] .'&srh[0][1][reportDateBgn][d]='. $today['d'] .'&srh[0][1][reportDateEnd][Y]='. $today['Y'] .'&srh[0][1][reportDateEnd][M]='. $today['m'] .'&srh[0][1][reportDateEnd][d]='. $today['d'] .'&srch=1">';
			$dailyReport .= Lang::getVal( 'dailyReport') .'</a>';
			
			$tpl -> assign_block_vars( 'myblck',  array(

				'RW' => Lang::numFrm( $pging -> prms['strt'] + $key + 1),
				'RWID' => $key,
				'RW_ODD' => $key & 1,
				'ID' => $rw['id'],
				
				'TITLE'		=> $rw['firstName'] .' '. $rw['lastName'],
				'USERNAME'	=> $rw['username'],
				
				'GROUP_TITLE'	=> $rw['groupTitle'],
				'ACTIVE'		=> $rw['active'] ? Lang::getVal( 'enable') : Lang::getVal( 'disable'),
				
				'DAILY_REPORT_URL' => $dailyReport,

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
