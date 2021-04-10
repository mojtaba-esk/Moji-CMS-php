<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	$ignrTbls = array( 
			'admin_menu',
			'captcha',
			'config_backup',
			'languages',
			'modules',
			'send_wait', 
			'sessions',
			'templates',
		);

	lib( array( 'File'));
	$file = new File( Module::$name);

	//<!-- Export the Data
	
		if( $_GET['mod'] == 'export' && isset( $_POST['submit']))
		{
		    $out =	"-- MySQL dump of database '{$_cfg['DB']['name']}' on host '{$_cfg['DB']['readUser']['host']}'\n";
		    $out .=	"-- Backup date and time: ". strftime( '%x %X', time()) ."\n";
		    $out .=	"SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n";
		    $out .=	"SET FOREIGN_KEY_CHECKS=0;\n";
		    $out .=	"SET NAMES 'utf8';\n";
		    
		    $tbls = DB::load( 'SHOW TABLE STATUS');
		    foreach( $tbls as $key => $rw)
			{

				if( in_array( $rw['Name'], $ignrTbls))
				{
					unset( $tbls[ $key ]);
					continue;
				}

				$cTbl = DB::load( 'SHOW CREATE TABLE `'. $rw['Name'] .'`');

				$out .= "DROP TABLE IF EXISTS `{$rw['Name']}`;\n";
				$out .=	$cTbl[0]['Create Table'] .";\n";

			}//End of foreach( $tbls as $key => $rw);
			
			//<!-- Fetch The data...

				foreach( $tbls as $tRw)
				{
					defined( 'DEBUG_MODE')	and $out .= "--\n-- Dumping data for table `{$tRw['Name']}`\n--\n\n";

					//$fTbl = DB::load( 'SHOW COLUMNS FROM `'. $rw['Name'] .'`');
					$rws = DB::load( 'SELECT * FROM `'. $tRw['Name'] .'`');
					$numflds = sizeof( $rws[0]);

					is_array( $rws) or $rws = array();
					foreach( $rws as $row)
					{
						$out .= 'INSERT INTO `'. $tRw['Name'] .'` VALUES( ';

						$i = 0;
						foreach( $row as $val)
						{
							//Replace the single quotation with two single quotation for solve the restore problem.
							//Replace the semicolon with semicolon + a space also, to solve the find end of query problem.
							
							$out .= is_null( $val) ? 'NULL' : "'". str_replace( array( "'", ';'), array( "''", '; '), $val) ."'";
							if( ++$i < $numflds) $out .= ',';

						}//End of foreach( $row as $val);
						
						$out .= ");\n";

					}//End of foreach( $fTbl as $rwF);

				}//End of foreach( $tbls as $rw);

				$out .=	"SET FOREIGN_KEY_CHECKS=1;\n";

			//-->

			//<!-- Send Download Header

				@ob_clean();
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header( 'Content-Description: File Transfer');
				header( 'Content-Type: application/octet-stream');
				//header( 'Content-Length: ' . $atchRw[ 'size']);
				header( 'Content-Disposition: attachment; filename="' . strftime( '%x-%H.%M') .'.sql"');
				header( 'Content-Transfer-Encoding: binary');
				print( $out);
				exit();

			//-->

		}//End of if( $_GET['mod'] == 'export' && isset( $_POST['submit']));

	//End of Export -->

	$tpl -> set_filenames( array(
		'edit' => $_GET['sub'] .'.'. Module::$name .'.sub.admin.export',
		)
	);

	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'EXPORT' => Lang::getVal( 'getBackup'),
		'CANCEL' => Lang::getVal( 'cancel'),
		
		'SUB_NAME' => Lang::getVal( $_GET['sub']),
		
		'RETURN_URL'	=> '?md='. Module::$name .'&sub='. $_GET['sub'],
		'MODULE_URL'	=> '?md='. Module::$name,
		)
	);

	$tpl -> display( 'edit');
?>
