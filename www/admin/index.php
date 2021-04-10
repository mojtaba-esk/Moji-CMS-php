<?php
/*
* Author: Mojtaba Eskandari
* Started at 2009-01-07
* Admin Panel Index.
*/

	define( 'IN_MJY_CMS', 1);

//<!--Requirements

	require( '../inc/config.inc.php');
	
	defined( 'DEBUG_MODE') and $startPageTime = microtime( true);

	require( '../inc/functions.common.inc.php');
	require( '../inc/functions.admin.inc.php');

	lib( array(
			'DB',
			'Session',
			'Cache',
			'User',
			'Lang',
			'Date',
			'Template',
			'Paging',
			'JS',
			'Module',
			'Search',
			'Input',
			'URL'
		)
	);

//Requirements-->

	//<!-- Fetch Domain information...

		isset( $_cfg['domain']) or $_cfg['domain'] = Cache::getData( $_SERVER['HTTP_HOST']) or $_cfg['domain'] = Cache::getData( 'www.'. $_SERVER['HTTP_HOST']);
		//$_cfg['URL'] = 'http://'. $_SERVER['HTTP_HOST'] .'/';
		if( !$_cfg['domain']) require('../domain.inc.php');
	
	//-->

	URL::$enRw = false;//Disable the Rewrite URL ( nice url);
	
	//printr( unserialize( 'a:4:{i:1;s:1:"1";i:4;s:1:"1";i:13;a:8:{s:3:"del";s:1:"1";s:3:"edt";s:1:"1";s:3:"new";s:1:"1";s:8:"requests";s:1:"1";s:14:"document_types";s:1:"1";s:11:"house_types";s:1:"1";s:7:"options";s:1:"1";s:7:"regions";s:1:"1";}i:-1;s:1:"1";}'));

	/*printr( serialize(
		array(
			-1 => 1,
			0	=> 1,
			1	=> 1,
			3	=> 1,
			4	=> 1,
			5	=> 1,
			6	=> 1,
			7	=> 1,
			8	=> 1,
			10	=> 1,
			11	=> 1,
			12	=> 1,
			13	=> 1,
			14	=> 1,
			15 => 1)));
			
			/*array(
					'sub' => array( 
						'cats' => array(
								'edt' => 1,
								'lst' => 1
							)
						),

					'mod' => array(
						'lst' => 1,
						'edt' => 1,
					),
				),
			)
		)
	);/**/

	$tpl = new Template( $_cfg['domain']['tmpId']);
	// $tpl -> set_template();

	$tpl -> set_filenames(array(
		'header'	=> 'admin.header',
		'footer'	=> 'admin.footer',
		)
	);

	//<!-- Check Accessible
	
	 	if( ( !User::isLogdIn() || !User::isAccess( -1 /*adminPanel*/)) && ( !isset( $_GET[ 'sub']) || $_GET[ 'sub'] != 'login'))
		{

			$tpl -> assign_vars( array(
				'PAGE_TITLE'	=> Lang::getVal( 'login'),
				'ADMIN_MENU'	=> NULL,
				'HINT'			=> NULL,

				'LANG_DIR'		=> 'rtl',
				'LANG_ALIGN'	=> 'right',
				'LANG_NALIGN'	=> 'left',
				'AJAX'			=> false,

				)
			);
			
			$tpl -> display( 'header');
			msgDie( Lang::getVal( 'uNeedLogin'), $_cfg['URL'] .'admin/?md=users&sub=login', 1, 'error', Lang::getVal( 'login'), true /*No ajax*/);
			$tpl -> display( 'footer');
			
			defined( 'TRANSLATION_MODE') and Lang::printRprt();

			return;
		}
	
	//-->

	//printr( serialize( array( 'adminPanel' => 1)));

	//include( '../inludes/header.admin.php');

	isset( $_GET[ 'md']) or $_REQUEST[ 'md'] = $_GET[ 'md'] = 'home';//Module name
	if( !Module::isExist( $_GET[ 'md'], $_cfg['domain']['planId'], $_cfg['domain']['id']))
	{
		printr( 'The Module does Not Exist!');
		exit();
	}
	
	//<!-- Prepare Admin Menu

		$adminMenu = Cache::getData( 'admin_menu_'. $_cfg['domain']['id'] .'_'. Lang::id() .'.'. @$_SESSION['groupId']) or $adminMenu = include( 'menu.inc.php');
		//$adminMenu .= '<script>$e("md'. Module::$opt['id'] .'").style.display="block";</script>';

	//End of Admin menu-->
	
	if( isset( $_GET[ 'sub']) && $_GET[ 'sub'] == 'login')
	{
		$adminMenu = NULL;
	}

	Lang::$info or Lang::id();//For load the Lang::$info;

	isset( $_GET['mod']) or $_REQUEST['mod'] = $_GET['mod'] = 'lst';
	
	//<!-- Fetch the hint of this page...
	
		$pageHint = NULL;
		$hRw = DB::load(
			array( 
				'tableName' => 'hints',
				'where'	=> array( 
					'mdId'	=> & Module::$opt['id'],
					'mod'	=> & $_GET[ 'mod'],
					'sub'	=> @$_GET[ 'sub'],
				),
			), true
		);

		//if( $hRw) $pageHint = & $hRw[0]['body'];
		if( $hRw) $pageHint = nl2br( $hRw[0]['body']);
		if( defined( 'DEVELOPER_MODE'))
		{
			$pageHint .= '<a href="./?md=developer&sub=hints&mod=edt';
			$pageHint .= '&q[mdId]='. Module::$opt['id'];
			$pageHint .= '&q[mod]='.  $_GET[ 'mod'];
			$pageHint .= '&q[sub]='.  @$_GET[ 'sub'];
			$pageHint .= '">Edit</a>';

		}//End of if( defined( 'DEVELOPER_MODE'));

	//End of Fetch the hint of this page.-->

	//<!-- Sending Variables to Template...

		$tpl -> assign_vars( array(

			'PAGE_TITLE'	=> Lang::getVal( Module::$name) . ( isset( $_GET['sub']) ?  ' | '. Lang::getVal( $_GET['sub']) : ''),
			'SITE_TITLE'	=> & $_cfg['domain']['title'],
			'SITE_URL'		=> & $_cfg['URL'],

			'ADMIN_MENU'	=> & $adminMenu,
			'HINT'			=> & $pageHint,
			
			'LANG_DIR'		=> & Lang::$info['dir'],
			'LANG_ALIGN'	=> & Lang::$info['align'],
			'LANG_NALIGN'	=> & Lang::$info['nAlign'],
			'LANG_SHORT_NAME' => & Lang::$info['shortName'],

			'TODAY'			=> Lang::numfrm( Date::get( 'D, d M Y', time())),
			'LOGOUT'		=> Lang::getVal( 'logout'),
			'PROFILE_EDIT'	=> Lang::getVal( 'profileEdit'),

			'MODULE_NAME'	=> Lang::getVal( Module::$name),
			'ACTION_TITLE'	=> Lang::getVal( $_GET['mod']),
			'MODULE_URL'	=> '?md='. Module::$name,
			'MD'			=> & Module::$name,

			'JS_GN_SRC'		=> JS::ld( '../ext/scr/gn.js'),
			
			'MULTI_LANG'	=> sizeof( Lang::getAll()) > 1,
			
			'AJAX'			=> defined( 'AJAX'),
			'JS_AJX_SRC'	=> JS::ld( '../ext/scr/ajx.js'),

			)
		);

	//End of Sending Variables to Template-->
	
	/*if( !isset( $_GET['mod']) || $_GET['mod'] != 'export' || !isset( $_POST['submit']))
	{
		$tpl -> display( 'header');
	}/**/
	
	if( empty( $_SESSION['pId'])) unset( $_SESSION['pId']);

	if( User::isAccess( Module::$opt['id']) 
		|| 
			( Module::$name == 'users' && 
				( @$_GET['sub'] == 'login' || $_GET['mod'] == 'edt' || $_GET['mod'] == 'view' || $_GET['mod'] == 'logout')
				)
		//||
			//( Module::$name == 'products' && @$_GET['mod'] == 'select')
		)
	{
	
		Module::$opt['permission'] = User::getPerm( Module::$opt['id']);

		if( defined( 'AJAX') && isset( $_REQUEST['ajx']))
		{
			$tpl -> set_filenames(array(
				'header'	=> 'ajax.admin.header',
				'footer'	=> 'ajax.admin.footer',
				)
			);

			require( '../modules/'. Module::$name .'/admin/index.php');
	
			$tpl -> display( 'footer');
			exit();

		}//End of if( defined( 'AJAX') && isset( $_REQUEST['ajx']));
	
		require( '../modules/'. Module::$name .'/admin/index.php');
	
	}else{
	
		//if( Module::$name == 'config' && @$_GET['sub'] == 'activation')
		//{
			//require( '../modules/config/admin/index.php');

		//}else{

			$tpl -> display( 'header');
			msgDie( Lang::getVal( 'accessDenied'), NULL, 0, 'error');
		//}

	}//End of if( User::isAccess( Module::$opt['id']) || Module...;
	
	$tpl -> display( 'footer');

	defined( 'TRANSLATION_MODE') and Lang::printRprt();
	if( defined( 'DEBUG_MODE'))
	{
		$pageCreateTime = microtime( true) - $startPageTime;
		printr( 'Page Create Time: '. round( $pageCreateTime, 3) .' Seconds');

		DB::printRprt();
		//JS::printRprt();
		printr( array( 'ModuleName' => Module::$name , 'Options' => Module::$opt));

		printr( 'Domain info:');
		printr( $_cfg['domain']);
		
		printr( 'SESSION:'); printr( $_SESSION);

	}//End of if( defined( 'DEBUG_MODE'));

	exit();
?>
