<?php
/**
* @Author Mojtaba Eskandari
* @since 2009-03-28
* @name Image Class.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class Img
{
		private static $prfx;
	
	/*-----------------------------------------------*/

		public static function setPrfx( $prfx = NULL)
		{
			Img::$prfx = $prfx;
		}

	/*-----------------------------------------------*/
	
		public static function get( $pth, $opt = NULL)
		{
			if( !$pth) return 'cache/ext/0.jpg';
			
			//<!-- Prepare the target image name
			
				$name = Img::name( $pth);
				
				isset( $opt[ 'w']) and $name .= 'w'. $opt[ 'w'];
				isset( $opt[ 'h']) and $name .= 'h'. $opt[ 'h'];
				isset( $opt[ 'wtrMrk']) and $name .= 'wtrMrk';
				
				$name .= '.jpg';
			
			//End of Prepare the target image name-->
			
			if( file_exists( dirname( __FILE__) .'/../../cache/ext/'. $name))
			{
				return 'cache/ext/'. $name;
			}

			$img = Img::load( $pth); //Load the image file in a Object;
			
			if( isset( $opt[ 'w']) || isset( $opt[ 'h']))
			{
				$dst = NULL;
				Img::rsize( $dst, $img, intval( @$opt[ 'w']), intval( @$opt[ 'h']));//Called By $img Reference;
				$img = & $dst;
				unset( $dst);
			}
			
			if( isset( $opt[ 'wtrMrk']))
			{
				$dst = NULL;
				Img::WtrMrk( $dst, $img, $opt[ 'wtrMrk']);
				$img = & $dst;
				unset( $dst);
			}
			
			Img::save( $img, dirname( __FILE__) .'/../../cache/ext/'. $name);
			
			return 'cache/ext/'. $name;
		}

	/*-----------------------------------------------*/
	
		private static function rsize( & $dst, & $img, $nW, $nH)
		{
			$w	= imagesx( $img);
			$h	= imagesy( $img);
			
			$rtio = $w / $h;
			
			if( !$nH || !$nW)
			{
				$nH		or	$nH		= $nW / $rtio;
				$nW	or	$nW	= $nH * $rtio;
			
			}else{

				if( $rtio > 1)
				{

					$nH		= $nW / $rtio;

				}elseif( $rtio < 1){

					$nW	= $nH * $rtio;

				}//End of if( $rtio > 1);

			}//End of if( !$nH || !$nW);

			$nW	= round( $nW);
			$nH	= round( $nH);

			$dst = imagecreatetruecolor( $nW, $nH);

			// Resize
			imagecopyresampled( $dst, $img, 0, 0, 0, 0, $nW, $nH, $w, $h);
			imagedestroy( $img);
			
			//Return The Result in $dst;
		}
	
	/*-----------------------------------------------*/
		private static function save( & $img, $name)
		{
			imagejpeg( $img, $name, 100);
			imagedestroy( $img);
		}
	
	/*-----------------------------------------------*/
	
		private static function name( $pth)
		{
			return Img::$prfx .'.'. str_replace( dirname( $pth).'/', '', $pth);
		}
	
	/*-----------------------------------------------*/
	
		private static function load( & $src)
		{
			$im = getimagesize( $src);

			switch( $im[ 'mime'])
			{
				case 'image/jpeg': case 'image/pjpeg': return imagecreatefromjpeg( $src);
				case 'image/png' : case 'image/x-png': return imagecreatefrompng(  $src);
				case 'image/gif' : return imagecreatefromgif( $src);
//				case 'image/bmp' : return imagecreatefromwbmp( $src);
				default: return NULL;

			}//End of switch( $img[ 'mime']);
		}
	
	/*-----------------------------------------------*/

		public static function clean( & $name)
		{
			/*if( is_array( $name))
			{
				foreach( $name as $n)
				{
					Img::clean( $n);
				}
				return;
			}*/

			$files = glob( dirname( __FILE__) .'/../../cache/ext/'. Img::$prfx .'.'. $name .'*');
			
			if( is_array( $files))
			{
				foreach( $files as $file)
				{
					@unlink( $file);
				}
			}
		}

	/*-----------------------------------------------*/
	
		private static function WtrMrk( & $dst, & $img, & $opt)
		{
			//global $_cfg;
			
			isset( $opt[ 'alpha']) or $opt[ 'alpha'] = 20;
			
			if( isset( $opt[ 'img']))
			{
				lib( array( 'Watermark'));
				$w = new Watermark();
				$wMrkImg = Img::load( $opt[ 'img']);
				$dst = $w -> create_watermark( $img, $wMrkImg, $opt[ 'alpha']);
				return;
			}
		}
	
	/*-----------------------------------------------*/

}//End of class Img;

?>
