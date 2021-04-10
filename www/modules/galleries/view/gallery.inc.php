<?php
/*
* @author Mojtaba Eskandari
* @since 2009-08-20
* @name Gallery Module option.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	//<!-- Load the informations
	
		$SQL = '
			SELECT
				`title`,
				`rltdId`
			FROM
				`'. Module::$name . '_gallery`
			WHERE 
				`lngId` = '. Lang::viewId() .' AND 
				`itemId` = '. intval( $_GET[ 'id']) .'
			ORDER BY `ordrId` ASC, `id` ASC';

		$gallRws = DB::load( $SQL, Module::$name . '_gallery');

	//End of Load the informations-->
	
	is_array( $gallRws) or $gallRws = array();
	foreach( $gallRws as $key => $gallRw)
	{
		$tpl -> assign_block_vars( 'gallblck',  array( 
				'TITLE'	=> $gallRw[ 'title'],
				'ALT'		=> '',

				//'THUMB_SRC' => $key > 18 /*Show only 20 thumbnails*/ ? '' : $_cfg['URL'] . Img::get( $file -> getPth( $gallRw[ 'rltdId'], 0, 'img.gallery.'), array( 'h' => 80, 'w' => 80)),
				'THUMB_SRC' => $_cfg['URL'] . Img::get( $file -> getPth( $gallRw[ 'rltdId'], 0, 'img.gallery.'), array( 'h' => 80, 'w' => 80)),
				'IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $gallRw[ 'rltdId'], 0, 'img.gallery.')),
			)
		);

	}//End of foreach( $gallRws as $gallRw);

?>
