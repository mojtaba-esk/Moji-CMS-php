<?php
/**
* @author Mojtaba Eskandari
* @since 2009-12-25
* @name User Authentication Class.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class User
{
	/*-----------------------------------------------*/
	
	static $info	 = NULL;
	static $msg		 = array();
	static $tblPrfx = 'admin_users';
	
	/*-----------------------------------------------*/

	public static function login( $user, $pass)
	{
		//<!-- Send Tries
	
		/*	lib( array( 'SendTries'));
			$SendTry = new SendTry( Module::$opt['id'], 3);
		
			if( $SendTry -> CantTry())
			{
				self::$msg[] = Lang::getVal( 'waitForXseconds', array( '{x}' => Lang::numFrm( 3)));
				return false;
			}

			$SendTry -> Clear( true); // Force Clear all
		/**/

		//-->

		//<!-- Load The user information

			$SQL = '
				SELECT
					`u`.*,
					`g`.`permission`
				FROM
					`'. self::$tblPrfx .'_main`		AS	`u`,
					`'. self::$tblPrfx .'_groups`	AS	`g`
				WHERE
					`u`.`username` = \''. self::clean( $user) .'\'
					AND
						`u`.`groupId` = `g`.`id`
				LIMIT 1
			';

			$rws = DB::load( $SQL);
			
		//-->
		
		if( !$rws)
		{
			self::$msg[] = Lang::getVal( 'userAndPassWrong');
			return false;
		}
		
		$rw = & $rws[0];
		
		if( $rw['password'] != self::hash( $pass, $rw['regDate']))
		{
			self::$msg[] = Lang::getVal( 'userAndPassWrong');
			return false;
		}

		if( !$rw['active'])
		{
			self::$msg[] = Lang::getVal( 'userNotActive');
			return false;
		}
		
		Session::$userId = $rw['id'];
		$_SESSION['permission'] = unserialize( $rw['permission']);
		$_SESSION['groupId'] = $rw['groupId'];
		
		//<!-- Update The Last login informations
		
			$SQL = '
				UPDATE
					`'. self::$tblPrfx .'_main`
				SET
					`lastLoginDate`	= `loginDate`,
					`lastLoginIP`	= `loginIP`,
					`loginIP`		= \''. $_SERVER['REMOTE_ADDR'] .'\',
					`loginDate`		=	'. time() .'
				WHERE
					`id` = '. $rw['id'] .'
				LIMIT 1
				';
			DB::exec( $SQL);
			
		//-->
		
		return true;
	}
	
	/*-----------------------------------------------*/
	
	public static function clean( & $str)
	{
		return preg_replace( '([^a-z0-9_]*)', '', strtolower( $str));
	}

	/*-----------------------------------------------*/

	public static function hash( & $pass, & $enc)
	{
		return strrev( sha1( strrev( md5( self::$tblPrfx . md5( $enc)) . strrev( sha1( $pass)))));
	}

	/*-----------------------------------------------*/

	public static function load( $id = 0)
	{
		$id or $id = Session::$userId;

		$SQL = '
			SELECT
				`u`.*,
				`g`.`permission`
			FROM
				`'. self::$tblPrfx .'_main`		AS	`u`,
				`'. self::$tblPrfx .'_groups`	AS	`g`
			WHERE
					`u`.`id` = '. $id .'
				AND
					`u`.`groupId` = `g`.`id`
			LIMIT 1
		';
		$rws = DB::load( $SQL);

		isset( $rws[0]) or $rws[0] = NULL;
		self::$info = $rws[0];
	}

	/*-----------------------------------------------*/
	
	public static function isLogdIn()
	{
		return Session::$userId;
	}

	/*-----------------------------------------------*/
	
	public static function isAccess( $md)
	{
		return isset( $_SESSION['permission'][$md]);
	}

	/*-----------------------------------------------*/

	public static function getPerm( $md)
	{
		return @$_SESSION['permission'][$md];
	}

	/*-----------------------------------------------*/

	public static function logout()
	{
		unset( $_SESSION['permission']);
		//return Session::destroy();
		return session_destroy();
	}

	/*-----------------------------------------------*/

}//End of class User;
?>
