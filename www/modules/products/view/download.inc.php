<?php
/*
* @author Mojtaba Eskandari
* @since 2009-08-20
* @name Downlaod Module option.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	//<!-- Load the informations
	
		$atchRws = DB::load(
			array( 
				'tableName' => Module::$name . '_attachments',
				'where' => array(
					'id'	=> intval( $_GET['id']),
					'domId'	=> $_cfg['domain']['id'],
				),
			),
			Module::$name . $_cfg['domain']['id'] .'_attachments'
		);
		
	//End of Load the informations-->
	
	if( !$atchRws)
	{
		notFound();
	}
	$atchRw = $atchRws[0];
	
	//<!-- Inc Download Hit...
	
		$SQL = '
			UPDATE
				`'. Module::$name . '_attachments`
			SET
				`hits` = `hits` + 1
			WHERE
				`id` = '. intval( $_GET['id']) .'
		';
		DB::exec( $SQL);
		Cache::clean( Module::$name . $_cfg['domain']['id'] .'_attachments');

	//-->
	
	
	//<!-- Send Download Header
	
		lib( array( 'File'));
		$file = new File( Module::$name);

		@ob_clean();
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header( 'Content-Description: File Transfer');
		header( 'Content-Type: application/octet-stream');
		header( 'Content-Length: ' . $atchRw[ 'size']);
		header( 'Content-Disposition: attachment; filename="' . $atchRw[ 'fileName'] .'"');
		header( 'Content-Transfer-Encoding: binary');
		readfile( $file -> getPth( $atchRw[ 'id'], 0, 'attchmnt.')); 

		exit();
	
	//-->
?>
