<?php
/**
* @author: Mojtaba Eskandari
* @since 2009-02-06
* @name Common Functions.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

/*-----------------------------------------------*/

	/**
	* @desc Print the Array Recursively, For Debug mod;
	*/
	function printr( $arr)
	{
		print( '<pre style="direction:ltr;text-align:left;border:1px solid #000;background-color:#FFCC22;display:block;">');
		$arr === false || $arr === NULL ? print( '[ NULL ]') : print_r( $arr);
		print( '</pre>');
	}
	
/*-----------------------------------------------*/

	/**
	* @desc Message Die( string $message , string $backPageURL, int $RedirectTime, string $type = array( 'info', 'error'))
	* After call this function, for stop the script, write: return;
	*/
	function msgDie( $msg, $url = NULL, $time = 0, $type = 'info', $lnkTitle = NULL, $noAjax = false)
	{
		global $tpl;

		$tpl -> set_filenames( array( 'msgBody' => 'msgDie'));

		$tpl -> assign_vars( array(

				'MESSAGE'		=> & $msg,
				'RETURN_URL'	=> & $url,
				'REFRESH_TIME'	=> $time ? $time : '',
				'RETURN'		=> $lnkTitle ? $lnkTitle : Lang::getVal( 'return'),
				'TYPE'			=> & $type,
				'NO_AJAX'		=> $noAjax,
			)
		);

		$tpl -> display( 'msgBody');
	}

/*-----------------------------------------------*/

	/**
	* @desc Language Selector( bool Force Show The Selector even The Number of Languages is one)	*/
	function lngSelctr( $forceShw = 0)
	{
		$lngs = Lang::getAll();
		
		if( count( $lngs) <= 1 && !$forceShw) return '';

		$optnsTag = '';
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$selected = isset( $_GET[ 'vLng' ]) && $_GET[ 'vLng' ] == $lngs[ $i ][ 'shortName'] ? 'selected="selected"' : '';
			$optnsTag .= "<option $selected value=\"{$lngs[ $i ][ 'shortName']}\">{$lngs[ $i ][ 'title']}</option>";
		}
		
		return '<select name="vLng" onchange="window.location.href+=\''. /*URL::get( array( 'vLng')) .*/'&pg=1&vLng=\'+this.value;" class="lngSlctr">
					'. $optnsTag .'
				</select>';
	}

/*-----------------------------------------------*/

	function briefStr( $str, $lnt, $spr = ' ')
	{
		$lnt -= 3;
		if( strlen( $str) > $lnt)
		{
			while( --$lnt > 0 && $spr && $str[ $lnt ] != $spr);
			$str = substr( $str , 0 , $lnt);
			$str .= '...';
		}
		return $str;
	}
	

/*-----------------------------------------------*/

	function briefHint( $str, $lnt, $spr = ' ')
	{
		return '<span title="'. $str .'">'. briefStr( $str, $lnt, $spr) .'</span>';
	}	

/*-----------------------------------------------*/
	/**
	 * @desc get The Categories of module
	 * @param string $md ( Module Name)
	 * @param int $lngId ( Language Id)
	 * @return Array of Categories*/
	function getCats( $md, $lngId, $catName = 'cats', $sql = '')
	{
		global $_cfg;
		
		$SQL = 'SELECT
					`rltdId`,
					`title`
				FROM
					`'. $md .'_'. $catName .'`
				WHERE
					`lngId` = '. $lngId . '
					AND 
						`domId` = '. $_cfg['domain']['id'] .' '.
					$sql;
		$rws = DB::load( $SQL, $md . $_cfg['domain']['id'] .'_cats');

		if( !$rws) return array();
		foreach( $rws as $rw)
		{
			$rslt[ $rw[ 'rltdId']] = $rw[ 'title'];
		}
		return $rslt;
	}

/*-----------------------------------------------*/

	function notFound( $file = '404.html')
	{
		header( "HTTP/1.0 404 Not Found");
		header( "Status: 404 Not Found");
		
		if( $file === NULL) return;

		include( dirname( __FILE__) .'/../'. $file);
		exit();
	}

/*-----------------------------------------------*/

	/**
	 * @desc Lib. Add class
	 * @param Array $lst
	 */
	function lib( $lst)
	{
		foreach( $lst as $f)
		{
			require_once( dirname( __FILE__) .'/lib/'. $f . '.class.inc.php' );
		}
	}

/*-----------------------------------------------*/

	
	/**
	 * @desc decode File Size
	 * @param int $bytes
	 */
	function dcFSize( $bytes)
	{
		 $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		 for( $i = 0; $bytes >= 1024 && $i < 4 /* Size of $types - 1*/; $bytes /= 1024, $i++);
		 return round( $bytes, 2 ) .' '. Lang::getVal( $types[$i]);
	}

/*-----------------------------------------------*/

	/*
	* @desc this function encrypts the input string with a special algorithm in order to evade web bots to discover sensitive data such as email address
	* @param string $txt
	* @req this function needs javascript on browser to decrypt the given data
	* @req this function needs "gn.js" to be loaded in the page
	*/
	function jsEnc( $txt)
	{
		$revEncNum = rand( 2, 8);
		
		for( $i = 0; $i != $revEncNum; $i++)
		{
			$txt = base64_encode( strrev( $txt));

		}//End of for( $i = 0; $i != $revNum; $i++);

		$rndVar = chr( rand( ord( 'a'), ord( 'z')));
		rand( 0, 1) and $rndVar = strtoupper( $rndVar);
		
		$res = '<script type="text/javascript">';
		$res .= "var $rndVar='$txt';";
		for( $i = 0; $i != $revEncNum; $i++)
		{
			$res .= "$rndVar=b64Dec($rndVar).split('').reverse().join('');";

		}//End of for( $i = 0; $i != $revNum; $i++);		
		$res .= '$d.write('. $rndVar .');</script>';

		return $res;
	}


/*-----------------------------------------------*/
?>
