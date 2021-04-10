<?php
/**
* @author: Mojtaba Eskandari
* @since 2009-08-27
* @name Load the domain Information.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

//<!-- Fetch Domain information...

	$hostName = str_replace( 'www.', '', $_SERVER['HTTP_HOST']);

	$SQL = "SELECT
				`rltdId`	AS	`id`,
				`title`,
				`tmpId`,
				`planId`,
				`parkedOn`,
				`statusId`,
				`options`
			FROM
				`domains_main`
			WHERE
				`name` = '$hostName'
				AND
					`lngId` = 0". Lang::id();
	
	$rw = DB::load( $SQL);
	$_cfg['domain'] = $rw[0];
	
	if( !$_cfg['domain'])
	{
		if( defined( 'DEBUG_MODE'))
		{
			printr( 'This domain is Not Registered!');
			exit();
		}
		notFound();

	}//End of if( !$_cfg['domain']);
	
	
	//<!-- Handle the parked domains for multiple domins for a single website...

		empty( $_cfg['domain']['parkedOn']) or $_cfg['domain']['id'] = $_cfg['domain']['parkedOn'];
		unset( $_cfg['domain']['parkedOn']);

	//-->
	
	$_cfg['domain']['options'] = unserialize( $_cfg['domain']['options']);

	/*Cache::putFile( 'www.'. $hostName, $tmp = '<?php return '. $_cfg['pId'] .';?>');*/
	Cache::putFile( 'www.'. $hostName, Cache::arrToSrc( $_cfg['domain'] ));

	unset( $rw);

//-->
?>