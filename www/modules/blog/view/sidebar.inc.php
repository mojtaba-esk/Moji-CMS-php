<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. List The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( defined( 'DEBUG_MODE') && empty( $tpl)){ printr( 'Error! the [ $tpl ] object is not defined here!<br />File: ' . __FILE__); exit();}
	
	$srchSQL = '1';
	$srchSQL .= ' AND `domId` = '. $_cfg['domain']['id'];

	$SQL = '
		SELECT
			`rltdId`,
			`title`,
			`body`,
			`pblishTime`
		FROM
			`'. $_cfg['domain']['sidebar'] . '_main` 
		WHERE
				`lngId` = '. Lang::viewId().'
			AND
				`pblishTime` > 0
			AND
				`pblishTime` <= '. time() .'
			AND '. $srchSQL .'
		ORDER BY
			`pblishTime` DESC
		LIMIT	4';

	$rws = DB::load( $SQL, $_cfg['domain']['sidebar'] . '_main');
	
	//printr( $SQL);

	$tpl -> assign_vars( array(
	
		'SD_TITLE' => Lang::getVal( $_cfg['domain']['sidebar'].'Side'),

		)
	); /**/

/*	if( Module::$opt['imageFile'])
	{
		lib( array( 'File', 'Img'));

		isset( $file) or $file = new File( Module::$name);
		Img::setPrfx( Module::$name);

	}//End of if( Module::$opt['imageFile']);
	/**/

	if( is_array( $rws))
	{
		foreach( $rws as $key => $rw)
		{
			$tpl -> assign_block_vars( 'sidebar',  array(

				//'RW' => Lang::numFrm( $pging -> prms['strt'] + $key + 1),
				//'RW_ODD' => $key & 1,
				'ID'	=> $rw['rltdId'],
				'CLASS'	=> '',
				
				'TITLE'	=> $rw['title'],
				'BRIEF' => !empty( $rw['lead']) ? $rw['lead'] : briefStr( strip_tags( $rw['body']), 200),
				'PUBLISH_TIME' => Lang::numFrm( Date::get( 'D d M Y', $rw[ 'pblishTime'])),
				
				//'IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'h' => 80, 'w' => 120)),
				
				'URL' => URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. $_cfg['domain']['sidebar'] .'&mod=full&id='. $rw['rltdId'] .'&t='. URL::clr( $rw['title'])),

				)
			);

		}//End of foreach( $rws as $key => $rw);
	
	}//End of if( sizeof( $rws));
?>
