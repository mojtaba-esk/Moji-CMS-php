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
		'body' => $_GET['sub'] .'.sub.admin.list',
		)
	);
	
	//<!-- Search 

		$srchSQL = include( 'srch.inc.php');
		//$srchSQL .= ' AND `domId` = '. $_cfg['domain']['id'];

	//End of Search-->

	$sortSQL = isset( $_GET['sort']) ? "ORDER BY `{$_GET['sort']}` {$_GET['srtType']} " : '';

/*	$SQL = 'SELECT * FROM `' . Module::$name . '_'. $_GET['sub'] .'` AS TRF 
		INNER JOIN `' . Module::$name . '_positions` AS POS ON TRF.positionId=POS.id
		WHERE '. $srchSQL .' '. $sortSQL;
		
		
		SELECT * FROM `ads_tariffs` AS `trf`
		INNER JOIN ( SELECT `posIn`.`id` AS `posInId`,`posIn`.`key`,`m`.`name`  FROM `ads_positions` AS posIn  INNER JOIN `modules` AS `m` ON `posIn`.`mdId`=`m`.`id`) AS `pos` ON `trf`.`positionId`=`pos`.`posInId`
		WHERE 1 
		
		/**/
	$SQL = 'SELECT * FROM `' . Module::$name . '_'. $_GET['sub'] .'` AS `trf`  
		INNER JOIN ( SELECT `posIn`.`id` AS `posInId`,`posIn`.`key`,`m`.`name`  FROM `' . Module::$name . 
		'_positions` AS `posIn`  INNER JOIN `modules` AS `m` ON `posIn`.`mdId`=`m`.`id`) AS `pos` ON `trf`.`positionId`=`pos`.`posInId`
		WHERE '. $srchSQL .' '. $sortSQL;
		
		
		
		
		
		
		
printr($SQL);	
//die;
	$pging = new Paging( array(
				'SQL' 		=> $SQL,
				'perPage'	=> Module::$opt['admnLstLmt'],
				'cachePrfx'	=> Module::$name .'_'. $_GET['sub'],
				//'excldVars'	=> array( 'vLng', 'rws[0][1][cat]'),
			)
		);
	
	$SQL = $pging -> getSQL();
	$rws = DB::load( $SQL, Module::$name .'_'. $_GET['sub']);
	
printr( $rws);
	
	$tpl -> assign_vars( array(

		'L_SEARCH' => Lang::getVal( 'search'),
		'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),

		'L_TOOLS'	=> Lang::getVal( 'tools'),

		'L_TYPE'	=> rwTitle( 'type'),
		'L_POSITION'	=> rwTitle( 'position'),
		'L_MODULE'	=> rwTitle( 'module'),
		'L_WIDTH'	=> rwTitle( 'width'),
		'L_HEIGHT'  => rwTitle( 'height'),
		'L_DURATION'=> rwTitle( 'duration').'('. rwTitle( 'day').')',
		'L_PRICE'   => rwTitle( 'price'),
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
		
		'DEL_URL'	=> URL::get(),
		'EDIT_URL'	=> URL::get( array( 'mod', 'chk')),

		//'DELETE_CONFIRM' => JS::call( 'confrm', array( JS::str( Lang::getVal( 'areYouSureToDeleteSelected')))),
		//'SELECT_ALL_CHKS' 	=> JS::call( 'selectAllChks', array( 'this')),
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
				
				//'TITLE' .' '..'*'..' '..' '.Lang::getVal( 'module'). ' ' .
				'TYPE'	=> $rw['type'],
				'KEY' => $rw['key'],
				'MODULE' => Lang::getVal($rw['name']),
				'WIDTHSIZE'=> $rw['widthSize'],
				'HEIGHTSIZE'=> $rw['heightSize'],
				'DURATION' =>$rw['duration'],
				'PRICE' =>$rw['price']
				
				//$rw['widthSize']."*".$rw['heightSize'].$rw['name'],
				//'DEL_CONFIRM_WITH_TITLE' => JS::call( 'cnfrmWthTitle', array( JS::str( Lang::getVal( 'areYouSureToDeleteThis')), $rw['rltdId'])),

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
