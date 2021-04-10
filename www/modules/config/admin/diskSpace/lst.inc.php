<?php
/**
* @author Mojtaba Eskandari
* @since 2009-12-24
* @name Module Admin Panel. Clean The Cache;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	//$startTime = time();
	
	$tpl -> set_filenames( array(
		'body' => $_GET['sub'] .'.sub.admin.list',
		)
	);
	
	$SQL = 'SELECT `name` FROM `modules`';
	$mdLst = DB::load( $SQL);
	
	$mdLst or $mdLst = array();
	foreach( $mdLst as $rw)
	{
		$path = dirname( __FILE__) .'/../../../'. $rw['name'] .'/files/';
		
		if( !file_exists( $path)) continue;
		$mdArr[ $rw['name'] ] = $path;

	}//End of foreach( $mdLst as $rw);
	
	$mdArr[ 'filesCache' ]	= dirname( __FILE__) .'/../../../../cache/files/';
	$mdArr[ 'etcCache' ]	= dirname( __FILE__) .'/../../../../cache/ext/';
	
	$totalSize = $key = 0;
	foreach( $mdArr as $name => $path)
	{
	
		$mdSize = 0;
		
		$files = glob( $path .'*');
		$files or $files = array();
		
		foreach( $files as $file)
		{
			/*if( time() - $startTime > 25)
			{
				msgDie( Lang::getVal( 'purgeInProgress') .'...', './'. getUrl(), 1);
				return;
			}/**/
			
			$mdSize += filesize( $file);

		}//End of foreach( $files as $file);
		
		$totalSize += $mdSize;
		
		$tpl -> assign_block_vars( 'myblck',  array(

				'MD_NAME'	=> Lang::getVal( $name),
				'MD_SIZE'	=> dcFSize( $mdSize),
				'RW_ODD'	=> $key ^= 1,

			)
		);
		
	}//End of foreach( $mdArr as $name => $path);

	//<!-- Calculate The DataBase Size...

		$SQL = 'SHOW TABLE STATUS';
		$rws = DB::load( $SQL);

		$dbSize = 0;
		$rws or $rws = array();
		foreach( $rws as $rw)
		{
			$dbSize += $rw['Data_length'] + $rw['Index_length'];

		}//End of foreach( $rws as $rw);
		
		$totalSize += $dbSize;
		
		$tpl -> assign_block_vars( 'myblck',  array(

				'MD_NAME'	=> Lang::getVal( 'database'),
				'MD_SIZE'	=> dcFSize( $dbSize),
				'RW_ODD'	=> $key ^= 1,

			)
		);

	//End of Calculate The DataBase Size.-->

	$tpl -> assign_vars( array(

			'L_TOTAL_SIZE' => Lang::getVal( 'totalSize'),
			'TOTAL_SIZE'	=> dcFSize( $totalSize),
			'SUB_NAME'	=> Lang::getVal( $_GET['sub']),

		)
	);

	$tpl -> display( 'body');
?>
