<?php
/**
* @author Mojtaba Eskandari
* @since 2009-03-27
* @name HTML Tabs Class.
* @notice This Class used jQuery
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');
defined( 'IN_MJY_CMS_LOCAL23X31') and die( 'Rstrct Acs!');

class Tab
{
	/*-----------------------------------------------*/
	
	private $tbs;	//Tabs.
	private $tbsIds;//Tabs IDs.
	private $frst;	//First Tab, Show this tab in first view.
	static $prfx = 0;
	
	/*-----------------------------------------------*/
	
	public function Tab( & $tbs, $frst = 0)
	{
		$this -> tbs  	= $tbs;
		$this -> tbsIds	= array_keys( $tbs);
		$this -> frst 	= $frst;
		self::$prfx++;
	}
	
	/*-----------------------------------------------*/
	
	public function bar()
	{
		$rslt  = '<script type="text/javascript">function tb'. self::$prfx .'(i){$(\'.tbar_'. self::$prfx .'\').hide();$(\'#tbr'. self::$prfx .'_\'+i).show();$(\'.actvBr\').attr("class","br");$(\'#brt'. self::$prfx .'_\'+i).attr("class","actvBr");}</script>';
		$rslt .= '<div style="direction:'. Lang::$info['dir'] .';text-align:'. Lang::$info['align'] .';" class="tab">';
		if( !is_array( $this -> tbs)) return NULL;
		foreach( $this -> tbs as $key => $title)
		{
			$class = $key == $this -> frst ? 'actvBr' : 'br';
			$rslt .= '<div class="'. $class .'" style="float:'. Lang::$info['align'] .';" id="brt'. self::$prfx .'_'. $key .'" onclick="tb'. self::$prfx .'('. $key .')">';
			$rslt .= $title;
			$rslt .= '</div>';
		}
		$rslt .= '</div>';
		return $rslt;
	}
	
	/*-----------------------------------------------*/
	
	public function rst()
	{
		return reset( $this -> tbsIds);
	}

	/*-----------------------------------------------*/
	
	public function opn( & $lng)
	{
		static $isFrst = true;
		$lng['id'] or $lng['id'] = $isFrst ? current( $this -> tbsIds) : next( $this -> tbsIds);
	 	
		$disp = $this -> frst == $lng['id'] ? '' : 'none';
		$this -> frst or $disp = $isFrst ? '' : 'none';
		
		$isFrst = false;

		return '<table style="width:100%;text-align:'. $lng['align'] .';direction:'. $lng['dir'] .';display:'. $disp .';" class="tbar tbar_'. self::$prfx .'" id="tbr'. self::$prfx .'_'. $lng['id'] .'">';
	}
	
	/*-----------------------------------------------*/

	public function clos()
	{
		return '</table>';
	}

	/*-----------------------------------------------*/

}//End of class Tab;

?>
