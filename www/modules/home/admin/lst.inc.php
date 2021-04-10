<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Admin Panel Home Page.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	$SQL = 'SELECT * FROM `admin_menu`
		WHERE `inHomePg` != 0
		ORDER BY `inHomePg` ASC, `orderId` ASC';
	
	$rws = DB::load( $SQL, 'admin_menu');
	
	is_array( $rws) or $rws = array();

	foreach( $rws as $rw)
	{
			if( !User::isAccess( $rw['mdId']))continue;
			$tpl -> assign_block_vars( 'mnublck',  array(

						'TITLE'		=>	Lang::getval( $rw[ 'title']),
						'URL'			=>	$rw[ 'link'],
						'IMG_SRC'	=>	'../ext/admnMnu/'. $rw[ 'title'] .'.big.png',
					)
			);

	}//End of foreach( $rws as $rw);
	
	//<!-- Count the new Private Messages...

		/* $SQL = '
			SELECT
				COUNT( *)	AS	`total`
			FROM
				`pm_main`	AS	`m`
			WHERE
					`m`.`recieverId` = '. Session::$userId .'
				AND
					`m`.`removed` = 0
				AND
					`m`.`read` = 0';
		$pmRw = DB::load( $SQL); /**/
				
	//End of Counting.-->

	User::load();
	$tpl -> assign_vars( array(
		
		'L_LAST_LOGIN_DATE'	=> Lang::getVal( 'lastLoginDate'),
		'L_LAST_LOGIN_IP'	=> Lang::getVal( 'lastLoginIP'),
		
		'LAST_LOGIN_DATE'	=> User::$info['lastLoginDate'] ? Lang::numfrm( Date::get( 'D, d M Y G:i', User::$info['lastLoginDate'])) : '---',
		'LAST_LOGIN_IP'		=> User::$info['lastLoginIP'],
		
		//'NUM_OF_NEW_PMS'	=> $pmRw[0]['total'] ? Lang::numfrm( $pmRw[0]['total'] ) : 0,
		//'L_NEW_PMS'			=> Lang::getVal( 'newPms'),

		)
	);

	$tpl -> set_filenames( array( 'body' => Module::$name .'.admin'));
	//$tpl -> display( 'header');
	$tpl -> display( 'body');
?>
