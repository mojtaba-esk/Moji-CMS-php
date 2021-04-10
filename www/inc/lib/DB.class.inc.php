<?php
/*
* Author: Mojtaba Eskandari
* Started at 2009-02-06
* DataBase Class.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class DB
{
	static $sqlArr = NULL;//For Debug Mode;
	static $cachArr = NULL;//For Debug Mode;
	static $forcCnn = false;//Force Connetc for any Query

	private static function conn( $wrtMod = 0 /* write Mode */)
	{
		global $_cfg;
		static $rdLnk	= NULL;//Read Link
		static $wrtLnk	= NULL;//Write Link

		static $utfW	= NULL;//UTF-8 Query is Runned on Write Mode Connection.
		static $utfR	= NULL;//UTF-8 Query is Runned on Read Mode Connection.

		self::$forcCnn and $rdLnk = $utfR = $wrtLnk = $utfW = NULL;
		
		if( !$wrtMod)	//Read user
		{
			$rdLnk	or $rdLnk = new mysqli( $_cfg['DB']['host'], $_cfg['DB']['readUser']['user'], $_cfg['DB']['readUser']['pass'], $_cfg['DB']['name']);
			$utfR	or $utfR = $rdLnk -> query( "SET NAMES 'utf8'");
			//unset( $_cfg['DB']['readUser']);
			return $rdLnk;
		
		}elseif( $wrtMod = 2 ){ //Backup User

			$wrtLnk	or $wrtLnk = new mysqli( $_cfg['DB']['host'], $_cfg['DB']['bckupUser']['user'], $_cfg['DB']['bckupUser']['pass'], $_cfg['DB']['name']);
			$utfW	or $utfW = $wrtLnk -> query( "SET NAMES 'utf8'");
			return $wrtLnk;

		}//End of elseif( $wrtMod = 2 );

		$wrtLnk	or $wrtLnk = new mysqli( $_cfg['DB']['host'], $_cfg['DB']['writeUser']['user'], $_cfg['DB']['writeUser']['pass'], $_cfg['DB']['name']);
		$utfW	or $utfW = $wrtLnk -> query( "SET NAMES 'utf8'");
		//unset( $_cfg['DB']['writeUser']);
		return $wrtLnk;
	}

	/*----------------------------------------------*/

  /**
	* 
	*/
	public static function load( $opt, $cachePrfx = NULL, $singleFld = false)
    {
		if( is_array( $opt))
		{
			$SQL = "SELECT ";

			if( isset( $opt['cols']))
			{
				foreach( $opt['cols'] as $field)
				{
					$SQL .= " `$field`,";
				}
				$SQL = substr( $SQL, 0, -1);

			}else{

				$SQL .= '*';

			}//End of if( isset( $opt['cols']));

			$SQL .= " FROM `{$opt['tableName']}` WHERE 1 ";

			if( isset( $opt['where']) && is_array( $opt['where']))
			{
				foreach( $opt['where'] as $key => $val)
				{
					$SQL .= " AND `$key` = '$val' ";
				}
			}
		
		}else{

			$SQL = $opt;

		}//End of if( is_array( $opt));

		defined( 'DEBUG_MODE') and $strtTime = microtime();
		
		//<!-- Load From Cache...

			if( $cachePrfx)
			{
				is_string( $cachePrfx) or isset( $opt['tableName']) and $cachePrfx = $opt['tableName'];
				$cacheFileName = $cachePrfx . md5( $SQL);
				if( $rws = Cache::getData( $cacheFileName))
				{
					if( defined( 'DEBUG_MODE'))
					{
						$timeLnt = microtime() - $strtTime;
						DB::$cachArr[] = array( 'SQL' => $SQL, 'RowsCount' => count( $rws), 'Time' => $timeLnt , 'RetivedFromCache' => 'true' , 'CacheFileName' => $cacheFileName);
					}
					return $rws;
				}
			}

		//End of Load From Cache-->
		
		$cnn  = DB::conn();
		
		$rslt = $cnn -> query( $SQL);
		
		defined( 'DEBUG_MODE') and $sqlTimeLnt = microtime() - $strtTime;
		
		$rslt or defined( 'DEBUG_MODE') and printr( array( 'SQL' => $SQL, 'Error' => $cnn -> error));

		$rws  = NULL;

		if( $singleFld)
		{

			while( $rslt && $rw = $rslt -> fetch_array())
			{
				$rws[] = $rw[0];
			}

		}else{

			//$opt['indx'] = '';
			while( $rslt && $rw = $rslt -> fetch_assoc())
			{
				//$rws[ $rw[ $opt['indx'] ] ] = $rw;
				$rws[] = $rw;
			}
		
		}//End of if( $singleFld);
		
		$rslt && $rslt -> free();
		
		//<!-- Save in Cache...

			if( $cachePrfx)
			{
				Cache::putFile( $cacheFileName, Cache::arrToSrc( $rws));
			}

		//End of Save in Cache-->
		
		if( defined( 'DEBUG_MODE'))
		{
			$totalTimeLnt = microtime() - $strtTime;
			DB::$sqlArr[] = array( 'SQL' => $SQL, 'RowsCount' => @sizeof( $rws), 'SQLTime' => $sqlTimeLnt , 'Total Time( SQL & Fetch Rows & Save in Cache )' => $totalTimeLnt , 'Error' => $cnn -> error);
		}
		return $rws;

    }//End of function load( $opt, $cachePrfx);

	/*----------------------------------------------*/
	
	public static function exec( $SQL, $wrtMod = 1)
	{
		$cnn = DB::conn( $wrtMod);
		
		defined( 'DEBUG_MODE') and $strtTime = microtime();
		
		$rslt = $cnn -> query( $SQL);
		
		defined( 'DEBUG_MODE') and $timeLnt = microtime() - $strtTime;
		
		$rslt or defined( 'DEBUG_MODE') and printr( array( 'SQL' => $SQL, 'Error' => $cnn -> error));

		defined( 'DEBUG_MODE') and DB::$sqlArr[] = array( 'SQL' => $SQL, 'AffectedRows' => DB::affctdRws(), 'Time' => $timeLnt , 'Error' => $cnn -> error);
		
		return;
	}
	
	/*----------------------------------------------*/

	public static function affctdRws()
	{
		$cnn = DB::conn( 1);
		return $cnn -> affected_rows;
	}

	/*----------------------------------------------*/
	
	public static function insert( $opt = array(), $cachePrfx = NULL)
	{
		$SQL = "INSERT INTO `{$opt['tableName']}` SET ";

		foreach( $opt['cols'] as $field => $value)
		{
			$SQL .= " `$field` = '$value',";
		}
		$SQL = substr( $SQL, 0, -1);
		
		DB::exec( $SQL);
		
		//<!--Clean the Cache...

			if( $cachePrfx)
			{
				is_string( $cachePrfx) or isset( $opt['tableName']) and $cachePrfx = $opt['tableName'];
				Cache::clean( $cachePrfx);
				defined( 'DEBUG_MODE') and DB::$cachArr[] = array( 'SQL' => $SQL, 'DELETE FROM Cache' => 'true' , 'Cache Prefix' => $cachePrfx);
			}

		//End of Clean the Cache-->
		
		defined( 'DEBUG_MODE') and DB::$sqlArr[ sizeof( DB::$sqlArr) -1]['inserted Id'] = DB::insrtdId();
		
		return DB::insrtdId();
	}
	
	/*----------------------------------------------*/
	
	public static function insrtdId()
	{
		$cnn = DB::conn( 1);
		return $cnn -> insert_id;
	}

	/*----------------------------------------------*/
	
	public static function update( $opt = array(), $cachePrfx = NULL)
	{
		
		$SQL = "UPDATE `{$opt['tableName']}` SET ";

		foreach( $opt['cols'] as $field => $value)
		{
			$SQL .= " `$field` = '$value',";
		}
		$SQL = substr( $SQL, 0, -1);
		
		//<!-- Where clause

			$SQL .= " WHERE 1 ";
			if( isset( $opt['where']) && is_array( $opt['where']))
			{
				foreach( $opt['where'] as $key => $val)
				{
					$SQL .= " AND `$key` = '$val' ";
				}
			}

		//-->
		
		DB::exec( $SQL);
		
		//<!--Clean the Cache...

			if( $cachePrfx)
			{
				is_string( $cachePrfx) or isset( $opt['tableName']) and $cachePrfx = $opt['tableName'];
				Cache::clean( $cachePrfx);
				defined( 'DEBUG_MODE') and DB::$cachArr[] = array( 'SQL' => $SQL, 'DELETE FROM Cache' => 'true' , 'Cache Prefix' => $cachePrfx);
			}

		//End of Clean the Cache-->

		
		return DB::affctdRws();
	}
	
	/*----------------------------------------------*/

	public static function delete( $opt = array(), $cachePrfx = NULL)
	{
		
		$SQL = "DELETE FROM `{$opt['tableName']}` WHERE 1 ";

		//<!-- Where clause

			if( isset( $opt['where']) && is_array( $opt['where']))
			{
				foreach( $opt['where'] as $key => $val)
				{
					if( is_array( $val) && sizeof( $val))
					{
						$SQL .= " AND `$key` IN ( '". implode( '\',\'', $val) ."' ) ";
						
					}else{
						
						$SQL .= " AND `$key` = '$val' ";	
					
					}//End of if( is_array( $val));
				
				}//End of foreach( $opt['where'] as $key => $val);
			
			}//End of if( isset( $opt['where']) && is_array( $opt['where']));

		//-->
		
		DB::exec( $SQL);
		
		//<!--Clean the Cache...

			if( $cachePrfx)
			{
				is_string( $cachePrfx) or isset( $opt['tableName']) and $cachePrfx = $opt['tableName'];
				Cache::clean( $cachePrfx);
				defined( 'DEBUG_MODE') and DB::$cachArr[] = array( 'SQL' => $SQL, 'DELETE FROM Cache' => 'true' , 'Cache Prefix' => $cachePrfx);
			}

		//End of Clean the Cache-->
		
		return DB::affctdRws();
	}
	
	/*----------------------------------------------*/
	
	public static function printRprt()
	{
		printr( 'Total Queries: '. count( DB::$sqlArr));
		printr( DB::$sqlArr);
		printr( 'Total Cache Automatic Oprations: '. @sizeof( DB::$cachArr));
		printr( DB::$cachArr);
		
		$cnn = DB::conn();
		printr( 'MySQL Server Stats:<br />'. $cnn -> stat());
	}

	/*-----------------------------------------------------------------------------------*/

	public static function Begin() //Transaction Start...
	{
		if( !$this -> DB) $this -> DB = $this -> Connect( 1);
		return mysql_query("BEGIN");
	}

	public static function Commit()//Transaction End.
	{
		return mysql_query("COMMIT");
	}

}//End of class DB.

?>
