<?php
/*
* Author: Mojtaba Eskandari
* Started at 2009-08-26
* Admin Panel Menu.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	$SQL = 'SELECT * FROM `admin_menu`
		WHERE `parentId` = 0
		ORDER BY `orderId` ASC';
	
	$rslt = '';

	$rws = DB::load( $SQL);
	
	if( defined( 'DEVELOPER_MODE'))
	{
		$dvlpr[] = array( 
						'id'	=>	-1,
						'mdId'	=>	0,
						'title'	=>	'developer'
					);
		$rws = array_merge( $dvlpr, $rws);
	}

/*	$rslt .= '<link rel="stylesheet" type="text/css" href="../ext/smenu/css/superfish-rtl.css" media="screen">';
	$rslt .= '<script type="text/javascript" src="../ext/smenu/js/hoverIntent.js"></script>';
	$rslt .= '<script type="text/javascript" src="../ext/smenu/js/superfish.js"></script>';
	$rslt .= '<script type="text/javascript">
		$(function(){
			setTimeout( function(){
			$(\'ul.sf-menu\').superfish();
			}, 2000);
		});
		</script>
		<style type="text/css">
			.sf-sub-indicator{
				background:none;
			}
			.sf-menu{
				float:right;
			}
		</style>';
/**/
		
	$rslt .= '<ul class="sf-menu mnu">';
	
	foreach( $rws as $rw)
	{
		if( !User::isAccess( $rw['mdId']))continue;
		
		$rslt .= '<li><a href="#"><img src="../ext/admnMnu/'. $rw[ 'title'] .'.png" /> '. Lang::getval( $rw[ 'title']) .'</a>';
		$bdy = DB::load( 'SELECT * FROM `admin_menu` WHERE `parentId` = '. $rw['id'] .' ORDER BY `orderId` ASC');
		
		$lastOrdr = 0;
		$rslt .= '<ul>';
		if( sizeof( $bdy))
		{
			$perm = User::getPerm( $rw['mdId']);
			foreach( $bdy as $bd)
			{
				if( is_array( $perm) && empty( $perm[ $bd[ 'title'] ])) continue;

				$rslt .= $lastOrdr == $bd['orderId'] ? ' ' : '<br />';
				$rslt .= '<li><a href="'. $bd['link'] .'"><img src="../ext/admnMnu/'. $bd[ 'title'] .'.png" /> '. Lang::getval( $bd[ 'title']) .'</a></li>';
				$lastOrdr = $bd['orderId'];
				
			}//End of foreach( $bdy as $bd);

			unset( $perm);

		}//End of if( sizeof( $bdy));

		$rslt .= '</ul></li>';
		
	}//End of foreach( $rws as $rw);
	
	//<!-- profile & logout
	
		$rslt .= '
			<li>
				<a href="./?md=users&mod=edt"><img src="../ext/admnMnu/profileEdt.png" /> '. Lang::getVal( 'profileEdit') .'</a>
			</li>
			<li>
				<a href="./?md=users&mod=logout"><img src="../ext/admnMnu/logout.png" /> '. Lang::getVal( 'logout') .'</a>
			</li>
		';

	//-->
	
	$rslt .= '</ul>';
	
	//<!-- Toolbar...
	
		$SQL = 'SELECT * FROM `admin_menu`
			WHERE `inHomePg` != 0
			ORDER BY `inHomePg` ASC, `orderId` ASC';
	
		$rws = DB::load( $SQL);
	
		$rslt .='<br style="clear:both;" /><div class="h-mnu"><ul class="h-mnu">';

		is_array( $rws) or $rws = array();
		foreach( $rws as $rw)
		{
			if( !User::isAccess( $rw['mdId']))continue;

			$rslt .= '<li class="item">
						<a href="'. $rw[ 'link'] .'">
							<img src="../ext/admnMnu/'. $rw[ 'title'] .'.big.png" />
							'. Lang::getval( $rw[ 'title']) .'
						</a>
					</li>';

		}//End of foreach( $rws as $rw);

		$rslt .= '</ul></div>';

	//End of Toolbar-->

	//$rslt .= '<script>$("div.mnu div.title").click(function(){$(this).parent().children(".bdy").slideToggle("slow");});</script>';

	Cache::putFile( 'admin_menu_'. $_cfg['domain']['id'] .'_'. Lang::id() .'.'. @$_SESSION['groupId'], Cache::arrToSrc( $rslt));
	return $rslt;
?>
