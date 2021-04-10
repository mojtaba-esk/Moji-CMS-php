<?php
defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

class Captcha
{
	private $Type;
	private $RandNum;

	/*------------------------------------------*/

	public function Captcha( $Type, $Generate = 0)
	{
		$this -> Type = $Type;
		$this -> RandNum = $Generate ? rand(1,999999999) : 0;

		$this -> Clear();//Clear the old sec codes of any user.
		$Generate and $this -> Save();//Save the security random code.
	}

	/*------------------------------------------*/
	
	private function Save()
	{
		$sid = Session::id();
		$Time = time();
		$SQL = "REPLACE INTO `captcha` VALUES ( '$sid', $Time, {$this -> RandNum}, {$this -> Type})";

		return DB::exec( $SQL);
	}
	
	/*------------------------------------------*/

	private function Read()
	{
		$sid = Session::id();
		$SQL = "SELECT `RndNum` FROM `captcha` WHERE `sid` = '$sid'  AND `Type` = {$this -> Type}";

		$rw = DB::load( $SQL, NULL, 1);
		return $this -> RandNum = $rw[0];
	}

	/*------------------------------------------*/
	
	public function GetCode()
	{
		$this -> RandNum or $this -> Read();
		return strtolower( substr( strrev( md5( strrev( $this -> RandNum))), 7, 5));
	}
	
	/*------------------------------------------*/

	private function Clear()
	{
		$Time = time() - 300;
		$SQL = "DELETE FROM `captcha` WHERE `LastTime` < $Time";

		return DB::exec( $SQL);
	}

	/*------------------------------------------*/
	
	public function Remove()
	{
		$sid = Session::id();
		$SQL = "DELETE FROM `captcha` WHERE `sid` = '$sid'  AND `Type` = {$this -> Type}";

		return DB::exec( $SQL);
	}	
	
	/*------------------------------------------*/

};
?>
