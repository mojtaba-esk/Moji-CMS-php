<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. List The Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	$comment_found = false;
	//recieve the score from rating page
	$score = $_GET['scr'];
	//the identifier which stands unique for each comment
	$id = $_GET['id'];

	//fetching the score and number of user from database
	$SQL = "select *
			  from blog_rate
			  where rltdId=".$id;
	$result = DB::load($SQL);//Module::$name . '_rate');
	//check if the comment has already scored or not
	if($result){
	foreach($result as $row)
	{
		if($row['rltdId'] == $id) //if we have already score for this comment
		{
			$tmp_score = $row['score'];
			$tmp_userCount = $row['userCount'];
			$new_userCount = $tmp_userCount + 1;
			$new_score = (($tmp_score*$tmp_userCount)+$score)/$new_userCount;

			$iCols['userCount']	= $new_userCount;
			$iCols['score']	= $new_score;

						
			DB::update( array(
					'tableName' => Module::$name . '_rate',
					'cols' 	=> & $iCols,
					'where'	=> array(
						'rltdId' => $id,
					),
				)
			);			
			$comment_found = true;
		}
	}	
}	
	if(!$comment_found)//if we haven't scored the comment yet
	{
		//<!--insert the required part of comments into database
		$iCols['id']	= 0;
		$iCols['rltdId']	= intval($_GET['id']);
		$iCols['domId']	= 0;
		$iCols['lngId']	= 0;
		$iCols['userCount']	= 1;
		$iCols['score']	= intval($score);
		//End of insert the required part of comments into database		-->
			DB::insert( array(
				'tableName' => Module::$name . '_rate',
				'cols' => & $iCols,
			)
			,  Module::$name /* Cache Prefix*/
		);
		$new_score=$score;
	}
	echo $new_score;
	exit();
	
?>