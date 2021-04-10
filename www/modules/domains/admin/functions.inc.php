<?php
/*-----------------------------------------------*/
	/**
	 * @desc get The Categories of module
	 * @param string $md ( Module Name)
	 * @param int $lngId ( Language Id)
	 * @return Array of Categories*/
	function getDomStatus( $lngId, $sql = '')
	{
		global $_cfg;
		
		$SQL = 'SELECT
					`rltdId`,
					`title`
				FROM
					`domains_status`
				WHERE
					`lngId` = '. $lngId .' '.
					$sql;

		$rws = DB::load( $SQL, 'domains_status');

		if( !$rws) return array();

		$rslt[0] = Lang::getVal( 'active');
		foreach( $rws as $rw)
		{
			$rslt[ $rw[ 'rltdId']] = $rw[ 'title'];
		}
		return $rslt;
	}

/*-----------------------------------------------*/

	/**
	 * @desc get The Categories of module
	 * @param string $md ( Module Name)
	 * @param int $lngId ( Language Id)
	 * @return Array of Categories*/
	function getDomPlans( $lngId, $sql = '')
	{
		global $_cfg;
		
		$SQL = 'SELECT
					`rltdId`,
					`title`
				FROM
					`domains_plans`
				WHERE
					`lngId` = '. $lngId .' '.
					$sql;

		$rws = DB::load( $SQL, 'domains_plans');

		if( !$rws) return array();

		$rslt[0] = Lang::getVal( 'custom');
		foreach( $rws as $rw)
		{
			$rslt[ $rw[ 'rltdId']] = $rw[ 'title'];
		}
		return $rslt;
	}

/*-----------------------------------------------*/
?>
