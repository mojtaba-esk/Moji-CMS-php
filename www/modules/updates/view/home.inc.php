<?php
/**
* @author Mojtaba Eskandari
* @since 2013-04-20
* @name List the information required to show on the home page;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( defined( 'DEBUG_MODE') && empty( $tpl)){ printr( 'Error! the [ $tpl ] object is not defined here!<br />File: ' . __FILE__); exit();}
	
	
		$tpl -> assign_vars( array(

			strtoupper( $boxMd). '_URL'		=> $_cfg['URL'] . $boxMd,

			)
		);

	URL::$rwRules['/md='. $boxMd .'/'] = $boxMd;
	URL::$rwRules['/mod=full/']	 = '';
	URL::$rwRules['/id=([0-9]*)/'] = '\\1';

	$SQL = '
		SELECT
			`rltdId`,
			`title`
		FROM
			`'. $boxMd .'_main`
		WHERE
			`lngId` = '. Lang::viewId().'
			AND
				`domId` = 0'. $_cfg['domain']['id'] .'
	
		ORDER BY `pblishTime` DESC
		LIMIT 10
	';

	$rws = DB::load( $SQL, $boxMd .'_main');
	
	isset( $file) or lib( array( 'File', 'Img'));
	
	//isset( $file) or $file = new File( $boxMd);
	//$file -> File( $boxMd);
	
	$file = new File( $boxMd);
	Img::setPrfx( $boxMd);

	$rwsSz = sizeof( $rws);
	for( $i = 0; $i != $rwsSz; $i++)
	{
		$tpl -> assign_block_vars( $boxMd,  array(

			'ID' 		=> $rws[ $i ]['rltdId'],

			'TITLE'		=> briefStr( $rws[ $i ]['title'], 150),
			'IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rws[ $i ]['rltdId']), array( 'w' => 140)),
			'URL' 		=> URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. $boxMd .'&mod=full&id='. $rws[ $i ]['rltdId'], '/'. URL::clr( $rws[ $i ]['title'])),

			)
		);

	}//End of foreach( $rws as $key => $rw);
?>
