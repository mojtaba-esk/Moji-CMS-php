<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	lib( array(
			'Tab',
			'File',
			'Img',
			'Addable',
		)
	);
	
	//<!-- Preaper Input Object ...
	
		$lngs = Lang::getAll();
		//require( $_cfg['path'] .'/inc/lib/Input.class.inc.php');
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$lngsIds[] = $lngs[ $i ][ 'id' ];
			$lngsTitle[ $lngs[ $i ][ 'id' ] ] = $lngs[ $i ][ 'title'];
		}
		$inpt = new Input( $lngsIds);

	//End of Preaper Input Object -->

	if( Module::$opt['imageFile'] || Module::$opt['attchmnt'])
	{
		$file = new File( Module::$name);
		Module::$opt['imageFile']	and	Img::setPrfx( Module::$name);
		Module::$opt['attchmnt']	and	$adbl = new Addable( Lang::$info, array( 'id' => 'hidden', 'attchmnt' => 'fileUpld', 'sp' => 'html'));
	}

	//<!-- Insert new rows
	
		if( $_GET['mod'] == 'new' && isset( $_POST['submit']))
		{
			$rltdId = $frstLngId = 0;
			while( $cols = $inpt -> getRow())
			{

				//IF The Required fields are empty, ignore the insertion action.
				if( !$cols['numOfSerials']) break;
				
				$cols['numOfSerials'] = intval( $cols['numOfSerials']);

				$iCols[ 'insrtTime'] = time();
				$iCols[ 'userId']	 = Session::$userId;
				empty( $cols['url']) or $iCols[ 'url'] = & $cols['url'];
				//empty( $cols['serial']) or $iCols[ 'serial'] = trim( $cols['serial']);

				$listOfSerials = array();

				for( $i = 0; $i != $cols['numOfSerials']; $i++)
				{

					$str = $i & 1 ? md5( microtime() . rand( 0, 999999999)) : strrev( md5( microtime() . rand( 0, 999999999)));
					rand( 0, 1) and $str = sha1( $str . microtime());
					$serial  = abs( crc32( $str));

					$serial .= '-';
					$serial .= abs( crc32( strrev( str_replace( array( '0.', ' '), array( '', '-'), microtime())))) * ( $cols['numOfSerials'] - $i );
					$serial .= '-';

					$str = $i & 1 ? strrev( sha1( microtime() . rand( 0, 999999999))) : sha1( microtime() . rand( 0, 999999999));
					rand( 0, 1) and $str = md5( microtime() . $str);
					$serial .= abs( crc32( $str));
					
					$listOfSerials[] = $serial;

					//printr( $serial);
					//continue;
					
					//S2
					$serial = strrev( sha1( strrev( md5( $serial))));
					//S3
					$iCols[ 'serial'] = md5( strrev( sha1( $serial) . crc32( $serial)));
					
					DB::insert( array(
							'tableName' => Module::$name . '_main',
							'cols' => & $iCols,
						)
						,  Module::$name /* Cache Prefix*/
					);

				}//End of for( $i = 0; $i != $cols['numOfSerials']; $i++);

				break;

			}//End of while( $cols = $inpt -> getRow());
			
			//printr( 'List of Serials...');
			//printr( $listOfSerials);
			
			if( sizeof( $listOfSerials) < 2)
			{
				$tpl -> display( 'header');
				printr( 'New serial: '. $listOfSerials[0]);
				return;
			}

			//<!-- Export the Serials as Excel file
			
				lib( array( 'ExcelWriter'));

				$path = dirname( __FILE__) .'/../files/';
				$fileName = 'serials-'. Date::get( 'Y-m-d-G.i', time()) .'.xls';

				if( !( $xsl = new ExcelWriter( $path . $fileName)))
				{
					defined( 'DEBUG_MODE') and printr( $xsl -> error);
					msgDie( $xsl -> error, NULL, 0, 'error');
				}

				foreach( $listOfSerials as $serial)
				{
					$xsl -> writeLine( array( $serial));

				}//End of foreach( $rws as $key => $rw);
				
				$xsl -> close();
				
				//<!-- Send Download Header

					@ob_clean();
					header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header( 'Content-Description: File Transfer');
					header( 'Content-Type: application/octet-stream');
					header( 'Content-Length: ' . filesize( $path . $fileName));
					header( 'Content-Disposition: attachment; filename="' . $fileName .'"');
					header( 'Content-Transfer-Encoding: binary');

					readfile( $path . $fileName);
					@unlink( $path . $fileName);

					exit();

				//-->	

			//End of Export-->

			return;

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End of Insert new rows -->

	$tpl -> set_filenames( array(
		'edit' => Module::$name .'.admin.edit',
		)
	);

	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,

		'SUBMIT' => Lang::getVal( 'submit'),
		'CANCEL' => Lang::getVal( 'cancel'),

		'RETURN_URL' => '?md='. Module::$name,

		)
	);
	
	//<!-- Prepare Form Elements...

		$form[] = $inpt -> hidden( 'id', $lngsIds /* can send an array of languages Ids*/);

		if( $_GET['mod'] == 'edt')
		{
			$form[] = $inpt -> html( 'insrtTime', Lang::numFrm( Date::get( 'D d M Y G:i', $inpt -> getVal( 'insrtTime'))));
			$form[] = $inpt -> html( 'actvTime', $inpt -> getVal( 'actvTime') ? Lang::numFrm( Date::get( 'D d M Y G:i', $inpt -> getVal( 'actvTime'))) : Lang::getVal( 'never'));
		}
		
		Module::$opt['categoryMod'] and $form[] = $inpt -> dropDown( 'catId', 0, array( 
																		'items'	=> getCats( Module::$name, Lang::viewId()), 
																		'dir'	=> & Lang::$info['dir'],
																		'align'	=> & Lang::$info['align']
																)
														);


		$form[] = $inpt -> text( 'numOfSerials', 0, array( 'class' => 'ltr', 'size' => 10, 'value' => 1));
		$form[] = $inpt -> text( 'url', 0, array( 'class' => 'ltr', 'size' => 40, 'autocomplete' => 'off'));
		//$form[] = $inpt -> text( 'lockSerial', 0, array( 'class' => 'ltr', 'size' => 40, 'autocomplete' => 'off'));

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
