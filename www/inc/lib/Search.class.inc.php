<?php
/**
* @author Mojtaba Eskandari
* @since 2009-08-16
* @name Search Class.
* @Comment: `search_words` table used "latin1" collation, Because it has a problem with Persian words in "utf8".
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class Search
{
	/*-----------------------------------------------*/
	
	private $mdId = 0;
	private $subId = 0; //Sub Module Id... Define by Programmer
	
	/*-----------------------------------------------*/
	
	/**
	* Search( int $ModuleId)
	*/
	public function Search( $mdId = 0, $subId = 0)
	{
		$this -> mdId = $mdId;
		$this -> subId = $subId;
	}
	
	/*-----------------------------------------------*/

	/**
	* 
	*/
	public function setIndexes( $txt, $postId, $lngId)
	{
		$txt = strtolower( strip_tags( $txt));

		DB::exec( 'SET NAMES \'latin1\'');
		DB::exec( 'SET NAMES \'latin1\'', 0);//For Read Mod Connection.
		
		//<!-- Insert New words
		
			$wrds = $this -> getWrds( $txt);
			if( sizeof( $wrds['new']))
			{
				$SQL = 'INSERT INTO `search_words` ( `word`) VALUES( \''. implode( '\'),(\'', $wrds['new']) .'\');';
				DB::exec( $SQL);
			}

		//End of Insert New words-->
		
		$this -> clearIndxs( $postId, $lngId);
		
		//<!-- Fetch The IDs of New Words...

			$wrds['new'] = $this -> getWrdIds( $wrds['new']);

		//-->
		
		//<!-- Add The Search key words into junction table
		
			foreach( array( 'old', 'new') as $indx)
			{
				foreach( $wrds[ $indx] as $id => $wrd)
				{
					DB::insert( array(
							'tableName' => 'search_main',
							'cols' => array(
								'wrdId'	=> $id,
								'postId'=> $postId,
								'lngId'	=> $lngId,
								'mdId'	=> $this -> mdId,
								'subId'	=> $this -> subId,
								'frq'	=> @substr_count( $txt, $wrd),
								),
						)
					);

				}//End of foreach( $wrds[ $indx] as $id => $wrd);

			}//End of foreach( array( 'old', 'new') as $indx);

		//End of Adding-->
		
		DB::exec( 'SET NAMES \'utf8\'');
		DB::exec( 'SET NAMES \'utf8\'', 0);//For Read Mod Connection.
	}

	/*-----------------------------------------------*/
	
	private function getWrdIds( & $wrds)
	{
		if( !sizeof( $wrds)) return array();

		$SQL = 'SELECT `id`, `word` FROM `search_words` WHERE `word` IN ( TRIM( \''. implode( '\' ),TRIM( \'', $wrds) .'\'));';
		$rws = DB::load( $SQL);
		
		$rslt = array();
		
		if( !$rws) return $rslt;
		
		foreach( $rws as & $rw)
		{
			$rslt[ $rw['id'] ] = $rw['word'];

		}//End of foreach( $rws as $rw);

		return $rslt;
	}
	
	/*-----------------------------------------------*/
	
	private function clr( & $txt)
	{
		$rplc = array(
			'آ' => 'ا',
			'ء' => '',
			'ؤ' => 'و',
			'ة' => 'ه',
			'ئ' => 'ی',
			'ي' => 'ی',
			'إ' => 'ا',
			'أ' => 'ا',
			'ك' => 'ک',
			'ُ' => '',
			'ِ' => '',
			'َ' => '',
			'ّ' => '',
			'ْ' => '',
			'ٌ' => '',
			'ٍ' => '',
		);
		return str_replace( array_keys( $rplc), $rplc, $txt);
		
	}
	
	/*-----------------------------------------------*/
	
	private function getWrds( & $txt)
	{
		$ignors = array( '.', ',', ':', ';', "\t", "\n", "\r", '\\', '/', '،', '(', ')', '[', ']');
		$wrds = array_unique( explode( ' ', str_replace( $ignors, ' ', $this -> clr( $txt))));
		
		$rslt[ 'old' ] = $this -> getWrdIds( $wrds); //The key of old is wordId.
		$newWrds = array_diff( $wrds, $rslt[ 'old']);
		
		$rslt[ 'new'] = array();
		foreach( $newWrds as $wrd)
		{
			strlen( $wrd = trim( $wrd)) > 3 and $rslt[ 'new'][] = $wrd;
		}
		
		return $rslt;
	}
	
	/*-----------------------------------------------*/
	
	public function clearIndxs( $postId, $lngId = 0)
	{
		$where = array(
					'postId'	=> $postId,
					'mdId'		=> $this -> mdId,
					'subId'		=> $this -> subId,
				);
		$lngId and $where[ 'lngId']	= $lngId;//For ignore languageId when needed.
		
		DB::delete( array(
				'tableName' => 'search_main',
				'where'	=> $where,
			)
		);

	}

	/*-----------------------------------------------*/
	
	public function getKwrds( & $qry)
	{
		$ignore = array( '%', '*', '#', '-', '=', '!');
		$arr = explode( ' ', str_replace( $ignore, '', $qry));
		
		$rslt = array();
		foreach( $arr as $kWrd)
		{
			strlen( $kWrd) > 3 and $rslt[] = trim( $kWrd);
		}
		
		return $rslt;
	}
	
	/*-----------------------------------------------*/
	
	public function getIdsSQL( & $qry , $lngId)
	{
		$kWrds = $this -> getKwrds( strtolower( $this -> clr( $qry)));
		
		if( !sizeof( $kWrds)) return '0';
		
		$SQL = 'SELECT `postId` FROM 
			(
				SELECT `s`.`postId`, `s`.`frq` , COUNT( `postId` ) AS `total` 
				FROM 
					`search_main` AS `s`,
					`search_words` AS `w` 
				WHERE 
					`w`.`word` IN ( CONVERT( _latin1 \''. implode( '\' USING utf8),CONVERT( _latin1 \'', $kWrds) .'\' USING utf8))
					AND 
						`s`.`lngId` = '. $lngId .'
					AND
						`w`.`id` = `s`.`wrdId`
					AND
						`s`.`mdId` = '. $this -> mdId .'
					AND
						`s`.`subId`= '. $this -> subId .'
				GROUP BY `postId`
				ORDER BY `total` DESC , `s`.`frq` DESC
			) AS `gholi` 
			WHERE `total` = '. sizeof( $kWrds);//For "AND" Search. 

		return $SQL;

		//return DB::load( $SQL, false, true);
	}
	
	/*-----------------------------------------------*/

}//End of class Search;

?>
