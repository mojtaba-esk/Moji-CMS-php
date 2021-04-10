<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Delete The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( empty( $_REQUEST['orderIds'])) return;
	
	$ordrLst = explode( ',', $_REQUEST['orderIds']);
	$pgNum = isset( $_REQUEST['pg']) ? intval( $_REQUEST['pg']) : 1;

	foreach( $ordrLst as $ordrId => $id)
	{
		DB::update( array(
				'tableName' => Module::$name . '_'. $_GET['sub'],
				'cols' 	=> array( 'ordrId' => $pgNum * $ordrId),
				'where'	=> array(
					'rltdId'	=> intval( $id),
					'domId'		=> $_cfg['domain']['id'],
				),
			)
			, Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
		);

	}//End of foreach( $ordrLst as $ordrId => $id);

	msgDie( Lang::getVal( 'orderSaved'), URL::get(), 1);

?>
