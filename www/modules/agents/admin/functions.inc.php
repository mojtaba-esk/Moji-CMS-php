<?php
/*-----------------------------------------------*/
	/**
	 * @desc get The Categories of module
	 * @param string $md ( Module Name)
	 * @param int $lngId ( Language Id)
	 * @return Array of Categories*/
	function getCuCats( $md, $lngId, $catName = 'cats', $sql = '')
	{
		global $_cfg;
		$SQL = 'SELECT
					`rltdId`,
					`title`
				FROM
					`'. $md .'_'. $catName .'`
				WHERE
					`lngId` = '. $lngId . ' '. $sql;

		$rws = DB::load( $SQL, $md .'_cats');

		if( !$rws) return array();
		foreach( $rws as $rw)
		{
			$rslt[ $rw[ 'rltdId']] = $rw[ 'title'];
		}
		return $rslt;
	}

/*-----------------------------------------------*/

?>
