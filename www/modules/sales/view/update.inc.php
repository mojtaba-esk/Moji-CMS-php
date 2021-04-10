<?php
/*
* Author: Mojtaba Eskandari
* Started at 2012-09-07
* Update log operations...
*/
	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	lib( array(
			'Input',
			'Session',
		)
	);
	
	isset( $_GET['action']) or die( 'Actn Err!');
	
	if( $_GET['action'] == 'lastVer')
	{
	
		//<!-- Finding the last version of the current product...
		
			$SQL = "SELECT
						`title` AS	`ver` 
					FROM
						`products_versions` 
					WHERE
						`testMode` != 1
						AND
							`itemId` = {$_cfg['product']['id']}
					ORDER BY
						`id` DESC
					LIMIT	1";
			
			$verRw = DB::load( $SQL);
			
			if( isset( $verRw[0]['ver']))
			{
				print( $verRw[0]['ver']);
			
			}else{
			
				print( '0');

			}//End of if( isset( $verRw[0]['ver']));
			
			//printr( $verRw);
			
			exit();// Do not show the footer.
		
		//-->
	
	}// End of if( $_GET['action'] == 'lastVer');
	
	
	//---------------------------------------
	
	if( $_GET['action'] == 'add')
	{
	
		//<!-- Finding versionId...
		
			$SQL = "SELECT
					`id` AS	`verId`
				FROM
					`products_versions` 
				WHERE
					`itemId` = {$_cfg['product']['id']}
					AND
						`title` = '{$_GET['ver']}'";

			$verRw = DB::load( $SQL);
			empty( $verRw) and die( '0');
			
			$verId = $verRw[0]['verId'];
		
		//-->

		//<!-- Finding customerId...

			$lockSerial = preg_replace( '([^0-9\-]*)', '', $_GET['serial']);

			$SQL = "SELECT
					`id` AS	`cuId`
				FROM
					`". Module::$name . "_main` 
				WHERE
					`lockSerial` = '{$lockSerial}'";

			$cuRw = DB::load( $SQL);
			empty( $cuRw) and die( '0');
			
			$cuId = $cuRw[0]['cuId'];
		
		//-->
		
		//<!-- Saving the update log...

			$iCols['cuId']			=	$cuId;
			$iCols['updateTime']	=	time();
			$iCols['ip']			=	& $_SERVER['REMOTE_ADDR'];
			$iCols['verId']			=	$verId;

			DB::insert( array(
					'tableName' => Module::$name . '_update_logs',
					'cols' 	=> & $iCols,
				)
			);			

		//-->
		
		print( '1');

		exit();// Do not show the footer.

	}// End of if( $_GET['action'] == 'add');
	
	exit();// Do not show the footer.
?>
