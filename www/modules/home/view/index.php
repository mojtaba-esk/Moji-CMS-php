<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	$tpl -> set_filenames( array(
		'full' => Module::$name .'.view.full',
		)
	);
	
		$tpl -> assign_vars( array(

			'PAGE_TITLE'	=> $_cfg['domain']['title']	.' | '.	Lang::getval( Module::$name),
			'PAGE_KEYWORDS'	=> $_cfg['domain']['title']	.','.	Lang::getval( Module::$name),
			'PAGE_DESC'		=> $_cfg['domain']['title']	.' - '.	Lang::getval( Module::$name),
			
			'NEWS_URL'		=> $_cfg['URL'] .'news',
			'FAQ_URL'		=> $_cfg['URL'] .'faq',
			'HELP_URL'		=> $_cfg['URL'] .'help',

			)
		);

	//End of Send vars to Template -->

	lib( array( 'File', 'Img'));
	isset( $file) or $file = new File( Module::$name);
	Img::setPrfx( Module::$name);
	
	//<!-- Load the slider Images...
	
			$SQL = '
				SELECT
					`rltdId`,
					`title`
				FROM
					`'. Module::$name .'_slider`
				WHERE
					`lngId` = '. Lang::viewId().'
					AND
						`domId` = 0'. $_cfg['domain']['id'] .'
				ORDER BY `id` ASC
			';

			$rws = DB::load( $SQL, Module::$name .'_slider');

			$rwsSz = sizeof( $rws);
			for( $i = 0; $i != $rwsSz; $i++)
			{
				$tpl -> assign_block_vars( 'slider',  array(

					'DESC'		=> briefStr( $rws[ $i ]['title'], 300),
					'IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rws[ $i ]['rltdId'], 0, 'img.slider.')),// array( 'w' => 960)),
					'URL' 		=> '',//URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. $md .'&mod=full&id='. $rws[ $i ]['rltdId'], '/'. $rws[ $i ]['title']),

					)
				);

			}//End of for( $i = 0; $i != $rwsSz; $i++);
		
	//-->

	//<!-- Show The Modules for the buttom boxes...
		
		//Home Page Boxes...
		if( @$_cfg['domain']['home'])
		{
			is_array( $_cfg['domain']['home']) or $_cfg['domain']['home'] = array();
			foreach( $_cfg['domain']['home'] as $boxMd)
			{
				require( './modules/'. $boxMd .'/view/home.inc.php');
			}

		}//End of if( @$_cfg['domain']['home']);
	
	//End of boxes-->
	
	$tpl -> display( 'header');
	$tpl -> display( 'full');
?>
