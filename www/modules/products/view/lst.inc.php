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

	//<!-- Types..
		
		$typeTitle = NULL;
		$tpRws = DB::load(
			array( 
				'tableName' => Module::$name . '_types',
				'cols' => array( 'rltdId', 'niceUrl', 'title'),
				'where' => array(
					'lngId'	=> Lang::viewId(),
					'domId'	=> $_cfg['domain']['id'],
				),
			),
			Module::$name . $_cfg['domain']['id'] . '_types'
		);
		
		if( is_array( $tpRws))
		{
			if( Module::$opt['catsImageFile'])
			{
				lib( array( 'File', 'Img'));
				
				isset( $file) or $file = new File( Module::$name);
				Img::setPrfx( Module::$name);

			}//End of if( Module::$opt['imageFile']);

			foreach( $tpRws as $rw)
			{
				$actv = false;
				if( isset( $_GET['typeId']) && 
					(
						(
							!empty( $rw['niceUrl']) && $rw['niceUrl'] == $_GET['typeId']) ||
							$rw['rltdId'] == $_GET['typeId']
						)
					)
				{
						$typeTitle = $rw['title'];
						$actv = true;
						$_GET['typeId'] = $rw['rltdId'];

				}//End of if( isset( $_GET['typeId']) && ...;

				$tpl -> assign_block_vars( 'typblck',  array(

						'ACTV'	=>	$actv ? 'actv' : '',
						'TITLE'	=>	$rw['title'],
						'IMG'	=>	Module::$opt['typesImageFile'] ? ( '<img src="'. $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId'], 0, 'img.types.'), array( 'h' => 80, 'w' => 100)).'" /><br />' ) : '',
						'URL'	=>	URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&typeId='. ( $rw['niceUrl'] ? $rw['niceUrl'] : $rw['rltdId'])),

					)
				);

			}//End of foreach( $tpRws as $rw);

			if( @sizeof( $tpRws) <= 1) $typeTitle = NULL;
			unset( $tpRws);
			/**/
		
		}//End of if( is_array( $tpRws));

	//End of Types.-->	
	
	//<!-- Categories..
		
		$catTitle = NULL;

		if( Module::$opt['categoryMod'] && is_array( $cats = getCats( Module::$name, Lang::viewId())))
		{
			if( Module::$opt['catsImageFile'])
			{
				lib( array( 'File', 'Img'));
				
				isset( $file) or $file = new File( Module::$name);
				Img::setPrfx( Module::$name);

			}//End of if( Module::$opt['imageFile']);
			
			if( sizeof( $cats) > 1)
			{
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

			}//End of if( sizeof( $cats) > 1);

			isset( $_GET['catId']) and $catTitle = $cats[ $_GET['catId'] ];
		
		}//End of if( sizeof( $rws));

	//End of Categories.-->
	
	//<!-- Extraction of main data...
	
		//<!-- Search 

			$srchSQL = '1';
			if( Module::$opt['viewSrchBar'])
			{
				//isset( $_REQUEST['md']) or $_REQUEST['md'] = 'news';
				$srchSQL = include( 'srch.inc.php');
			
			}//End of if( Module::$opt['viewSrchBar']);
		
		//End of Search-->

		isset( $_GET['catId'])	and	$srchSQL .= ' AND `m`.`catId` = '. intval( $_GET['catId']);
		isset( $_GET['typeId'])	and	$srchSQL .= ' AND `m`.`typeId` = '. intval( $_GET['typeId']);
		$srchSQL .= ' AND `m`.`domId` = '. $_cfg['domain']['id'];

		$sortSQL = '';//isset( $_GET['sort']) ? "`{$_GET['sort']}` {$_GET['srtType']}, " : '';

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
				`'. Module::$name . '_main`		AS	`m`,
				`'. Module::$name . '_types`	AS	`t`
				
			WHERE
				`m`.`lngId` = '. Lang::viewId().'
				AND
					`t`.`lngId` = '. Lang::viewId().'
				AND
					`t`.`rltdId` = `m`.`typeId`
				AND '. $srchSQL .'
			ORDER BY
				'. $sortSQL .' `m`.`'. Module::$opt['viewOrdrBy'] .'` '. Module::$opt['viewOrdrType'];

		lib( array( 'Paging'));
		$pging = new Paging( array(
					'SQL'		=> $SQL,
					'perPage'	=> Module::$opt['viewLstLmt'],
					//'cachePrfx'	=> Module::$name . '_main',
					//'excldVars'	=> array( 'vLng', 'rws[0][1][cat]'),
				)
			);
	
		$SQL = $pging -> getSQL();
		$rws = DB::load( $SQL, Module::$name . $_cfg['domain']['id'] .'_main');

		//printr( $rws);

	//End of Extraction of main data-->
	
	$tpl -> assign_vars( array(

		'L_SEARCH' => Lang::getVal( 'search'),
		//'L_CANCEL_SEARCH' => Lang::getVal( 'cancelSearch'),

		'PAGE_TITLE' => $_cfg['domain']['title'] .' | '. Lang::getVal( Module::$name) .( $typeTitle ? ' | '. $typeTitle : ''),
		
		'L_SEARCH_RESULTS'  => Lang::getVal( 'searchResults'),
		'SEARCH_BAR'		=> Module::$opt['viewSrchBar'],
		'SEARCHED'			=> isset( $_GET['srch']),
		
		'NOT_EXIST_MESSAGE'	=> Lang::getVal( 'noDataExist'),
		'DATA_EXIST'		=> @sizeof( $rws),

		'TYPES'		=>	true,
		'TYPES_IMG'	=>	Module::$opt['typesImageFile'],//IF Types  Image.
		'TYPE_TITLE'=>	& $typeTitle,
		
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

				'RW_ODD' => $key & 1,
				'ID' => $rw['rltdId'],
				
				'TITLE' => $rw['title'],
				'BRIEF' => !empty( $rw['lead']) ? $rw['lead'] : briefStr( strip_tags( $rw['body']), 300),
				'PUBLISH_TIME' => Lang::numFrm( Date::get( 'D d M Y', $rw[ 'insrtTime'])),

				'PRICE' => Module::$opt['hasPrice'] && $rw[ 'price'] ? Lang::numFrm( number_format( $rw[ 'price'])) .' '. Lang::getVal( 'rials') : false,

				'IMG_SRC'	=> $_cfg['URL'] . Img::get( $file -> getPth( $rw['rltdId']), array( 'h' => 80, 'w' => 120)),
				
				'URL' => URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&mod=full&typeId='. ( $rw['typeNiceUrl'] ? $rw['typeNiceUrl'] : $rw['typeId']) .'&id='. ( $rw['niceUrl'] ? $rw['niceUrl'] : $rw['rltdId']) .'&t='. URL::clr( $rw['title'])),

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
	//$tpl -> display( 'leftMenu');
?>
