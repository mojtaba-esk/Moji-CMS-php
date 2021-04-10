<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. List The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	//<!-- Preaper Input Object ...

		$lngs = Lang::getAll();
		//require( $_cfg['path'] .'/inc/lib/Input.class.inc.php');
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$lngsIds[] = $lngs[ $i ][ 'id' ];
			$lngsTitle[ $lngs[ $i ][ 'id' ] ] = $lngs[ $i ][ 'title'];
		}
		$inpt = new Input( $lngsIds, 'srh');

	//End of Preaper Input Object -->
	
	$sFrm = '';	
	$sFrm .= $inpt -> text( 'name', 0, array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'size' => 40));
	$sFrm .= $inpt -> text( 'username', 0, array( 'dir' => 'ltr', 'align' => 'ltr', 'size' => 40));
	$sFrm .= $inpt -> hiddenRqst( array( 'vLng', 'md', 'lng', 'sub'));

	//<!-- Modules Drop Down List...
	
		$SQL = 'SELECT `id`, `name` FROM `modules`';
		$rws = DB::load( $SQL);

		$itms = array( 0 => '');
		$rws or $rws = array();
		foreach( $rws as $rw)
		{
			$itms[ $rw[ 'id']] = Lang::getVal( $rw[ 'name']);
		}

		$sFrm .= $inpt -> dropDown( 'mdId', 0, array( 
											'items'	=> $itms,
											'dir'	=> Lang::$info['dir'],
											'align'	=> Lang::$info['align']
									)
							);

	//End of Modules Drop Down List.-->
	
	$sFrm .= $inpt -> text( 'itemId', 0, array( 'dir' => 'ltr', 'align' => 'ltr', 'size' => 20));
	$sFrm .= $inpt -> text( 'description', 0, array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'size' => 40));

	$sFrm .= $inpt -> date( 'logTimeBgn', 0, array( 
						'elmnts' => 'Y,M,d',
						//'type' => 'jalali',
						//'value' => 'now',
						'difY'		=> array( -15, 1),
						'attribs' => array( 
							'dir'	=> Lang::$info['dir'],
							'align'	=> Lang::$info['align']
					)
				)
			);

	$sFrm .= $inpt -> date( 'logTimeEnd', 0, array( 
						'elmnts' => 'Y,M,d',
						//'type' => 'jalali',
						//'value' => 'now',
						'difY'		=> array( -15, 1),
						'attribs' => array( 
							'dir'		=> Lang::$info['dir'],
							'align'	=> Lang::$info['align']
					)
				)
			);

	
	$tpl -> assign_vars( array(

			'SEARCH_FORM_ELEMENTS' => & $sFrm
		)
	);
	
	if( !isset( $_GET['srch'])) return '1';

	$srchSQL = '1';
	$cols = $inpt -> getRow();

	$usrSrch = new Search( Module::getId( 'users'));

	empty( $cols['name'])		or	$srchSQL .= ' AND `l`.`userId` IN ( '. $usrSrch -> getIdsSQL( $cols['name'], 0) .')';
	empty( $cols['username'])	or	$srchSQL .= " AND `u`.`username` = '{$cols['username']}'";
	empty( $cols['mdId'])		or	$srchSQL .= " AND `l`.`mdId` = '{$cols['mdId']}'";
	empty( $cols['itemId'])		or	$srchSQL .= " AND `l`.`itemId` = '{$cols['itemId']}'";
	empty( $cols['description']) or	$srchSQL .= " AND `l`.`desc` LIKE '%{$cols['description']}%'";

	isset( $cols['logTimeBgn']) and $cols['logTimeBgn'] = Date::mkTime( $cols['logTimeBgn']) and $srchSQL .= ' AND `l`.`logTime` >= '. $cols['logTimeBgn'];
	if( isset( $cols['logTimeEnd']))
	{
		if( !empty( $cols['logTimeEnd']['Y']))
		{
			empty( $cols['logTimeEnd']['M']) and $cols['logTimeEnd']['M']	= 12;
			empty( $cols['logTimeEnd']['d']) and $cols['logTimeEnd']['d']	= 31;
		}

		$cols['logTimeEnd'] = Date::mkTime( $cols['logTimeEnd']) and $srchSQL .= ' AND `l`.`logTime` <= '. $cols['logTimeEnd'];

	}//End of if( isset( $cols['logTimeEnd']));

	return $srchSQL;
?>
