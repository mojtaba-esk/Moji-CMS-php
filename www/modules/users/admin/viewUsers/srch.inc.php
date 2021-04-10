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
	
	@Module::$opt['showId'] and $sFrm .= $inpt -> text( 'fileCode', 0, array( 'dir' => 'ltr', 'align' => 'left', 'size' => 20));
	$sFrm .= $inpt -> text( 'name', 0, array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'size' => 40));
	$sFrm .= $inpt -> text( 'username', 0, array( 'dir' => 'ltr', 'align' => 'ltr', 'size' => 40));
	$sFrm .= $inpt -> hiddenRqst( array( 'vLng', 'md', 'lng', 'sub'));

	//<!-- Groups Drop Down List...
	
		$SQL = 'SELECT `id`, `title` FROM `view_users_groups`';
		$rws = DB::load( $SQL);

		$itms = array( 0 => '');
		$rws or $rws = array();
		foreach( $rws as $rw)
		{
			$itms[ $rw[ 'id']] = $rw[ 'title'];
		}

		$sFrm .= $inpt -> dropDown( 'groupId', 0, array( 
											'items'	=> $itms,
											'dir'	=> Lang::$info['dir'],
											'align'	=> Lang::$info['align']
									)
							);

	//End of Groups Drop Down List.-->
	
	$sFrm .= $inpt -> chkBx( 'active', 0, array( 'value' => 1));
	
	$tpl -> assign_vars( array(

			'SEARCH_FORM_ELEMENTS' => & $sFrm
		)
	);
	
	if( !isset( $_GET['srch'])) return '1';

	$srchSQL = '1';
	$cols = $inpt -> getRow();
	isset( $srch) or $srch = new Search( Module::$opt[ 'id']);

	empty( $cols['fileCode'])	or	$srchSQL .= ' AND `u`.`id` = '. intval( $cols['fileCode']);
	empty( $cols['name'])		or	$srchSQL .= ' AND `u`.`id` IN ( '. $srch -> getIdsSQL( $cols['name'], 0) .')';
	//empty( $cols['name'])		or	$srchSQL .= " AND ( `firstName` LIKE '%{$cols['name']}%' OR `lastName` LIKE '%{$cols['name']}%')";
	empty( $cols['username'])	or	$srchSQL .= " AND `u`.`username` = '{$cols['username']}'";
	empty( $cols['groupId'])	or	$srchSQL .= " AND `u`.`groupId` = '{$cols['groupId']}'";
	empty( $cols['active'])		or	$srchSQL .= " AND `u`.`active` = '{$cols['active']}'";

	return $srchSQL;
?>
