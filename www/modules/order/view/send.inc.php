<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Sending The Form to Email;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	defined( 'DEBUG_MODE') and !isset( $inpt) and printr( 'Error! The Input object is not Set. [ $inpt ]');

	$cols = $inpt -> getRow();

	//<!-- Validate Security Captcah Code
	
		lib( array( 'Captcha', 'Session'));
		$sImg = new Captcha( Module::$opt['id']);
		
		if( strtolower( $cols['captcha']) != $sImg -> GetCode())
		{
			return Lang::getVal( 'wrongCaptcha');
		}
		$sImg -> Remove();//Remove the Sec-Code from System.
		
	//-->

	//<!-- Send Tries
	
		lib( array( 'SendTries'));
		$SendTry = new SendTry( Module::$opt['id'], 120);
		
		if( $SendTry -> CantTry())
		{
			return Lang::getVal( 'waitForXminutes', array( '{x}' => Lang::numFrm( 2)));
		}
	
	//-->

	//<!-- Make Mail Object 

		lib( array( 'Mail'));
		$ml = new Mail();

	//End of Make Mail Object -->

	//Email validation...
	if( !$ml -> isValidEmail( $cols['email']))
	{
		return Lang::getVal( 'validatorEmail');
	}
	
	$ml -> from( $cols['email']);
	
	//<!-- Find The Reciver Email...
	
		if( Module::$opt['emailsList'])
		{
				$SQL = 'SELECT `email` FROM `'. Module::$name .'_emails` WHERE `rltdId` = '. intval( $cols['emailId']);
				$rw = DB::load( $SQL, Module::$name .'_emails', 1);

		}else{

				$SQL = 'SELECT `email` FROM `'. Module::$name .'_main` WHERE 1';
				$rw = DB::load( $SQL, Module::$name .'_emails', 1);

		}//End of if( Module::$opt['emailsList']);

		$ml -> to( $rw[0]);

	//End of Find The Reciver Email-->

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

				'{SITE_URL}'	=> $_cfg['URL'],
				'{TODAY}'		=> Lang::numfrm( Date::get( 'D, d M Y G:i', time())),
				'{SUBJECT}'		=> $cols['subject'],
				'{NAME}'		=> $cols['name'],
				'{BODY}'		=> nl2br( $cols['body']),

			);

		$ml -> body( str_replace( array_keys( $vars), $vars, $mailBdy));

	//End of Prepare The Mail Body-->

	$ml -> subject( Module::$name .' ['. date( 'Y-M-d G-i') .']');
	//$ml -> attach( FILE_PATH, FILE_TYPE);
	$ml -> send();
	
	msgDie( Lang::getVal( 'msgSent'));
	return false;//Do not have any Message, Sending Successfull.

?>
