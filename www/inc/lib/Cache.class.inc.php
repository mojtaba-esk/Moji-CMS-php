<?php
/*
* Author: Mojtaba Eskandari
* Started at 2009-02-10
* Cache Class.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

class Cache
{
	/*-----------------------------------------------*/
	
	public static function getFile( $name)
	{
		if( defined( 'DISABLE_CACHE')) return NULL;
		
		$name = dirname( __FILE__) .'/../../cache/files/'. $name .'.cache';
		
		if( file_exists( $name ))
		{
			return $name;
		}
		return NULL;
	}
	
	/*-----------------------------------------------*/
	
	public static function getData( $name)
	{
		if( defined( 'DISABLE_CACHE')) return NULL;
		return @include( dirname( __FILE__) .'/../../cache/files/'. $name .'.cache');
	}
	
	/*-----------------------------------------------*/

	public static function putFile( $name, & $data)
	{
		if( defined( 'DISABLE_CACHE')) return;

		$fp = fopen( dirname( __FILE__) .'/../../cache/files/'. $name .'.cache', 'w');
		$fp or defined( 'DEBUG_MODE') and printr( 'Can not Open file[ '. dirname( __FILE__) .'/../../cache/files/'. $name .' ]');
		$rslt = fwrite( $fp, $data);
		$rslt === false and defined( 'DEBUG_MODE') and printr( 'Can not write to file[ '. dirname( __FILE__) .'/../../cache/files/'. $name .' ]');
		fclose( $fp);
	}

	/*-----------------------------------------------*/
	
	public static function arrToSrc( & $arr)
	{
		if( is_array( $arr))
		{
			
			$str = '<?php return array(';
			$str .= Cache::trvrsArry( $arr);
			$str .= '); ?>';
			
		}else{
			
			$str = '<?php return \'';
			$str .= str_replace( "'", "\\'", $arr);
			$str .= '\'; ?>';
			
		}//End of if( is_array( $arr));

		return $str;
	}
	
	/*-----------------------------------------------*/
	
	private static function trvrsArry( & $arr)
	{
		if( is_array( $arr))
		{
			$str = '';
			foreach( $arr as $key => $itm)
			{
				$str .= is_numeric( $key) ? $key : "'$key'";
				$str .= '=>';
				if( !is_array( $itm))
				{
					$str .= ( is_numeric( $itm) && $itm[0] != 0 ? $itm : "'". str_replace( "'", "\\'", $itm) ."'") .',';
				
				}else{
				
					$str .= 'array(';
					$str .= Cache::trvrsArry( $itm);
					$str .= '),';
				
				}//End of if( !is_array( $itm));
			
			}//End of foreach( $arr as $key => $itm);
		
		}//End of if( is_array( $arr));
		return $str;
	}
	
	/*-----------------------------------------------*/
	
	public static function clean( $name, $pstFx = '.cache')
	{
		if( defined( 'DISABLE_CACHE')) return NULL;
		$files = glob( dirname( __FILE__) .'/../../cache/files/'. $name .'*'. $pstFx);

		if( is_array( $files))
		{
			foreach( $files as $file)
			{
				@unlink( $file);
			}
		}
	}
	
	/*-----------------------------------------------*/

}//End of class Cache;

?>
