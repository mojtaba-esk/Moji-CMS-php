<?php
defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class SendTry
{
	private $Type;
	private $MaxTryTime;//In Second.
	private $LastTryTime = 0;//In Second.

	/*------------------------------------------*/

	public function SendTry( $Type, $MaxTryTime = 900, $dontAdd = false)
	{
		//$this -> Data = NULL;
		$this -> Type = $Type;
		$this -> MaxTryTime = $MaxTryTime;

		$this -> Clear();//Clear the last sends of any user.
		$this -> Read();//Read the LastTryTime.
		$dontAdd or $this -> Add();//Add this user to list.
	}

	/*------------------------------------------*/
	
	public function Add()
	{
		$IP = $_SERVER[ 'REMOTE_ADDR'];
		$Time = time();
		$SQL = "REPLACE INTO `send_wait` VALUES ( '$IP', 1, $Time, {$this -> Type})";

		return DB::exec( $SQL);
	}
	
	/*------------------------------------------*/

	private function Read()
	{
		$IP = $_SERVER[ 'REMOTE_ADDR'];
		$SQL = "SELECT `LastTryTime` FROM `send_wait` WHERE `IP` = '$IP'  AND `Type` = {$this -> Type}";

		$rw = DB::load( $SQL, 0, 1);
		$this -> LastTryTime = $rw[0];
	}

	/*------------------------------------------*/

	public function CantTry()//The user can not try . . . Must be wait...
	{
		return $this -> LastTryTime > time() - $this -> MaxTryTime;
	}
	
	/*------------------------------------------*/

	public function Clear( $rmAll = false)
	{
	
		$Time = time() - $this -> MaxTryTime;
		$SQL = "DELETE FROM `send_wait` WHERE `Type` = {$this -> Type}";
		$rmAll or $SQL .= " AND `LastTryTime` < $Time";

		return DB::exec( $SQL);
	}

	/*------------------------------------------*/

};
?>
