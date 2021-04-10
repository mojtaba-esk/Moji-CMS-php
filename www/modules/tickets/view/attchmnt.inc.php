<?php
/*
* @author Mojtaba Eskandari
* @since 2009-08-20
* @name Attachments Module option.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

isset( $files) or $file = new File( Module::$name);
defined( 'DEBUG_MODE') and !isset( $adbl) and printr( 'Error! The Addable object is not Set. [ $adbl ]');

while( $aCols = $adbl -> getRow())
{
	$files = $adbl -> getFiles();

	if( defined( 'DEBUG_MODE') && @$files[ 'attchmnt'][ 'error'] && @$files[ 'attchmnt'][ 'name'])
	{
		printr( 'Error while uplodaing');
		printr( $files[ 'attchmnt']);

	}//End of if( defined( 'DEBUG_MODE'));
	
	if( empty( $files[ 'attchmnt'][ 'name'])) continue;

	$aCols['id'] = intval( $aCols['id']);
	$aCols['fileName'] = & $files[ 'attchmnt'][ 'name'];
	$aCols['size'] = & $files[ 'attchmnt'][ 'size'];
	isset( $aCols['itemId']) or $aCols['itemId'] = $rltdId;
	
	if( $aCols['size'] > Module::$opt['attchmntSize'])
	{
		msgDie( Lang::getVal( 'attchmntSizeErr', array( '{x}' => Lang::numFrm( number_format( round( Module::$opt['attchmntSize'] / ( 1024 * 1024), 2))) .' '. Lang::getVal( 'MB') )), NULL, 0, 'error');
		return;
	}

	DB::insert( array(
			'tableName' => Module::$name . '_attachments',
			'cols' => & $aCols,
		)
		, true
	);
	$aCols['id'] = DB::insrtdId();
	
	$file -> save( $aCols['id'], $files[ 'attchmnt'][ 'tmp_name'], 'attchmnt.');

}//End of while( $aCols = $adbl -> getRow());

unset( $aCols);
?>
