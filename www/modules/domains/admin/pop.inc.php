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
	
	//<!-- Preaper Popup Input Object for parameters sent from parent page...
	
		$lngs = Lang::getAll();
		//require( $_cfg['path'] .'/inc/lib/Input.class.inc.php');
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$lngsIds[] = $lngs[ $i ][ 'id' ];
			$lngsTitle[ $lngs[ $i ][ 'id' ] ] = $lngs[ $i ][ 'title'];
		}
		$popInpt = new Input( $lngsIds, 'pop' /* Prefix */);

		$getPrms = $popInpt -> getRow();
//		printr( $getPrms);
		
	//End of Preaper Input Object -->	

	$tpl -> set_filenames( array(
		'header'=> 'admin.popup.header',
		'body'	=> Module::$name .'.admin.popup',
		)
	);
	$tpl -> display( 'header');
	
	//<!-- Search 
	
		$srchSQL = include( 'srch.inc.php');
		
		$_GET['pmd'] == Module::$name and $srchSQL .= ' AND `rltdId` != 0'. $getPrms['rltdId'];
	
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
	
	$_GET['pmd'] == Module::$name	and $targetId = & $_GET['ids']['parkedOn'][0];
	$_GET['pmd'] == 'templates' 	and $targetId = & $_GET['ids']['ownerId'][0];
	
	$tpl -> assign_vars( array(

		'L_SEARCH'			=> Lang::getVal( 'search'),
		'L_CANCEL_SEARCH'	=> Lang::getVal( 'cancelSearch'),
		'SRCH_CANCEL_URL'	=> URL::get( array( 'srch', 'srh')),
		'ACTION_TITLE'		=> Lang::getVal( 'select'),
		
		'PARENT_EID'		=> $targetId,
		'PARENT_EID_TITLE'	=> & $_GET['prntElmId'],
		

		//'L_TOOLS'	=> Lang::getVal( 'tools'),
		'L_TITLE'	=> rwTitle( 'title'),
		//'L_BRIEF'	=> rwTitle( 'body', 'brief'),
		//'L_NEW'		=> Lang::getVal( 'new'),
		//'L_PUBLISH_TIME' => rwTitle( 'pblishTime'),

		'LANGUAGE_SELECTOR'	=> lngSelctr(),
		'NOT_EXIST_MESSAGE'	=> Lang::getVal( 'noDataExist'),
		'DATA_EXIST'		=> sizeof( $rws),
		
		'L_CLEAR'			=> Lang::getVal( 'clear'),

		//'L_DELETE'				=> Lang::getVal( 'delete'),
		//'L_EDIT'				=> Lang::getVal( 'edit'),
		//'L_DELETE_ALL_LANGS'	=> Lang::getVal( 'deleteFromAllLangs'),
		//'ARE_YOU_SURE_DELETE_SELECTED'	=> Lang::getVal( 'areYouSureToDeleteSelected'),
		//'ARE_YOU_SURE_DELETE_THIS'		=> Lang::getVal( 'areYouSureToDeleteThis'),

		'SEARCH_DISPLAY'	=> isset( $_GET['srch']) ? 'block' : 'none',
		
		//'DEL_URL'	=> URL::get(),
		//'EDIT_URL'	=> URL::get( array( 'mod', 'chk')),

		//'DELETE_CONFIRM'	=> JS::call( 'confrm', array( JS::str( Lang::getVal( 'areYouSureToDeleteSelected')))),
		//'SELECT_ALL_CHKS'	=> JS::call( 'selectAllChks', array( 'this')),
		)
	);
	
	//<!-- Prepare Image File 
/*
		lib( array( 'File', 'Img'));

		$file = new File( Module::$name);
		Img::setPrfx( Module::$name);
/**/
	//End of Prepare Image File -->

	if( is_array( $rws))
	{
		$_GET['pmd'] == Module::$name	and $prmDmnId = $getPrms['parkedOn'];
		$_GET['pmd'] == 'templates' 	and $prmDmnId = $getPrms['ownerId'];
		
		foreach( $rws as $key => $rw)
		{
			$tpl -> assign_block_vars( 'myblck',  array(

				'RW' => Lang::numFrm( $pging -> prms['strt'] + $key + 1),
				'RWID' => $key,
				'RW_ODD' => $key & 1,
				'ID' => $rw['rltdId'],
				
				'CLASS'		=> $rw['rltdId'] == $prmDmnId ? 'popMarkd' : '',
				'TITLE'		=> $rw['name'],
				//'IMG_SRC'	=> '../'. Img::get( $file -> getPth( $rw['rltdId'], 0, 'img.'), array( 'h' => 80, 'w' => 120)),

				//'PUBLISH_TIME' => Lang::numFrm( Date::get( 'D d M Y', $rw[ 'pblishTime'])),
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
