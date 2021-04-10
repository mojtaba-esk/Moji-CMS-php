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

		}//End of if( isset( $_REQUEST[ 'del']) || isset( $_REQUEST[ 'delAllLngs']));

	//End of Delete The Records-->


	$tpl -> set_filenames( array(
		'body' => Module::$name .'.admin.list',
		)
	);
	
	//<!-- Search 
	
		$srchSQL = include( 'srch.inc.php');
	
	//End of Search-->

	$sortSQL = isset( $_GET['sort']) ? "`{$_GET['sort']}` {$_GET['srtType']}, " : '';

	$SQL = 'SELECT * FROM `'. Module::$name . '_main` 
		WHERE `lngId` = '. Lang::viewId().' AND '. $srchSQL .'
		ORDER BY '. $sortSQL .' `'. Module::$opt['admnOrdrBy'] .'` '. Module::$opt['admnOrdrType'];
		
	$pging = new Paging( array(
				'SQL' 		=> $SQL,
				'perPage'	=> Module::$opt['admnLstLmt'],
				'cachePrfx'	=> Module::$name . '_main',
				//'excldVars'	=> array( 'vLng', 'rws[0][1][cat]'),
			)
		);
	
	$SQL = $pging -> getSQL();
	$rws = DB::load( $SQL, Module::$name . '_main');
	
	//printr( $SQL);
	
	$tpl -> assign_vars( array(

		'L_SEARCH' => Lang::getVal( 'search'),
		'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),

		'L_TOOLS'	=> Lang::getVal( 'tools'),
		'L_TITLE'	=> rwTitle( 'title'),
		'L_BRIEF'	=> rwTitle( 'name'),
		'L_NEW'		=> Lang::getVal( 'new'),
		'L_PUBLISH_TIME' => rwTitle( 'insrtTime'),
		
		'L_PLAN'	=> Lang::getVal( 'planId'),
		'L_STATUS'	=> Lang::getVal( 'statusId'),

		'LANGUAGE_SELECTOR'	=> lngSelctr(),
		'NOT_EXIST_MESSAGE'	=> Lang::getVal( 'noDataExist'),
		'DATA_EXIST'		=> sizeof( $rws),

		'L_DELETE'				=> Lang::getVal( 'delete'),
		'L_EDIT'				=> Lang::getVal( 'edit'),
		'L_DELETE_ALL_LANGS'	=> Lang::getVal( 'deleteFromAllLangs'),
		'ARE_YOU_SURE_DELETE_SELECTED'	=> Lang::getVal( 'areYouSureToDeleteSelected'),
		'ARE_YOU_SURE_DELETE_THIS'		=> Lang::getVal( 'areYouSureToDeleteThis'),

		'SEARCH_DISPLAY'	=> isset( $_GET['srch']) ? 'block' : 'none',

		'DEL_URL'	=> URL::get(),
		'EDIT_URL'	=> URL::get( array( 'mod', 'chk')),
		'SRCH_CANCEL_URL'	=> '?md='. Module::$name,

		'ACTION_TITLE'	=> Lang::getVal( $_GET['mod']) .' ('. Lang::numFrm( $pging -> total()) .' '. Lang::getVal( 'results') .')',

		)
	);

	if( is_array( $rws))
	{
		isset( $_GET['srch']) and hgLightSrch( $rws); // Highlight the searched keywords

		isset( $statusLst)	or $statusLst = getDomStatus( Lang::viewId());
		isset( $planLst)	or $planLst = getDomPlans( Lang::viewId());
		foreach( $rws as $key => $rw)
		{
			$tpl -> assign_block_vars( 'myblck',  array(

				'RW'	=> Lang::numFrm( $pging -> prms['strt'] + $key + 1),
				'RWID'	=> $key,
				'RW_ODD'=> $key & 1,
				'ID'	=> $rw['rltdId'],

				'TITLE' => $rw['title'],
				'BRIEF' => '<a href="http://www.'. strip_tags( $rw['name']) .'" target="_blank">'. $rw['name'] .'</a>', // strip_tags is used to avoid highlight effects
				'PLAN'	=> & $planLst[ $rw['planId'] ],
				'STATUS' => & $statusLst[ $rw['statusId'] ],
				'PUBLISH_TIME' => Lang::numFrm( Date::get( 'D d M Y - G:i', $rw[ 'insrtTime'])),

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
