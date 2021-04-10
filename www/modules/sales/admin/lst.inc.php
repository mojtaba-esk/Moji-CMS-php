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
	
	//<!-- Enable or Disable The Records...
		
		if( isset( $_REQUEST[ 'enable']) || isset( $_REQUEST[ 'disable']))
		{
			include( 'enable.inc.php');
			return;

		}//End of if( isset( $_REQUEST[ 'enable']) || isset( $_REQUEST[ 'disable']));

	//End of Enable or Disable The Records-->
	
	$tpl -> set_filenames( array(
		'body' => Module::$name .'.admin.list',
		)
	);
	
	//<!-- Search 
	
		$srchSQL = include( 'srch.inc.php');
		$srchSQL .= ' AND `productId` = '. $_SESSION['pId'];
		
		//<!-- Permission Check...
		
			if( !empty( Module::$opt['permission'][ 'ownDataOnly' ]))
			{
				$srchSQL .= ' AND `userId` = 0'. Session::$userId;
			}
			
		//-->		
	
	//End of Search-->
	
	$sortSQL = isset( $_GET['sort']) ? "`{$_GET['sort']}` {$_GET['srtType']}, " : '';

	$SQL = 'SELECT * FROM `'. Module::$name . '_main` 
		WHERE `lngId` = '. Lang::viewId().' AND '. $srchSQL .'
		ORDER BY '. $sortSQL .' `'. Module::$opt['admnOrdrBy'] .'` '. Module::$opt['admnOrdrType'];
		
		
	//<!-- Excel Export for Mobile numbers
		
		if( isset( $_POST[ 'export'])) // Use this $SQL variable for exporting...
		{
			include( 'export.inc.php');
			return;

		}//End of if( isset( $_POST[ 'export']));

	//-->

	//<!-- Excel Export for Product Update logs
		
		if( isset( $_POST[ 'updateExport'])) // Use this $SQL variable for exporting...
		{
			include( 'updateExport.inc.php');
			return;

		}//End of if( isset( $_POST[ 'updateExport']));

	//-->

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

		'L_SEARCH' => Lang::getVal( 'search'),// .' ('. Lang::numFrm( $pging -> total()) .' '. Lang::getVal( 'results') .')',
		'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),

		'L_TOOLS'	=> Lang::getVal( 'tools'),
		'L_TITLE'	=> rwTitle( 'lastName', 'firstName'),
		'L_COTITLE' => rwTitle( 'coTitle'),
		'L_BRIEF'	=> Lang::getVal( 'lockSerial'),
		'L_NEW'		=> Lang::getVal( 'new'),
		'L_PUBLISH_TIME' => rwTitle( 'saleTime'),

		'LANGUAGE_SELECTOR'	=> lngSelctr(),
		'NOT_EXIST_MESSAGE'	=> Lang::getVal( 'noDataExist'),
		'DATA_EXIST'		=> sizeof( $rws),

		'L_DELETE'				=> Lang::getVal( 'delete'),
		'L_EDIT'				=> Lang::getVal( 'edit'),
		'L_VIEW'				=> Lang::getVal( 'view'),
		'L_DELETE_ALL_LANGS'	=> Lang::getVal( 'deleteFromAllLangs'),
		'ARE_YOU_SURE_DELETE_SELECTED'	=> Lang::getVal( 'areYouSureToDeleteSelected'),
		'ARE_YOU_SURE_DELETE_THIS'		=> Lang::getVal( 'areYouSureToDeleteThis'),
		
		'L_ENABLE'				=> Lang::getVal( 'enable'),
		'L_DISABLE'				=> Lang::getVal( 'disable'),
		'L_EXPORT'				=> Lang::getVal( 'xlsExport'),
		'L_UPD_EXPORT'			=> Lang::getVal( 'xlsUpdtExport'),

		'SEARCH_DISPLAY'	=> isset( $_GET['srch']) ? 'block' : 'none',
		
		'DEL_URL'	=> URL::get(),
		'EDIT_URL'	=> URL::get( array( 'mod', 'chk')),
		
		'ACTION_TITLE'	=> Lang::getVal( $_GET['mod']) .' ('. Lang::numFrm( $pging -> total()) .' '. Lang::getVal( 'results') .')',

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
				'ID' => $rw['rltdId'],

				'TITLE' => $rw['firstName'] .' '. $rw['lastName'],
				'BRIEF' => $rw['lockSerial'],
				'COTITLE' => $rw['coTitle'],

				'PUBLISH_TIME' => Lang::numFrm( Date::get( 'D d M Y', $rw[ 'saleTime'])),

				'CLASS' => $rw['enableActv'] ? '' : 'disabled',

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
