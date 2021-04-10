<?php
/*
* @author Ghasem Babaie
* @since 2013-02-02
* @name Attachments Module option.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

isset( $files) or $file = new File( Module::$name);
defined( 'DEBUG_MODE') and !isset( $adbl) and printr( 'Error! The Addable object is not Set. [ $adbl ]');

while( $aCols = $adbl -> getRow())
{
	//<!-- Delete The File
		
		if( isset( $aCols['del_attchmnt']))
		{
			DB::delete( array(
					'tableName' => Module::$name . '_attachments',
					'where'	=> array(
						'id' => intval( $aCols['id']),
					),
				)
				,Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
			);
			unset( $aCols['del_attchmnt']);

			$file -> delete( $aCols['id'], 0, 'attchmnt.');
			$aCols['id'] = 0;

		}//End of if( isset( $aCols['del_attchmnt']));
		
	//-->
	
	$files = $adbl -> getFiles();

	if( defined( 'DEBUG_MODE') && @$files[ 'attchmnt'][ 'error'] && @$files[ 'attchmnt'][ 'name'])
	{
		printr( 'Error while uplodaing');
		printr( $files[ 'attchmnt']);

	}//End of if( defined( 'DEBUG_MODE'));
	
	if( empty( $files[ 'attchmnt'][ 'name'])) continue;

	$aCols['id'] = intval( $aCols['id']);
	$aCols['fileName'] = $files[ 'attchmnt'][ 'name'];
	$aCols['size'] = $files[ 'attchmnt'][ 'size'];
	isset( $aCols['itemId']) or $aCols['itemId'] = $rltdId;

	if( $aCols['id'])
	{
		DB::update( array(
				'tableName' => Module::$name . '_attachments',
				'cols' 	=> & $aCols,
				'where'	=> array(
					'id' => $aCols[ 'id'],
				),
			)
			,Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
		);
	
	}else{
	
		DB::insert( array(
				'tableName' => Module::$name . '_attachments',
				'cols' => & $aCols,
			)
			, Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
		);
		$aCols['id'] = DB::insrtdId();

	}//End of if( $aCols['id'])
	
	$file -> save( $aCols['id'], $files[ 'attchmnt'][ 'tmp_name'], 'attchmnt.');

}//End of while( $aCols = $adbl -> getRow());

unset( $aCols);
?>
