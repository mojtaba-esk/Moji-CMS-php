<?php
/*
* Author: Mojtaba Eskandari
* Started at 2009-08-28
* Site View Index.
*/

define( 'IN_MJY_CMS', 1);

//<!--Requirements

	require( './inc/config.inc.php');
	
	defined( 'DEBUG_MODE') and $startPageTime = microtime( true);

	require( './inc/functions.common.inc.php');
	//require( './inc/functions.view.inc.php');

	lib( array(
			'DB',
			'Session',
			'Cache',
			'Lang',
			'Date',
//			'User',
			'Template',
//			'Paging',
			'JS',
			'Module',
//			'Search',
//			'Input',
			'URL'
		)
	);
	
	//<!-- Fetch Domain information...

		isset( $_cfg['domain']) or $_cfg['domain'] = Cache::getData( $_SERVER['HTTP_HOST']) or $_cfg['domain'] = Cache::getData( 'www.'. $_SERVER['HTTP_HOST']);
		//$_cfg['URL'] = 'http://'. $_SERVER['HTTP_HOST'] .'/';
		if( !$_cfg['domain']) require('./domain.inc.php');
	
	//-->

	//Requirements-->

		URL::prepare();
		//User::$tblPrfx = 'view_users';
		Lang::$info or Lang::id();//For load the Lang::$info;

	//-->
	
	
	//<!-- Changing Template...
	
		if( !empty( $_GET['tmpId']))
		{
			$_cfg['domain']['tmpId'] = $_SESSION['tmpId'] = intval( $_GET['tmpId']);
	
		}elseif( !empty( $_SESSION['tmpId'])){
		
			$_cfg['domain']['tmpId'] = $_SESSION['tmpId'];
		}

	//-->

	$tpl = new Template( $_cfg['domain']['tmpId']);
	// $tpl -> set_template();

	$tpl -> set_filenames( array(
		'header'	=> 'view.header',
		'footer'	=> 'view.footer',
		)
	);

	isset( $_GET[ 'md']) or $_GET[ 'md'] = 'home';//Module name
	if( !Module::isExist( $_GET[ 'md'], $_cfg['domain']['planId'], $_cfg['domain']['id']))
	{
		if( defined( 'DEBUG_MODE'))
		{
			printr( 'The Module is Not Exist!');
			exit();
		}

		notFound();

	}//End of if( !Module::isExist( $_GET[ 'md']));

	isset( $_GET['mod']) or $_GET['mod'] = 'lst';

	URL::$rwRules['/md='. Module::$name .'/'] = Module::$name;

	//<!-- Sending Variables to Template...
	
		$tpl -> assign_vars( array(
			
			'PAGE_TITLE'		=> $_cfg['domain']['title'] .' | '. Lang::getVal( Module::$name),
			'SITE_TITLE'		=> & $_cfg['domain']['title'],
			'DOM_ID'			=> & $_cfg['domain']['id'],

			'SITE_URL'			=> & $_cfg['URL'],
			'SITE_ADDR'			=> & $_SERVER['HTTP_HOST'], //Lang::getVal( 'siteAddr'),
			'PAGE_COPYRIGHT'	=> & $_cfg['URL'],
			'PAGE_AUTHOR'		=> 'Developed By AndisheSazan.ir',
			
			//'ADMIN_MENU'	=> & $adminMenu,
			
			'LANG_DIR'		=> & Lang::$info['dir'],
			'LANG_ALIGN'	=> & Lang::$info['align'],
			'LANG_NALIGN'	=> & Lang::$info['nAlign'],
			'LANG_SHORT_NAME' => & Lang::$info['shortName'],
			
			//'TODAY'		=> Lang::numfrm( Date::get( 'D, d M Y', time())),
			'G_YEAR'	=> date( 'Y'),
			
			'MODULE_NAME'	=> Lang::getVal( Module::$name),
			'MD'			=> & Module::$name,
			'MODULE_URL'	=> URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name),

			'HOME'			=> Lang::getVal( 'home'),
			'HOME_URL'		=> URL::rw( '?lng='. Lang::$info['shortName']),

			'JS_GN_SRC'		=> JS::ld( 'ext/scr/gn.js'),
			
			'SIDEBAR'		=> @$_cfg['domain']['sidebar'],
			
			//'SEARCH'		=> Lang::getVal( 'search'),
			//'SEARCH_URL'	=> URL::rw( '?lng='. Lang::$info['shortName'] .'&md=search'),
			//'SEARCH_QUERY'	=> isset( $_REQUEST['srh'][0][1]['query']) ? $_REQUEST['srh'][0][1]['query'] : Lang::getVal( 'search') .'...',

			)
		);

	//End of Sending Variables to Template-->
	
	//<!-- Top menu
	
		//URL::$rwRules['/([&])?md=([^&]*)+(&)?/'] = '\\2';
	
		$menu = array(
			'home'		=> NULL,
			'products'	=> NULL,
			'news'		=> NULL,
			//'downloads'	=> NULL,
			//'search'	=> NULL,
			//'faq'		=> NULL,
			//'galleries'	=> NULL,//URL::rw( '?lng='. Lang::$info['shortName'] .'&md=pages&name=aboutUs'),
			//'help'		=> NULL,
			//'customers'	=> NULL,
			'aboutUs'	=> NULL,
			'about'		=> NULL,
			//'blog' 		=> NULL,
			//'poll'		=> NULL,
		);

		foreach( $menu as $md => $url)
		{
			//$url or $url = URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. $md);
			$url or $url = $_cfg['URL'] . $md;// .'/lng/'. Lang::$info['shortName'];

			$tpl -> assign_block_vars( 'menu',  array(

					'ACTIVE'	=>	$_GET['md'] == $md || @$_GET['name'] == $md ? 'active' : '',
					'TITLE'		=>	Lang::getVal( $md),
					'NAME'		=>	$md,
					'URL'		=>	$url,
				)
			);

		}//End of foreach( $menu as $md => $url);

		unset( $menu);
		
		$tpl -> assign_vars( array( 'PAGE_NAME' => isset( $_GET['name']) ? $_GET['name'] : $_GET['md']));
	//-->
	
	//include( '../inludes/menu.admin.php');
	//$tpl -> display( 'header');//For Set Header Title, Keywords, Desc & ... This Method Called in Module index.

	require( './modules/'. Module::$name .'/view/index.php');

	//Sidebar...
	if( @$_cfg['domain']['sidebar']) require( './modules/'. $_cfg['domain']['sidebar'] .'/view/sidebar.inc.php');
	
	//include( '../inludes/footer.admin.php');
	$tpl -> display( 'footer');
	
	//<!-- Template Listing
	
		$tRws = DB::load(
			array( 
				'tableName' => 'templates_main',
				'where' => array(
					'isPublic'	=>	1,
					'lngId'		=>	Lang::id()
				),
			)
		);

		$tplList = '<ul>';
		foreach( $tRws as $rw)
		{
			$tplList .= '<li>';
			
				$tplList .= '<a href="'. $_cfg['URL'] .'?tmpId='. $rw['rltdId'] .'">';
				$tplList .= $_cfg['domain']['tmpId'] == $rw['rltdId'] ? ' [ <b>' : '';
				$tplList .= $rw['title'];
				$tplList .= $_cfg['domain']['tmpId'] == $rw['rltdId'] ? ' ]</b>' : '';
				$tplList .= '</a>';
			
			$tplList .= '</li>';
		}
		$tplList .= '</ul>';

		print( '<fieldset style="background-color:#FF0;"><legend><b>Templates</b></legend>'. $tplList .'</fieldset>');

	//-->	

	defined( 'TRANSLATION_MODE') and Lang::printRprt();
	if( defined( 'DEBUG_MODE'))
	{

		$pageCreateTime = microtime( true) - $startPageTime;
		printr( 'Page Create Time: '. round( $pageCreateTime, 3) .' Seconds');

		DB::printRprt();
		//JS::printRprt();
		printr( array( 'ModuleName' => Module::$name , 'Options' => Module::$opt));
		
		printr( 'URL Rewrite Ruls:');
		printr( URL::$rwRules);

		//printr( 'SESSION:'); printr( $_SESSION);

	}//End of if( defined( 'DEBUG_MODE'));
	exit();
?>