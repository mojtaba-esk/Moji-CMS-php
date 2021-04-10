<?php
/**
* @author Mojtaba Eskandari
* @since 2009-03-28
* @name File Upload & Download Class.
* @comment this class call the Img::clean() method, for clear the Image Cache.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class File
{
	/*-----------------------------------------------*/
	
	private $md;//Module Name
	
	/*-----------------------------------------------*/
	
	public function __construct( $moduleName = NULL)
	{
		$this -> md = $moduleName;
	}
	
	/*-----------------------------------------------*/
	
	private function upload( $name, $tmpName)
	{
		return move_uploaded_file( $tmpName, dirname( __FILE__) .'/../../modules/'. $this -> md .'/files/'. $name);
	}
	
	/*-----------------------------------------------*/
	
	public function save( $id, & $files, $prfx = 'img.')
	{
		if( !$files) return;
		
		//<!-- Check for quota avilability...

			global $_cfg;
			if( empty( $_cfg['domain']['id'])) return false;

			$dRws = DB::load(
				array( 
					'tableName' => 'domains_main',
					'cols' => array( 'quotaLimit', 'usedQuota'),
					'where' => array(
						'rltdId' => $_cfg['domain']['id'],
					),
				),
				false
			);

			if( $dRws[0]['quotaLimit'] != 0 && // 0 means unlimited space...
				$dRws[0]['quotaLimit'] - $dRws[0]['usedQuota'] <= 0) return false;

		//-->

		$totalSize = 0;
		if( is_array( $files))
		{
			foreach( $files as $key => $file)
			{
				if( !$file) continue;
				$name = $prfx . $id .'.'. $key;
				
				//<!-- Size of the files...
				
					$totalSize += filesize( $file);
					$oldPath = dirname( __FILE__) .'/../../modules/'. $this -> md .'/files/'. $name;
					file_exists( $oldPath) and $totalSize -= filesize( $oldPath);

				//-->

				$this -> upload( $name, $file);
				Img::clean( $name);

			}//End of foreach( $files as $key => $file);
			
		}else{
		
			$name = $prfx . $id;

			//<!-- Size of the file...
			
				$totalSize += filesize( $files);
				$oldPath = dirname( __FILE__) .'/../../modules/'. $this -> md .'/files/'. $name;
				file_exists( $oldPath) and $totalSize -= filesize( $oldPath);

			//-->			

			$this -> upload( $name, $files);
			Img::clean( $name);//Clear The Image Cache.

		}//End of if( is_array( $files));

		//<!-- save the quota used by user...

			if( $totalSize)
			{
				global $_cfg;
				if( empty( $_cfg['domain']['id'])) return true;
				
				$totalSize /= 1024; // Converting Byte to KB
				
				$SQL = "UPDATE
							`domains_main`
						SET
							`usedQuota` = `usedQuota` + $totalSize
						WHERE
							`rltdId` = {$_cfg['domain']['id']}";

				DB::exec( $SQL);

			}//End of if( $totalSize);

		//-->

		return true;
	}

	/*-----------------------------------------------*/
	
	public function getPth( $id, $mltiId = 0, $prfx = 'img.')
	{
		$pth = dirname( __FILE__) .'/../../modules/'. $this -> md .'/files/'. $prfx . $id .( $mltiId ? '.'. $mltiId : '');
		return file_exists( $pth) ? $pth : NULL;
	}

	/*-----------------------------------------------*/

	public function delete( $id, $mltiId = 0, $prfx = 'img.')
	{
		$name = $prfx . $id .( $mltiId ? '.'. $mltiId : '');
		Img::clean( $name);
		$pth = dirname( __FILE__) .'/../../modules/'. $this -> md .'/files/'. $name;
		
		//<!-- save the quota used by user...

			$totalSize = file_exists( $pth) ? filesize( $pth) : 0;
			if( $totalSize > 0)
			{
				global $_cfg;
				if( empty( $_cfg['domain']['id'])) return true;
				
				$totalSize /= 1024; // Converting Byte to KB
				
				$SQL = "UPDATE
							`domains_main`
						SET
							`usedQuota` = `usedQuota` - $totalSize
						WHERE
							`rltdId` = {$_cfg['domain']['id']}";

				DB::exec( $SQL);

			}//End of if( $totalSize > 0);

		//-->

		return @unlink( $pth);
	}

	/*-----------------------------------------------*/
}//End of class File;

?>
