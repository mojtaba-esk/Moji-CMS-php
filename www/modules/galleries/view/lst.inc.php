<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. List The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	$tpl -> set_filenames( array(
		'body' => Module::$name .'.view.list',
		)
	);
	
	//<!-- Search 
		
		$srchSQL = '1';
		if( Module::$opt['viewSrchBar'])
		{
			$srchSQL = include( 'srch.inc.php');
			
		}//End of if( Module::$opt['viewSrchBar']);
		
	//End of Search-->
	
	isset( $_GET['catId']) and $srchSQL .= ' AND `catId` = '. intval( $_GET['catId']);
	$sortSQL = '';//isset( $_GET['sort']) ? "`{$_GET['sort']}` {$_GET['srtType']}, " : '';

	$srchSQL .= ' AND `domId` = '. $_cfg['domain']['id'];

	$SQL = 'SELECT * FROM `'. Module::$name . '_main` 
		WHERE `lngId` = '. Lang::viewId().' AND '. $srchSQL .'
		ORDER BY '. $sortSQL .' `'. Module::$opt['viewOrdrBy'] .'` '. Module::$opt['viewOrdrType'];
		
	lib( array( 'Paging'));

	//require( $_cfg['path'] .'/inc/lib/Paging.class.inc.php');
	$pging = new Paging( array(
				'SQL' 		=> $SQL,
				'perPage'	=> Module::$opt['viewLstLmt'],
				'cachePrfx'	=> Module::$name . '_main',
				//'excldVars'	=> array( 'vLng', 'rws[0][1][cat]'),
			)
		);
	
	$SQL = $pging -> getSQL();
	$rws = DB::load( $SQL, Module::$name . '_main');
	
	//printr( $SQL);
	
	//<!-- Categories..
		
		$catTitle = NULL;

		if( Module::$opt['categoryMod'] && is_array( $cats = getCats( Module::$name, Lang::viewId())))
		{
			if( Module::$opt['catsImageFile'])
			{
				
				lib( array(
					'File',
					'Img',
				));
				isset( $file) or $file = new File( Module::$name);
				Img::setPrfx( Module::$name);

			}//End of if( Module::$opt['imageFile']);
			
			foreach( $cats as $id => $title)
			{
				$tpl -> assign_block_vars( 'catblck',  array(

						'ACTV'	=>	isset( $_GET['catId']) && $_GET['catId'] == $id ? 'actv' : '',
						'TITLE'	=>	$title,
						'IMG'	=>	Module::$opt['catsImageFile'] ? ( '<img src="'. $_cfg['URL'] . Img::get( $file -> getPth( $id, 0, 'img.cats.'), array( 'h' => 80, 'w' => 100)).'" /><br />' ) : '',
						'URL'	=>	URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&catId='. $id),

					)
				);
				
			}//End of foreach( $rws as $key => $rw);
			
			isset( $_GET['catId']) and $catTitle = $cats[ $_GET['catId'] ];
		
		}//End of if( Module::$opt['categoryMod'] && is_array( $cats = getCa...;

	//End of Categories.-->
	
	$tpl -> assign_vars( array(

		'L_SEARCH' => Lang::getVal( 'search'),
		//'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),

		'PAGE_TITLE' => $_cfg['domain']['title'] .' | '. Lang::getVal( Module::$name),
		
		'L_SEARCH_RESULTS' => Lang::getVal( 'searchResults'),
		'SEARCH_BAR'	=> NULL,//Module::$opt['viewSrchBar'], //Hide The search bar.
		'SEARCHED'		=> isset( $_GET['srch']),
		
		'NOT_EXIST_MESSAGE' => Lang::getVal( 'noDataExist'),
		'DATA_EXIST'	=> sizeof( $rws),
		
		'CATS'		=>	Module::$opt['categoryMod'],//IF Cats.
		'CATS_IMG'	=>	Module::$opt['catsImageFile'],//IF Cats Image.
		'CAT_TITLE'	=>	& $catTitle,
		
		'L_READ_MORE' => Lang::getVal( 'readMore'),

		)
	);

	if( Module::$opt['imageFile'])
	{
		lib( array(
			'File',
			'Img',
		));

		//require_once( $_cfg['path'] .'/inc/lib/File.class.inc.php');
		isset( $file) or $file = new File( Module::$name);

		//require_once( $_cfg['path'] .'/inc/lib/Img.class.inc.php');
		Img::setPrfx( Module::$name);

	}//End of if( Module::$opt['imageFile']);

	is_array( $rws) or $rws = array();
	foreach( $rws as $key => $rw)
	{
		$tpl -> assign_block_vars( 'myblck',  array(

			//'RW' => Lang::numFrm( $pging -> prms['strt'] + $key + 1),
			'RW_ODD' => $key & 1,
			'ID' => $rw['rltdId'],
			
			'TITLE' => $rw['title'],
			'BRIEF' => briefStr( !empty( $rw['lead']) ? $rw['lead'] : strip_tags( $rw['body']), 500),
			'PUBLISH_TIME' => Lang::numFrm( Date::get( 'D d M Y', $rw[ 'pblishTime'])),
			
			'IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( /*'h' => 80,*/ 'w' => 170)),
			
			'URL' => URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&mod=full&id='. $rw['rltdId']),

			)
		);

	}//End of foreach( $rws as $key => $rw);
	
	$pging -> makeLnks();
	$tpl -> assign_vars( array(

			'PAGING'	=> $pging -> lnks[ 'totlPgs'] > 1,
			'PG_LINKS'	=> & $pging -> lnks[ 'all'],
			'PG_NEXT'	=> & $pging -> lnks[ 'nxt'],
			'PG_PREV'	=> & $pging -> lnks[ 'prv'],
			'PG_FIRST'	=> & $pging -> lnks[ 'frst'],
			'PG_LAST'	=> & $pging -> lnks[ 'last'],

			'FIRST_PG'	=> Lang::getVal( 'firstPage'),
			'PREV_PG'	=> Lang::getVal( 'previousPage'),
			'NEXT_PG'	=> Lang::getVal( 'nextPage'),
			'LAST_PG'	=> Lang::getVal( 'lastPage'),

		)
	);
	
	$tpl -> display( 'header');
	$tpl -> display( 'body');
?>
