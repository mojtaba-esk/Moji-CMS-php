<?php
/**
* @author Hooman Raesi
* @since 2013-02-27
* @name Module custom ductions;
*/


function recLoad( $tbName, $whr = '1', $rootId = 0, $level = 0)
{
	$SQL = "SELECT *
			FROM
				`$tbName`
			WHERE
				$whr
			AND
				`parentId` = $rootId
			ORDER BY
				`id` ASC";
	$rws = DB::load( $SQL);
	if( empty( $rws)) return NULL;
	
	$rslt = array();
	foreach( $rws as $rw)
	{
		$rw['level'] = $level;
		$rslt[] = $rw;
		$chldrn = recLoad( $tbName, $whr, $rw['id'], $level + 1);
		if( is_array( $chldrn))
		{
			foreach( $chldrn as $chRw)
			{
				$rslt[] = $chRw;
			
			}//End of foreach( $chldrn as $chRw);
		
		}//End of if( is_array( $chldrn));

	}//End of foreach( $rws as $rw);
	
	return $rslt;
}

?>