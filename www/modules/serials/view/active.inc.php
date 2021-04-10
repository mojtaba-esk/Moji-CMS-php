<?php
/*
* Author: Mojtaba Eskandari
* Started at 2011-04-02
* Active mode for the products.
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	lib( array(
			'Input',
			'Session',
		)
	);

	$tpl -> display( 'header');

	$tpl -> set_filenames( array(
		'body' => Module::$name .'.view.'. $_GET['mod'],
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
	
	if( isset( $_POST['submit']))
	{
		while( $cols = $inpt -> getRow())
		{
			$ref = & $cols['host'];

			//<!-- Validate Security Captcah Code

				lib( array( 'Captcha'));
				$sImg = new Captcha( Module::$opt['id']);
	
				if( strtolower( $cols['captcha']) != $sImg -> GetCode())
				{
					$msg = Lang::getVal( 'wrongCaptcha');
					break;
				}
				$sImg -> Remove();//Remove the Sec-Code from System.
				
				/**/
	
			//-->

			$cipher = base64_decode( $_GET['a']); // H1
			$iv 	= base64_decode( $_GET['b']); // iv for Decryption

			//<!-- Find the Key...

				if( empty( $ref) && defined( 'DEBUG_MODE'))
				{
					printr( 'Err: Refer is not set! Please Click on the link in your admin panel.');
					die();
				}

				$url = parse_url( $ref);
				$hostUrl = str_replace( 'www.', '', $url['host']);

				if( isset( $_GET['c'])) // Hardware Lock
				{
					$hostUrl = base64_decode( $_GET['c']);

				}//End of if( isset( $_GET['c']));
				
				$key = md5( sha1( strrev( md5( $hostUrl))));
				
			//End of Key-->

			//S2
			$serial = mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $key, $cipher, MCRYPT_MODE_CFB, $iv);
			//printr( 'S2: '. $serial);

			//S3
			$serial = md5( strrev( sha1( $serial) . crc32( $serial)));
			//printr( 'S3: '. $serial);
			
			$actvTime = time();

			//<!-- Search S3 in DB..

				$rw = DB::load(
					array( 
						'tableName' => Module::$name . '_main',
						'where' => array(
							'serial'	=> & $serial,
							'actvTime'	=> 0, //Not activated yet.
						),
					)
				);
				
				if( !$rw || ( !empty( $rw[0]['url']) && $rw[0]['url'] != $hostUrl))
				{
					msgDie( Lang::getVal( 'wrongSerial'), NULL, 0, 'error');
					return;
				}

				//Save This URL into DB

				DB::update( array(
						'tableName' => Module::$name . '_main',
						'cols' 	=> array( 
								'url'		=> & $hostUrl,
								'actvTime'	=> $actvTime,
							),
						'where'	=> array(
							'id' => & $rw[0]['id'],
						),
					)
				);

			//-->

			//<!-- Generate part A1 of the Activition Code...

				$url = parse_url( $ref);
				//$hostUrl = str_replace( 'www.', '', $url['host']);

				//A1
				$actv1 = sha1( md5( $hostUrl) . $actvTime . strrev( $serial));
	
			//End of Generate part A1 of the Activition Code.-->

			//<!-- Generate part B1 of the Activition Code...

				$B1 = $actv1;
				$U1 = strrev( md5( $hostUrl));
	
				$D1 = array();
				for( $i = 0; $i != 32; $i++)
				{
					$D1[ $i ] = abs( ord( $U1[ $i ]) - ord( $serial[ $i ]));
				}
				arsort( $D1);
				//printr( $D1);die();
	
				$i = 0;
				$actvTimeStr = $actvTime .'';
				$timeLen = strlen( $actvTimeStr);
				foreach( $D1 as $indx => $foo)
				{
					$B1[ $indx ] = $actvTimeStr[ $i++ ];
					if( ! --$timeLen) break;
				}
	
				//printr( 'actv1:'. $actv1);
				//printr( 'B1: &nbsp;&nbsp;'. $B1);
				//printr( 'actvTime: '. $actvTime);
				//$timeLen = strlen( $actvTimeStr);
				//$actvTimeStr = '';
				//foreach( $D1 as $indx => $foo)
				//{
				//	$actvTimeStr .= $B1[ $indx ];
				//	if( ! --$timeLen) break;
				//}
	
				//printr( 'actvTime: '. $actvTimeStr);
	

			//End of Generate part B1 of the Activition Code.-->


			//<!-- Prepare the activation link...

				$url = parse_url( $ref);
				//$hostUrl = str_replace( 'www.', '', $url['host']);

				$text = $actv1 . $B1;
				$key  = md5( strrev( sha1( md5( $hostUrl))));

				$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB), MCRYPT_RAND);
				$a  = base64_encode( mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_CFB, $iv));


			//-->
			
			$actUrl = $ref .'&a='. urlencode( $a) .'&b='. urlencode( base64_encode( $iv));

			msgDie( Lang::getVal( 'activationDone'), $actUrl, 5, 'info', Lang::getVal( 'activation'));
			return;

			break;

		}//End of while( $cols = $inpt -> getRow());

	}//End of if( isset( $_POST['submit']));


	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'doActive'),
		
		//'RETURN_URL' => '?md='. Module::$name,
		
		'ACTION_TITLE' => Lang::getVal( 'activation'),

		)
	);

	isset( $ref) or $ref = $_SERVER['HTTP_REFERER'];
	$url = parse_url( $ref);

	$form[] = $inpt -> html( 'url', $url['host']);
	$form[] = $inpt -> hidden( 'host', 0, $ref);
	$form[] = $inpt -> captcha( 'captcha', 0);

	for( $i = 0; $i != sizeof( $form); $i++)
	{
		$tpl -> assign_block_vars( 'myblck',  array(
				'INPUT' => & $form[ $i]
			)
		);
	}

	$tpl -> display( 'body');
?>
