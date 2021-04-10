<?php
/**
* @Author Mojtaba Eskandari
* @since 2009-03-15
* @name JavaScript Class.
* @desc Fetch the Needed javascript functions and codes. and Automatic Pack them.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class JS
{
	/*-----------------------------------------------*/
	
	/**
	* @desc Load the  javascript file with Optimizer
	*/
	public static function ld( $src)
	{
		global $_cfg;
		if( defined( 'DEBUG_MODE')) return $_cfg['URL'] . str_replace( array( '../'), array( './'), $src);
		
		$chName = str_replace( array( '../', './', '/'), array( '', '', '.'), $src);
		
		if( !file_exists( dirname( __FILE__) .'/../../cache/ext/'. $chName))
		{
			lib( array( 'JsPacker'));
			$script = file_get_contents( $src);

			$packer = new JavaScriptPacker( $script, 'Normal', true, false);
			$packed = $packer->pack();

			file_put_contents( dirname( __FILE__) .'/../../cache/ext/'. $chName, $packed);

		}//End of if( !file_exists( $_cfg['etcPath']. $chName));
		
		return $_cfg['URL'] .'cache/ext/'. $chName;
	}

	/*-----------------------------------------------*/
	
	public static function getLnk( $src)
	{
		$b = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB), MCRYPT_RAND);
		$a = base64_encode( mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5( sha1( strrev( md5( HOST_URL)))), $src, MCRYPT_MODE_CFB, $b));
		//printr( array( $a, base64_encode( $b)));
		return 'a='. urlencode( $a) .'&b='. urlencode( base64_encode( $b));
	}
	
	/*-----------------------------------------------*/

}//End of class JS;

?>
