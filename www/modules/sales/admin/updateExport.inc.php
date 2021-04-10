<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. List The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	lib( array( 'ExcelWriter'));

	$path = dirname( __FILE__) .'/../files/';
	$fileName = 'Customers_Update_Log_'. @$_SESSION['pId'] .'.xls';
	
	$xsl = new ExcelWriter( $path . $fileName);
	
	if( !$xsl)
	{
		defined( 'DEBUG_MODE') and printr( $xsl -> error);
		msgDie( $xsl -> error, NULL, 0, 'error');
	}

	$rws = DB::load( $SQL, Module::$name . '_main');
	
	$listOfCuIds = '';
	foreach( $rws as $key => $rw)
	{
		$listOfCuIds .= $rw['rltdId'] .',';
		
	}//End of foreach( $rws as $key => $rw);
	$listOfCuIds .= '0';
	
	//<!-- Permission Check...
	
		$permSQL = '';
		if( !empty( Module::$opt['permission'][ 'ownDataOnly' ]))
		{
			$permSQL .= ' AND `m`.`userId` = 0'. Session::$userId;
		}

	//-->
	
	$SQL = 'SELECT
				`m`.`firstName`,
				`m`.`lastName`,
				`m`.`lockSerial`,
				`l`.*,
				`v`.`title`	AS	`version`
			FROM
				`'. Module::$name . '_main`			AS	`m`,
				`'. Module::$name .'_update_logs`	AS	`l`,
				`products_versions`					AS	`v`

			WHERE
				`l`.`cuId`	IN ( '. $listOfCuIds .')
				AND
					`m`.`rltdId` = `l`.`cuId`
				AND
					`l`.`verId`	= `v`.`id`
				'. $permSQL .'
			ORDER BY
				`l`.`id` ASC';
				
	$rws = DB::load( $SQL);
	
	//printr( $rws);exit();
	
	$xsl -> writeLine( array( Lang::getVal( 'name'), Lang::getVal( 'lockSerial'), Lang::getVal( 'version'), Lang::getVal( 'updateTime'), Lang::getVal( 'ip')));
	
	foreach( $rws as $key => $rw)
	{
		$xsl -> writeLine( array( $rw['firstName'] .' '. $rw['lastName'], & $rw['lockSerial'], & $rw['version'], Lang::numFrm( Date::get( 'D d M Y G:i', $rw['updateTime'])), & $rw['ip']));
		
	}//End of foreach( $rws as $key => $rw);
	
	$xsl -> close();
	
	//<!-- Send Download Header

		@ob_clean();
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header( 'Content-Description: File Transfer');
		header( 'Content-Type: application/octet-stream');
		header( 'Content-Length: ' . filesize( $path . $fileName));
		header( 'Content-Disposition: attachment; filename="' . $fileName .'"');
		header( 'Content-Transfer-Encoding: binary');
		
		readfile( $path . $fileName);
		unlink( $path . $fileName);
		
		exit();

	//-->	
?>
