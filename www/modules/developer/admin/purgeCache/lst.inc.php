<?php
/**
* @author Mojtaba Eskandari
* @since 2009-12-24
* @name Module Admin Panel. Clean The Cache;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	$startTime = time();
	
	$_cfg['cachePath']	= dirname( __FILE__) .'/../../../../cache/files/';
	$_cfg['etcPath']	= dirname( __FILE__) .'/../../../../cache/ext/';
	
	if( !in_array( @$_cfg[ @$_GET['type']], array( $_cfg['cachePath'], $_cfg['etcPath'] )))
	{
		msgDie( 'The path is Not Valid', NULL, 0, 'error');
		return;
	}
	
	$ignoreLst = array(
		$_cfg[ 'etcPath'] .'0.jpg',
		$_cfg[ 'etcPath'] .'index.html',
	);
	
	$files = glob( $_cfg[ $_GET['type']] .'*');
	$files or $files = array();
	foreach( $files as $file)
	{
		if( time() - $startTime > 25)
		{
			msgDie( Lang::getVal( 'purgeInProgress') .'...', './'. getUrl(), 1);
			return;
		}
		
		if( in_array( $file, $ignoreLst))continue;
		unlink( $file);
		printr( $file);
	}

	msgDie( Lang::getVal( 'finished'));
?>
