<?php
/**
* @author Mojtaba Eskandari
* @since 2009-12-24
* @name Session Class.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class Session implements \SessionHandlerInterface
{
	public static $lifeTime	= 900;//15 Minutes
	public static $userId	= 0;

	/*-----------------------------------------------*/
	
	public function open( $p, $n)
	{
		return true;
	}
	
	/*-----------------------------------------------*/
	
	public function close()
	{
		return true;
	}

	/*-----------------------------------------------*/

	public static function id()
	{
		return session_id();
	}

	/*-----------------------------------------------*/

	private function secStr()
	{
		return sha1( strrev( md5( $_SERVER['REMOTE_ADDR'])) . self::id());
	}
	
	/*-----------------------------------------------*/
	
	public function read( $id)
	{
		$rw = DB::load( 'SELECT 
							`userId`,
							`secStr`,
							`val`
						FROM
							`sessions`
						WHERE
							`id` = \''. $id .'\'
							AND
								`secStr` = \''. self::secStr() .'\'
						');

		if( !$rw) return '';

		self::$userId = $rw[0]['userId'];
		return $rw[0]['val'];
	}

	/*-----------------------------------------------*/

	public function write( $id, $val)
	{
		$cols = array(
			'id'		=>		& $id,
			'expTime'	=>		time() + self::$lifeTime,
			'userId'	=>		& self::$userId,
			'secStr'	=>		self::secStr(),
			'val'		=>		& $val,
		);
		
		DB::$forcCnn = true;//Must be reset to false later.

		$SQL = "
			SELECT
				COUNT(*) AS `total`
			FROM
				`sessions`
			WHERE
				`id` = '{$cols['id']}'
				AND
					`secStr` = '{$cols['secStr']}'";

		$Rw	= DB::load( $SQL, false, true);
		
		if( !$Rw[0])
		{
			return DB::insert( array(
					'tableName' => 'sessions',
					'cols' => & $cols,
				)
			);

		}
		
		return DB::update( array(
				'tableName' => 'sessions',
				'cols' 	=> & $cols,
				'where'	=> array(
					'id'		=> & $cols['id'],
					'secStr'	=> & $cols['secStr'],
				),
			)
		);
	}

	/*-----------------------------------------------*/

	public function destroy( $id = NULL)
	{
		$id or $id = session_id();

		$SQL = "DELETE FROM `sessions` WHERE `id` = '$id'";

		DB::$forcCnn = true;//Must be set to false later.
		
		$rs = DB::exec( $SQL);
		
		DB::$forcCnn = false;

		return $rs;

		//return DB::exec( $SQL);
	}

	/*-----------------------------------------------*/

	public function gc( $expTm)
	{
		$SQL = 'DELETE FROM `sessions` WHERE `expTime` < '. time();
		
		DB::$forcCnn = true;//Must be reset to false later.
		
		$rs = DB::exec( $SQL);
		
		DB::$forcCnn = false;

		return $rs;
	}

	/*-----------------------------------------------*/

}//End of class MySession;

$scHandler = new Session();
session_set_save_handler( $scHandler, true);

/*session_set_save_handler( 
	array( 'Session', 'open'),
	array( 'Session', 'close'),
	array( 'Session', 'read'),
	array( 'Session', 'write'),
	array( 'Session', 'destroy'),
	array( 'Session', 'gc')
);
/**/
session_name( 'SID');
session_save_path('/tmp');
session_start();

?>
