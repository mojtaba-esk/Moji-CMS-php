<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. List The Informations;
*/



	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
	
	//the identifier which stands unique for each post
	$id = $_GET['postid'];
	$like = $_GET['like'];

	//fetching the like count
	$SQL = "select *
			  from blog_comment
			  where id=".$id;
	$result = DB::load($SQL);//Module::$name . '_rate');
	//check if the comment has already scored or not
	if($result){
	foreach($result as $row)
	{
		if($row['id'] == $id) //if we have already score for this comment
		{
			$likeCount = $row['like'];
			$unlikeCount = $row['unlike'];		
			if ($like == 1)
				++$likeCount;
			else
				++$unlikeCount;
			($like == 1)? $iCols['like'] = $likeCount : $iCols['unlike'] = $unlikeCount;
			DB::update( array(
					'tableName' => Module::$name . '_comment',
					'cols' 	=> & $iCols,
					'where'	=> array(
						'id' => $id,
					),
				)
			);			
		}
	}	
}	
	echo $likeCount.','.$unlikeCount;
	exit();
	
?>