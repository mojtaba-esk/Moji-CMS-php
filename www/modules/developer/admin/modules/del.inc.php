<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Delete The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( !is_array( $_REQUEST['chk'])) return;
	
	$mdId = $_REQUEST['chk'][0];
	if( empty( $mdId)) return;
	
	//<!-- Finding the module's name...
	
		$rws = DB::load(
			array( 
				'tableName' => $_GET['sub'],
				'where' => array(
					'id' => $mdId,
				),
			)
		);
		
		$mdName = $rws[0]['name'];
		unset( $rws);
		
	//-->
	
	//<!-- Removing from admin menu...

		DB::delete( array(
				'tableName' => 'admin_menu',
				'where'	=> array(
					'mdId' => $mdId,
				),
			)
		);
		
		Cache::clean( 'admin_menu' /* Cache Prefix*/, '');
	
	//-->
	
	//<!-- Removing from Template...
	
		$SQL = "DELETE FROM
					`templates_contents`
				WHERE `name` LIKE '%{$mdName}.%'";
		DB::exec( $SQL);
		
		Cache::clean( 'tpl' /* Cache Prefix*/, '');
	
	//-->
	
	//<!-- Removing associated tables...
	
		$SQL = "SHOW TABLE STATUS WHERE `Name` LIKE '{$mdName}_%'";
		$rws = DB::load( $SQL);
		
		foreach( $rws as $rw)
		{
			$SQL = "DROP TABLE `{$rw['Name']}`";
			DB::exec( $SQL);
		}
	
	//-->

	Cache::clean( $mdName /* Cache Prefix*/, '');
	
	DB::delete( array(
			'tableName' => $_GET['sub'],
			'where'	=> array(
				'id' => & $_REQUEST['chk'],
			),
		),
		$_GET['sub']
	);
	
	//Cache::clean( $_GET['sub'] /* Cache Prefix*/, '');

	msgDie( Lang::getVal( 'deleted'), URL::get( array( 'pg', 'chk[]', 'del')), 1);

?>
