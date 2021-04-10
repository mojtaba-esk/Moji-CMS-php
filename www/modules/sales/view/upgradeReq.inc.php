<?php
/*
* @author Ghasem Babai
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

$MapTable = 'D^Q*A#H_T%?';
$ManTable = '0123456789,';
	
function StringSplitorForEncrypt(&$withCommaStrings, &$AfterCommaStrings, $BitString)
{
	$Len = strlen($BitString);
	$withCommaStrings = '';
	$AfterCommaStrings = '';
	$j = 0;
  
	for ($i = 0; $i < $Len; $i++)
	{
		if ($j == 1)
		{
			$AfterCommaStrings .= $BitString[$i];
			$j = 0;
		} // { if ($j == 1) }
		else
		{
			if ($BitString[$i] == ',')
				$j = 1;
			$withCommaStrings .= $BitString[$i];
		} // { else }
	} // { for i = 1 to Len do }
}

// کليه کارکترهاي جدا شده بعد از کاما ها را در موقعيت قبلي شان قرار مي دهد
function StringSplitorForDecrypt(&$withCommaStrings, &$AfterCommaStrings)
{
	$j = 0;
	$tempStr = '';
	$HaveCama = false;
	$Len = strlen($withCommaStrings);

	for ($i = 0; $i < $Len; $i++)
	{	
		if ($withCommaStrings[$i] == ',')
			$HaveCama = true;
		else
			$HaveCama = false;
			
		$tempStr .= $withCommaStrings[$i];
		
		if ($HaveCama)
		{
			$tempStr .= $AfterCommaStrings[$j];
			//echo $tempStr."<br />";			
			$j++;
		} //{ if ($HaveCama) }
	} //{ ($i = 0; $i < $Len; $i++)}
	
	return $tempStr;
}
 
// جايگزين مي نمايد MapTable ها را  از ورودي گرفته و آنها را با  Feature رشته ليست 
function ChangeMainStringToCodeString($MainString)
{
	global $MapTable, $ManTable;

	for ($i = 0; $i < 11; $i++)
	{
		$MainString = str_replace($ManTable[$i], $MapTable[$i], $MainString);
	} //{ for ($i = 0; $i < 11; $i++) }
	
	return $MainString;
}

// برعکس بالايي 
function ChangeCodeStringToMainString($CodeString)
{
	global $MapTable, $ManTable;

	for ($i = 0; $i < 11; $i++)
	{
		$CodeString = str_replace($MapTable[$i], $ManTable[$i], $CodeString);
	} //{ for ($i = 0; $i < 11; $i++) }
  
  return $CodeString;
}

// رشته را از ورودي دريافت مي کند و آن را به هم مي ريزد
function GhasemEncrypt($BitString)
{
	StringSplitorForEncrypt($withCommaStrings, $AfterCommaStrings, $BitString);
	
	$StringLength = strlen($withCommaStrings);
	$DivitionStringBy3 = floor($StringLength / 3);
	$withCommaStrings = strrev($withCommaStrings);
	
	$StrPrt1 = substr($withCommaStrings, 0, $DivitionStringBy3);
	$StrPrt2 = substr($withCommaStrings, $DivitionStringBy3, $DivitionStringBy3);
	$StrPrt3 = substr($withCommaStrings, 2 * ($DivitionStringBy3),
	$StringLength - 2 * $DivitionStringBy3);
	$BitString = $StrPrt2.$StrPrt3.$StrPrt1;	
	
	$StringLength = strlen($AfterCommaStrings);
	$DivitionStringBy3 = floor($StringLength / 3);
	$AfterCommaStrings = strrev($AfterCommaStrings);
	
	$StrPrt1 = substr($AfterCommaStrings, 0, $DivitionStringBy3);
	$StrPrt2 = substr($AfterCommaStrings, $DivitionStringBy3, $DivitionStringBy3);
	$StrPrt3 = substr($AfterCommaStrings, 2 * ($DivitionStringBy3),
	$StringLength - 2 * $DivitionStringBy3);
	$BitString = $StrPrt3.$BitString.$StrPrt2.$StrPrt1;
	
	return strrev($BitString);
}

// برعکس بالايي
function GhasemDecrypt($BitString)
{
	$BitString = strrev($BitString);

	$CommaCount = substr_count($BitString, ',');
	$StringLength = strlen($BitString);
	$DivitionStringBy3 = floor($CommaCount / 3);
	
	$StrPrt1 = substr($BitString, ($StringLength - $DivitionStringBy3),
	$DivitionStringBy3);
	$StrPrt2 = substr($BitString, $StringLength - ($DivitionStringBy3 * 2),
	$DivitionStringBy3);
	$StrPrt3 = substr($BitString, 0, $CommaCount - 2 * $DivitionStringBy3);
	$BitString = substr($BitString, ($CommaCount - 2 * $DivitionStringBy3),
	$StringLength - (strlen($StrPrt1.$StrPrt2.$StrPrt3)));	
	
	$AfterCommaStrings = strrev($StrPrt1.$StrPrt2.$StrPrt3);		
	$StringLength = strlen($BitString);
	$DivitionStringBy3 = floor($StringLength / 3);
	
	$StrPrt1 = substr($BitString, ($StringLength - $DivitionStringBy3),
	$DivitionStringBy3);
	$StrPrt2 = substr($BitString, ($StringLength - ($DivitionStringBy3 * 2)),
	$DivitionStringBy3);
	$StrPrt3 = substr($BitString, 0, $StringLength - 2 * $DivitionStringBy3);	
	$withCommaStrings = strrev($StrPrt2).strrev($StrPrt3).strrev($StrPrt1);

	return StringSplitorForDecrypt($withCommaStrings, $AfterCommaStrings);
}	

?>
