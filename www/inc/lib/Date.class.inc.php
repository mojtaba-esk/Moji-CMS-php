<?php
/**
* @Author Mojtaba Eskandari
* @since 2009-03-16
* @name Date Class.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class Date
{

	private static $GMTZone = 3.5; //Tehran +3:30;
	
	/**
	 * @desc Set The New GMT Zone for dates
	 * */
	public static function setGMT( $GMTZone)
	{
		Date::$GMTZone = $GMTZone;
	}

	/*---------------------------------------------------------*/
	
	/**
	 * @desc Get The date in requested format
	 * @example Date::get( 'd M Y', $rw[ 'pblishTime']) returns "14 Esfand 1387 "
	 * @example Date::get( 'd/m/Y', $rw[ 'pblishTime']) returns "14/12/1387 "
	 * @example Date::get( 'D d M Y', $rw[ 'pblishTime']) returns "Wed 14 Esfand 1387 "
	 * */
	public static function get( $format, $time, $type = NULL)
	{
		if( !$time) return '[ - - - ]';
		Lang::$info or Lang::id();
		$type or $type = Lang::$info['dateType'];

		if( $type != 'jalali')
		{
			return Date::create( $format, $time);
		}
		
		list( $year, $month, $day) = explode( ',', Date::create( 'Y,m,d', $time));
		$jalaliArray = Date::g2Jalali( $year, $month, $day);
		
		$format = str_replace( array_keys( $jalaliArray), $jalaliArray, $format);

		$frmts = array( 'G', 'D', 'i', );
		foreach( $frmts as $frmt)
		{
			if( strpos( $format, $frmt) !== false)
			{
				$title = Date::create( $frmt, $time);
				is_numeric( $title) or $title = Lang::getVal( $title);
				$format = str_replace( $frmt, $title, $format);
			}

		}//End of foreach( $frmts as $frmt);
	
		if( strpos( $format, 'M') !== false)
		{
			$format = str_replace( 'M', Lang::getVal( 'month_'. $jalaliArray[ 'm']), $format);
		}

		return $format;
	}

	/*---------------------------------------------------------*/
	
	private static function g2Jalali( $g_y, $g_m, $g_d)
	{

	    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

	   $gy = $g_y-1600;
	   $gm = $g_m-1;
	   $gd = $g_d-1;

	   $g_day_no = 365*$gy+(int)(($gy+3)/4) - (int)(($gy+99)/100)+(int)(($gy+399)/400);

	   for ($i=0; $i < $gm && $i < 12; ++$i)
	      $g_day_no += $g_days_in_month[$i];

	   if ($gm>1 && (($gy%4==0 && $gy%100!=0) || ($gy%400==0)))
	      /* leap and after Feb */
	      $g_day_no++;
	   $g_day_no += $gd;

	   $j_day_no = $g_day_no-79;

	   $j_np = (int)($j_day_no/12053); /* 12053 = 365*33 + 32/4 */
	   $j_day_no = $j_day_no % 12053;

	   $jy = 979+33*$j_np+4*(int)($j_day_no/1461); /* 1461 = 365*4 + 4/4 */

	   $j_day_no %= 1461;

	   if ($j_day_no >= 366) {
	      $jy += (int)(($j_day_no-1)/365);
	      $j_day_no = ($j_day_no-1)%365;
	   }

	   for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i)
	      $j_day_no -= $j_days_in_month[$i];
	   $jm = $i+1;
	   $jd = $j_day_no+1;

		return array( 'Y' => $jy, 'm' => $jm, 'd' => $jd);
	}

	/*---------------------------------------------------------*/

	public static function mkTime( & $date, $type = NULL)
	{
		isset( $date['M']) and $date['m'] = $date['M'];
		if( empty( $date[ 'Y']) && empty( $date['m']) && empty( $date[ 'd']) && empty( $date['G']) && empty( $date['i']) && empty( $date['s'])) return 0;

		empty( $date['m']) and $date['m']	= 1;
		empty( $date['d']) and $date['d']	= 1;

		Lang::$info or Lang::id();
		$type or $type = Lang::$info['dateType'];

		if( $type == 'jalali')
		{
			$myDate = Date::j2Gregorian( $date['Y'], $date['m'], $date['d']);
		
		}else{

			$myDate = & $date;
		}

		empty( $date['G']) and $date['G'] = date( 'G');
		empty( $date['i']) and $date['i'] = date( 'i');
		empty( $date['s']) and $date['s'] = date( 's');
		
		return mktime( @$date['G'], @$date['i'], @$date['s'], $myDate['m'], $myDate[ 'd'], $myDate[ 'Y']);
	}	
	
	/*---------------------------------------------------------*/
	
	private static function create( $format, $gmepoch)
	{
		return gmdate( $format, $gmepoch + ( 3600 * Date::$GMTZone));
	}	

	/*-------------------------------------------------------------------------------------------------------------------------*/
	
	private static function j2Gregorian( $j_y, $j_m, $j_d)
	{
	   $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	   $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
		
	   $jy = $j_y-979;
	   $jm = $j_m-1;
	   $jd = $j_d-1;

	   $j_day_no = 365*$jy + ((int)( $jy/ 33))*8 + ((int)($jy%33+3)/4);
	   for ($i=0; $i < $jm; ++$i)
	      $j_day_no += $j_days_in_month[$i];

	   $j_day_no += $jd;

	   $g_day_no = $j_day_no+79;

	   $gy = 1600 + 400*((int)($g_day_no/ 146097)); /* 146097 = 365*400 + 400/4 - 400/100 + 400/400 */
	   $g_day_no = $g_day_no % 146097;

	   $leap = true;
	   if ($g_day_no >= 36525) /* 36525 = 365*100 + 100/4 */
	   {
	      $g_day_no--;
	      $gy += 100*((int)($g_day_no/ 36524)); /* 36524 = 365*100 + 100/4 - 100/100 */
	      $g_day_no = $g_day_no % 36524;

	      if ($g_day_no >= 365)
	         $g_day_no++;
	      else
	         $leap = false;
	   }

	   $gy += 4*(int)($g_day_no/ 1461); /* 1461 = 365*4 + 4/4 */
	   $g_day_no %= 1461;

	   if ($g_day_no >= 366) {
	      $leap = false;

	      $g_day_no--;
	      $gy += (int)($g_day_no/ 365);
	      $g_day_no = $g_day_no % 365;
	   }

	   for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); $i++)
	      $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap);
	   $gm = $i+1;
	   $gd = $g_day_no+1;

	   return array( 'Y' => $gy, 'm' => $gm, 'd' => $gd);
	}

}//End of class Data;

?>
