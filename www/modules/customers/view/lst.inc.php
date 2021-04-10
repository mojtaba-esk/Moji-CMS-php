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
		if( @Module::$opt['viewSrchBar'])
		{
			isset( $_REQUEST['md']) or $_REQUEST['md'] = 'news';
			$srchSQL = include( 'srch.inc.php');
			
		}//End of if( Module::$opt['viewSrchBar']);
		
	//End of Search-->
	
	//<!-- Categories...
		
		$catTitle = NULL;

		if( Module::$opt['categoryMod'] && is_array( $cats = getCats( Module::$name, Lang::viewId(), 'cats', ' AND `domId` = 0'. $_cfg['domain']['id'])))
		{
			if( Module::$opt['catsImageFile'])
			{
				lib( array( 'File', 'Img'));
				
				isset( $file) or $file = new File( Module::$name);
				Img::setPrfx( Module::$name);

			}//End of if( Module::$opt['imageFile']);
			
			
			isset( $_GET['catId']) or $_GET['catId'] = key( $cats);

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
			/**/
			
			isset( $_GET['catId']) and $catTitle = $cats[ $_GET['catId'] ];
		
		}//End of if( sizeof( $rws));

	//End of Categories.-->	
	
	//isset( $_GET['catId']) && $srchSQL == '1' and $srchSQL .= ' AND `catId` = '. intval( $_GET['catId']);
	//isset( $_GET['catId']) and $srchSQL .= ' AND `catId` = '. intval( $_GET['catId']);
	$sortSQL = '';//isset( $_GET['sort']) ? "`{$_GET['sort']}` {$_GET['srtType']}, " : '';
	
	if( isset( $_GET['catId']))
	{
		$srchSQL .= ' AND `catId` = '. intval( $_GET['catId']);
	
	}//End of if( isset( $_GET['catId']));
	
	$srchSQL .= ' AND `domId` = '. $_cfg['domain']['id'];
	
	//<!-- Load the archives...

		$archvTitle = NULL;
		if( isset( $_GET['archv']))
		{
		 	$_GET['archv'] = explode( ',', $_GET['archv']);
		 	$srchSQL .= ' AND `pblishTime` >= '. intval( $_GET['archv'][0]);
		 	$srchSQL .= ' AND `pblishTime` < '. intval( $_GET['archv'][1]);

		 	$archvTitle = Lang::numFrm( Date::get( 'M Y', intval( $_GET['archv'][0])));

		}//End of if( isset( $_GET['archv']));
		
	//End of Load the archives.-->

	$SQL = '
		SELECT *
		FROM
			`'. Module::$name . '_main` 
		WHERE
			`lngId` = '. Lang::viewId().'
			AND '. $srchSQL .'
		ORDER BY
			'. $sortSQL .' `'. Module::$opt['viewOrdrBy'] .'` '. Module::$opt['viewOrdrType'];

	lib( array( 'Paging'));
	$pging = new Paging( array(
				'SQL'		=> $SQL,
				'perPage'	=> Module::$opt['viewLstLmt'],
				'cachePrfx'	=> Module::$name . '_main',
				//'excldVars'	=> array( 'vLng', 'rws[0][1][cat]'),
			)
		);
	
	$SQL = $pging -> getSQL();
	$rws = DB::load( $SQL, Module::$name .'_main');
	
	//printr( $SQL);
	
	$tpl -> assign_vars( array(

		'L_SEARCH' => Lang::getVal( 'search'),
		//'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),

		'PAGE_TITLE' => $_cfg['domain']['title'] .' | '. Lang::getVal( Module::$name) . $archvTitle,
		
		'ARCHIVE'	=> & $archvTitle,
		
		'L_SEARCH_RESULTS'  => Lang::getVal( 'searchResults'),
		'SEARCH_BAR'		=> @Module::$opt['viewSrchBar'],
		'SEARCHED'			=> isset( $_GET['srch']),
		
		'NOT_EXIST_MESSAGE'	=> Lang::getVal( 'noDataExist'),
		'DATA_EXIST'		=> sizeof( $rws),

		
		'CATS'		=>	Module::$opt['categoryMod'],//IF Cats.
		'CATS_IMG'	=>	Module::$opt['catsImageFile'],//IF Cats Image.
		'CAT_TITLE'	=>	& $catTitle,
		
		'L_READ_MORE' => Lang::getVal( 'readMore'),

		)
	);

	if( Module::$opt['imageFile'])
	{
		lib( array( 'File', 'Img'));

		isset( $file) or $file = new File( Module::$name);
		Img::setPrfx( Module::$name);

	}//End of if( Module::$opt['imageFile']);

	if( is_array( $rws))
	{
		foreach( $rws as $key => $rw)
		{
			$tpl -> assign_block_vars( 'myblck',  array(

				//'RW' => Lang::numFrm( $pging -> prms['strt'] + $key + 1),
				'RW_ODD' => $key & 1,
				'ID' => $rw['rltdId'],
				
				'TITLE' => $rw['title'],
				//'BRIEF' => !empty( $rw['lead']) ? $rw['lead'] : briefStr( strip_tags( $rw['body']), 300),
				'BRIEF' => $rw['body'],
				//'PUBLISH_TIME' => Lang::numFrm( Date::get( 'D d M Y', $rw[ 'pblishTime'])),
				
				'IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'h' => 80, 'w' => 120)),
				
				//'URL' => URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&mod=full&id='. $rw['rltdId'] .'&t='. $rw['title']),

				)
			);

		}//End of foreach( $rws as $key => $rw);
	
	}//End of if( sizeof( $rws));

	$tmp = URL::$enRw;
	isset( $_GET['srch']) and URL::$enRw = false;

	$pging -> makeLnks();

	URL::$enRw = $tmp;

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
