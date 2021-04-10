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
		'body' => Module::$name .'.'. $_GET['mod'] .'.admin.list',
		)
	);
	
	//<!-- Search 
	
		$srchSQL = ' AND 1';//include( 'srch.inc.php');
		//$srchSQL .= ' AND `productId` = '. $_SESSION['pId'];
		
		//<!-- Permission Check...
		
			//if( !empty( Module::$opt['permission'][ 'ownDataOnly' ]))
			//{
				//$srchSQL .= ' AND `userId` = 0'. Session::$userId;
			//}
			
		//-->
	
	//End of Search-->

	$sortSQL = isset( $_GET['sort']) ? "`{$_GET['sort']}` {$_GET['srtType']}, " : '';

	$_GET['cuId'] = intval( $_GET['cuId']);
	$SQL = 'SELECT * FROM `'. Module::$name . '_logs` 
		WHERE `cuId` = '. $_GET['cuId'] . $srchSQL .'
		ORDER BY '. $sortSQL .' `'. Module::$opt['admnOrdrBy'] .'` '. Module::$opt['admnOrdrType'];
		
	$pging = new Paging( array(
				'SQL' 		=> $SQL,
				'perPage'	=> Module::$opt['admnLstLmt'],
				'cachePrfx'	=> Module::$name . '_logs',
				//'excldVars'	=> array( 'vLng', 'rws[0][1][cat]'),
			)
		);
	
	$SQL = $pging -> getSQL();
	$rws = DB::load( $SQL, Module::$name . '_logs');
	
	//printr( $SQL);
	
	$cuRws = DB::load(
		array( 
			'tableName' => Module::$name . '_main',
			'where' => array(
				'rltdId' => & $_GET['cuId'],
			),
			'cols' => array( 'firstName', 'lastName')
		), true
	);
	$cuRws = & $cuRws[0];
	$cuLnk = '<a href="?md='. Module::$name .'&mod=view&id='. $_GET['cuId'] .'">'. $cuRws['firstName'] .' '. $cuRws['lastName'] .'</a>';
	
	$tpl -> assign_vars( array(

		'L_SEARCH' => Lang::getVal( 'search'),
		'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),

		'L_IP'	=> Lang::getVal( 'ip'),
		'L_PUBLISH_TIME' => rwTitle( 'insrtTime'),

		'NOT_EXIST_MESSAGE'	=> Lang::getVal( 'noDataExist'),
		'DATA_EXIST'		=> sizeof( $rws),

		'SEARCH_DISPLAY'	=> isset( $_GET['srch']) ? 'block' : 'none',
		'RETURN_URL'	=> '?md='. Module::$name,
		
		'CU_NAME'	=>	& $cuLnk,
		
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

				'IP' => $rw['ip'],
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
