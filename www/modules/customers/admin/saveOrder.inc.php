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
				'tableName' => Module::$name . '_main',
				'cols' 	=> array( 'orderId' => $pgNum * $ordrId),
				'where'	=> array(
					'id' => intval( $id),
				),
			)
		);

	}//End of foreach( $ordrLst as $ordrId => $id);
	
	Cache::clean( Module::$name /* Cache Prefix*/, '');

	msgDie( Lang::getVal( 'orderSaved'), URL::get(), 1);

?>
