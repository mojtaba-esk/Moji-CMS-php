<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Enable or Disable The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	$fieldName = 'enableActv'; // This field is the enable field name, in table.
	
	if( empty( $_REQUEST['chk']))
	{
		msgDie( Lang::getVal( 'plzSelect'), URL::get( array( 'pg', 'chk[]')), 1, 'warning');
		return;
	}
	
	$newStatus = isset( $_REQUEST[ 'enable']) ? 1 : 0;
	
	//<!-- Permission Check...
	
		$permSQL = '';
		if( !empty( Module::$opt['permission'][ 'ownDataOnly' ]))
		{
			$permSQL .= ' AND `userId` = 0'. Session::$userId;
		}

	//-->	
	
	//<!-- Prepare and Exec Query...

		$SQL = 'UPDATE `'. Module::$name . '_main`
				SET
					`'. $fieldName .'` = '. $newStatus .'
				WHERE
					`rltdId` IN ( 0'. implode( ', ', $_REQUEST['chk']) .')'
					. $permSQL;

		DB::exec( $SQL);
		Cache::clean( Module::$name);
		
	//-->

	$actionTitle = $newStatus ?  'enable' : 'disable';
	sLog( array(
			'itemId'	=> current( $_REQUEST['chk']),
			'desc'		=> Lang::getVal( $actionTitle) .': '. implode( ', ', $_REQUEST['chk']),
			'action'	=> 'edt',
		)
	);

	msgDie( Lang::getVal( $actionTitle .'Done'), URL::get( array( 'pg', 'chk[]')), 1);
	return;

?>
