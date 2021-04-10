<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. List The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	lib( array( 'ExcelWriter'));

	$path = dirname( __FILE__) .'/../files/';
	$fileName = 'Customers_'. @$_SESSION['pId'] .'.xls';
	
	$xsl = new ExcelWriter( $path . $fileName);
	
	if( !$xsl)
	{
		defined( 'DEBUG_MODE') and printr( $xsl -> error);
		msgDie( $xsl -> error, NULL, 0, 'error');
	}

	$rws = DB::load( $SQL, Module::$name . '_main');
	
	//$unicodeStringForExcel = chr(255).chr(254).mb_convert_encoding( $utf8_str, 'UTF-16LE', 'UTF-8');

	$xsl -> writeLine( array( Lang::getVal( 'name'), Lang::getVal( 'coTitle'), Lang::getVal( 'lockSerial'), Lang::getVal( 'mobile')));
	
	foreach( $rws as $key => $rw)
	{
		$xsl -> writeLine( array( $rw['firstName'] .' '. $rw['lastName'], & $rw['coTitle'], & $rw['lockSerial'], & $rw['mobile']));
		
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
