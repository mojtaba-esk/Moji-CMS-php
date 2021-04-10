<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	lib( array(
			'Input',
			'File',
			'Img',
			'Addable',
			'Search',
			'Session', // for the captcha
		)
	);
	
	$tpl -> display( 'header');
	
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

	$file = new File( Module::$name);
	Module::$opt['attchmnt']	and		$adbl = new Addable( Lang::$info, array( 'id' => 'hidden', 'attchmnt' => 'fileUpld', 'sp' => 'html'));

	//<!-- Insert new rows
	
		if( $_GET['mod'] == 'new' && isset( $_POST['submit']))
		{
			$rltdId = $frstLngId = 0;
			while( $cols = $inpt -> getRow())
			{
				//IF The Required fields are empty, ignore the insertion action.
				if( !$cols['body']) continue;
				
				//<!-- Validate Security Captcah Code
	
					lib( array( 'Captcha'));
					$sImg = new Captcha( Module::$opt['id']);
		
					if( strtolower( $cols['captcha']) != $sImg -> GetCode())
					{
						$msg = Lang::getVal( 'wrongCaptcha');
						break;
					}
					$sImg -> Remove();//Remove the Sec-Code from System.
		
				//-->
				
				//<!-- Send Tries
	
					lib( array( 'SendTries'));
					$SendTry = new SendTry( Module::$opt['id'], 120);
		
					if( $SendTry -> CantTry())
					{
						$msg = Lang::getVal( 'waitForXminutes', array( '{x}' => Lang::numFrm( 2)));
						break;
					}
	
				//-->

				//<!-- Make Mail Object 

					lib( array( 'Mail'));
					$ml = new Mail();

				//End of Make Mail Object -->

				//Email validation...
				if( !$ml -> isValidEmail( $cols['email']))
				{
					$msg = Lang::getVal( 'validatorEmail');
					break;
				}

				$iCols['title']	= $inpt -> dbClr( $cols['title']);
				$iCols['productId'] = & $_cfg['product']['id'];
				
				$iCols[ 'insrtTime'] = time();
				DB::insert( array(
						'tableName' => Module::$name . '_main',
						'cols' => & $iCols,
					)
				);
				$ticketId = DB::insrtdId();
				
				//<!-- Generate the ticket key...
				
					$uCols['key'] = sha1( md5( ( time() * $ticketId) .' '. rand( 0 , 9999) ) .'_'. $ticketId);

					DB::update( array(
							'tableName' => Module::$name . '_main',
							'cols' 	=> & $uCols,
							'where'	=> array(
								'id' => $ticketId,
							),
						)
					);					
					
				//-->
				
				//<!-- Add the ticket body...

					$pCols[ 'ticketId']		= & $ticketId;
					$pCols[ 'insrtTime']	= time();
					$pCols[ 'ip']			= & $_SERVER['REMOTE_ADDR'];
					$pCols[ 'body']			= $inpt -> dbClr( $cols['body']);
					
					DB::insert( array(
							'tableName' => Module::$name . '_posts',
							'cols' => & $pCols,
						)
					);
					$rltdId = DB::insrtdId();

				//-->

				//<!-- Upload The Attachements ...

					if( Module::$opt['attchmnt'])
					{
						require( 'attchmnt.inc.php');
					
					}//End of if( Module::$opt['attchmnt']);

				//End of Upload The Attachements-->

				//<!-- Save the Search key words... ( Indexing)
					
					isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
					$srch -> setIndexes( $iCols['title'] .' '. $pCols['body'] , $ticketId, $cols['lngId']);
					
				//End of Search-->

				//<!-- Sending mail...
				
					$ml -> to( $cols['email']);
					$ml -> from( 'info@'. str_replace( 'www.', '', $_SERVER['HTTP_HOST']));
					
					//<!-- Prepare The Mail Body
	
						$rw = DB::load( 
								array( 
									'tableName' => 'templates',
									'where' => array(
										'name' => Module::$name .'.mail.body',
									),
							),
							'tpl_'. Module::$name .'.mail.body'
						);
						$mailBdy = & $rw[0]['content'];
		
						$vars = array(

								'{SITE_URL}'	=> & $_cfg['URL'],
								'{TODAY}'		=> Lang::numfrm( Date::get( 'D, d M Y G:i', time())),
								//'{SUBJECT}'		=> 'new '. Module::$name,
								'{TITLE}'		=> & $iCols['title'],
								'{ITEM_URL}'	=> URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&mod=reply&key='. $uCols['key']),

							);

						//print( str_replace( array_keys( $vars), $vars, $mailBdy));//die();
						$ml -> body( str_replace( array_keys( $vars), $vars, $mailBdy));

					//End of Prepare The Mail Body-->

					$ml -> subject( 'new '. Module::$name .' ['. date( 'Y-M-d G-i') .']');
					//$ml -> attach( FILE_PATH, FILE_TYPE);
					$ml -> send();

				//End of Sending. -->

				break;

			}//End of while( $cols = $inpt -> getRow());

			if( empty( $msg))
			{
				msgDie( Lang::getVal( Module::$name .'Inserted'));//, './?md='. Module::$name, 1);
				return;
			}

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));

	//End of Insert new rows -->

	$tpl -> set_filenames( array(
		'edit' => Module::$name .'.view.new',
		)
	);

	// $msg = 'Gholi kochooolooo';
	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'submit'),
		'CANCEL' => Lang::getVal( 'cancel'),
		
		'RETURN_URL' => '?md='. Module::$name,
		
		'ACTION_TITLE' => Lang::getVal( $_GET['mod']),

		)
	);
	
	//<!-- Prepare Form Elements...

		$form[] = $inpt -> prValidate( 'form' /* HTML Form Id*/);

		$form[] = $inpt -> hidden( 'id', $lngsIds /* can send an array of languages Ids*/);

		$form[] = $inpt -> text( 'email', 0, array( 'class' => 'ltr', 'size' => 30, 'validate' => 'required email'));
		$form[] = $inpt -> text( 'title', 0, array( 'class' => & Lang::$info['dir'], 'size' => 40));
		$form[] = $inpt -> textArea( 'body', 0, array( 
						'class'	=> & Lang::$info['dir'],
						'cols'	=> 80,
						'rows'	=> 10,
						'validate' => 'required'
					)
			);

		//<!-- Prepare the Attachements Form . . .
		
			if( Module::$opt['attchmnt'])
			{

				$form[] = '<tr><td colspan="2"><fieldset><legend><img src="../etc/icn/attach.png" /> '. Lang::getVal( 'attachements') .' </legend><table>';

				for( $i = 0; $i != Module::$opt['attchmnt']; $i++)
				{
					$adbl -> add( array( 
							'id'			=> @$atchRws[ $i ][ 'id' ],
							//'lngId'		=> @$atchRws[ $i ][ 'lngId' ],
							'attchmnt'	=> @$atchRws[ $i ][ 'fileName' ],
							//'sp'			=> '<tr><td colspan="2"><hr /></td></tr>',
							'sp'			=> '<tr><td colspan="2"></td></tr>',
						)
					);

				}//End of for( $i = 0; $i != Module::$opt['attchmnt']; $i++);

				$form[] = $adbl -> getHTML();
				$form[] = '</table></fieldset></td><tr>';

			}//End of if( Module::$opt['attchmnt']);

		//End of Prepare the Attachements Form-->
		
		$form[] = $inpt -> captcha( 'captcha', 0);

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
