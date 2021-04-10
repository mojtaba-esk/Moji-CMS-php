<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	//<!-- Preaper Input Object ...
	
		$lngs = Lang::getAll();
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$lngsIds[] = $lngs[ $i ][ 'id' ];
			$lngsTitle[ $lngs[ $i ][ 'id' ] ] = $lngs[ $i ][ 'title'];
		}
		$inpt = new Input( $lngsIds);

	//End of Preaper Input Object -->	
	
	lib( array( 'File', 'Img'));
	$file = new File( Module::$name);
	
	if( isset( $_POST['submit']))
	{
		$files = $inpt -> getFiles();
		if( empty( $files[ 'backupFile'][ 'tmp_name'])) return;
		$file -> save( 0, $files[ 'backupFile'][ 'tmp_name'], 'bak.'. $_GET['sub'] .'.');
		
		$_SESSION['import'] = 0;	//	Number of Excecuted Queries, in import process...
		
	}//End of if( isset( $_POST['submit']));
	
	 //<!-- Import Process...

		if( isset( $_SESSION['import']))
		{
			DB::exec( 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";');
			DB::exec( 'SET FOREIGN_KEY_CHECKS=0;');
			
			$start = 0;			// Skip the executed queries, in last run.
			$sTime = time();	// Start time

			$fp = fopen( $file -> getPth( 0, 0, 'bak.'. $_GET['sub'] .'.'), 'r');
			$qry = '';
			
			while( !feof( $fp))
			{
				$itms = fscanf( $fp, "%[^\n]s\n");
				$item = & $itms[0];
				
				if( !$item || $item[0] == '-' && $item[1] == '-') continue; // skip the comments and empty lines

				$qry .= $item;
				if( substr( $item, -1, 1) == ';')//End of Query
				{
					if( time() - $sTime > 20)	// Break time
					{
						$_SESSION['import'] = $start;

						msgDie( Lang::getVal( 'toBeContinue'), './'. URL::get(), 1, 'info', Lang::getVal( 'continue'));
						return;

					}//End of if( time() - $sTime > 20);

					if( ++$start > $_SESSION['import'])
					{
						DB::exec( str_replace( '; ', ';', $qry), 2 /* Restore mode, high privilege */);
					}
					$qry = '';

				}//End of if( substr( $item, -1, 1) == ';');
			
			}//End of while( !feof( $fp));

			//<!-- Clearing the Cache and so on...

				Cache::clean( '', ''); // Clean the all items in cache
				$file -> delete( 0, 0, 'bak.'. $_GET['sub'] .'.');
				unset( $_SESSION['import']);

				DB::exec( 'SET FOREIGN_KEY_CHECKS=1;');

			//End of Clearing the Cache. -->

			msgDie( Lang::getVal( 'imported'), NULL, 0);
			return;

		}//End of if( isset( $_SESSION['import']));
		
	//End of Import Process. -->
	

	$tpl -> set_filenames( array(
		'edit' => $_GET['sub'] .'.'. Module::$name .'.sub.admin.import',
		)
	);

	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'import'),
		'CANCEL' => Lang::getVal( 'cancel'),
		
		'SUB_NAME' => Lang::getVal( $_GET['sub']),
		
		'RETURN_URL'	=> '?md='. Module::$name .'&sub='. $_GET['sub'],
		'MODULE_URL'	=> '?md='. Module::$name,
		)
	);
	
	//<!-- Prepare Form Elements...

		$form[] = $inpt -> fileUpld( 'backupFile');

		for( $i = 0; $i != sizeof( $form); $i++)
		{
			$tpl -> assign_block_vars( 'myblck',  array(
					'INPUT' => & $form[ $i]
				)
			);
		}

	//End of Prepare Form Elements, and sent to Template-->	

	$tpl -> display( 'edit');
?>
