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
			'L_READ_MORE' => Lang::getVal( 'readMore'),

			)
		);
		
	URL::$rwRules['/md='. $boxMd .'/'] = $boxMd;
	URL::$rwRules['/mod=full/']	 = '';
	URL::$rwRules['/id=([0-9]*)/'] = '\\1';
	URL::$rwRules['/typeId=([0-9a-zA-Z\_\-\.]+)/']	= '\\1';
	URL::$rwRules['/t=((.)*)/']	= '\\1';

	$SQL = '
			SELECT
				`m`.`rltdId`,
				`m`.`niceUrl`,
				`m`.`title`,
				`m`.`body`,
				`m`.`insrtTime`,
				`m`.`price`,
				`m`.`typeId`,
				`t`.`niceUrl` AS `typeNiceUrl`
			FROM
				`'. $boxMd . '_main`	AS	`m`,
				`'. $boxMd . '_types`	AS	`t`
			WHERE
				`m`.`lngId` = '. Lang::viewId().'
				AND
					`t`.`lngId` = '. Lang::viewId().'
				AND
					`t`.`rltdId` = `m`.`typeId`
				AND
					`m`.`domId` = 0'. $_cfg['domain']['id'] .'
				AND
					`t`.`domId` = 0'. $_cfg['domain']['id'] .'
			ORDER BY
				`m`.`id` DESC
			LIMIT 4';	

	$rws = DB::load( $SQL, $boxMd . $_cfg['domain']['id'] .'_main');
	
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
			'BRIEF'		=> briefStr( strip_tags( $rws[ $i ]['body']), 300),
			'PRICE'		=> $rws[ $i ][ 'price'] ? Lang::numFrm( number_format( $rws[ $i ][ 'price'])) .' '. Lang::getVal( 'rials') : false,

			'IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rws[ $i ]['rltdId']), array( 'h' => 130, 'w' => 130)),
			'URL'		=> URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. $boxMd .'&mod=full&typeId='. ( $rws[ $i ]['typeNiceUrl'] ? $rws[ $i ]['typeNiceUrl'] : $rws[ $i ]['typeId']) .'&id='. ( $rws[ $i ]['niceUrl'] ? $rws[ $i ]['niceUrl'] : $rws[ $i ]['rltdId']) .'&t='. URL::clr( $rws[ $i ]['title'])),

			)
		);

	}//End of foreach( $rws as $key => $rw);
?>
