<?php
/*
* @author Ghasem Babaie
* @since 2013-02-02
* @name Attachments Module option.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	//<!-- Load the informations
	
		$atchRws = DB::load(
			array( 
				'tableName' => Module::$name . '_attachments',
				'where' => array(
					'itemId' => intval( $_GET['id']),
					//'lngId'	=> Language Id,
				),
			), Module::$name . $_cfg['domain']['id'] /* Cache Prefix*/
		);

	//End of Load the informations-->

	is_array( $atchRws) or $atchRws = array();
	foreach( $atchRws as $atchRw)
	{
		$tpl -> assign_block_vars( 'atchblck',  array( 
				'URL'		=> URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&mod=download&id='. $atchRw[ 'id']),
				'TITLE'	=> $atchRw[ 'fileName'],
				'HITS'	=> Lang::numFrm( $atchRw[ 'hits']),
				'SIZE'	=> Lang::numFrm( number_format( round( $atchRw[ 'size'] / 1024, 2))),
			)
		);

	}//End of for( $i = 0; $i != Module::$opt['attchmnt']; $i++);

	$tpl -> assign_vars( array(
			'L_ATTACHEMENTS'	=>	Lang::getVal( 'attachements'),
			'L_DOWNLOAD_HITS'	=>	Lang::getVal( 'downloadHits'),
			'L_FILE_SIZE'		=>	Lang::getVal( 'fileSize'),
			'L_KB'				=>	Lang::getVal( 'KB'),
		)
	);

?>
