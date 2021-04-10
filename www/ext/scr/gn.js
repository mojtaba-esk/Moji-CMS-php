var $w = window;
var $d = $w.document;
function $e(i)
{
	return $d.getElementById( i);
}

/*---------------------------------------*/

function confrm( t)
{
	return $w.confirm( t);
}

/*---------------------------------------*/

/* Comment: Examples: 
	- selectAllChks( this): "this" is a checkbox that this function call on OnClick event;
	- selectAllChks( document.getElementById( 'myCheckBox_'));
this function use the ID of called parameter;
HTML Ex:
	<input type="checkbox" name="chkAll" id="chk_" onclick="selectAllChks(this);" /> */
function selectAllChks( o)
{
	for( var i = 0; 1; i++)
	{
		if( !$e( o.id + i)) break;
		$e( o.id + i).checked = o.checked;
	}
}

/*---------------------------------------*/

/* ID: 4 */
/* Comment: Example:
	<a onclick="return cnfrmWthTitle( 'Lang_areYouSureToDeleteThis', 37);" href="?md=news&mod=lst&lng=fa&del=1&chk=Array&vLng=fa&del=1&chk[]=37">
		Lang_delete
	</a>
	<td id="title37">
		Title text
	</td> */
function cnfrmWthTitle( t, i)
{
	return $w.confirm( t + '\n' + $e( 'title' + i).innerHTML);
}

/*---------------------------------------*/

/* 
Load Dynamically javascript files in page.
Examples: 
	- ldJS( './etc/scr/jq.vldt.js', isLoadedObject, callThisFunction);
*/
function ldJS( s, o, f)
{
	if( isLDs( o, f))return;
	var S=$d.createElement('script');
	S.src=s;
	$d.getElementsByTagName('head').item(0).appendChild(S);
	isLDs( o, f);
}
/*
Call the function is loaded
*/
function isLDs( o, f)
{
	if( typeof( eval( o)) != 'undefined')
	{
		f();
		return 1;
	}
	setTimeout( function(){ isLDs( o, f)}, 300);
	return 0;
}

/*---------------------------------------*/

/*
 *	Write a data on parent's page.
 *	Ex: popSlct('tmpTitle','Pashmaloo');
*/
function popSlct( i, d)
{
	var e = $w.parent.document.getElementById(i);
	if( typeof( e) == 'undefined') return;
	if( typeof( e.value) != 'undefined') e.value = d;
	if( typeof( e.innerHTML) != 'undefined') e.innerHTML = d;
}

/*---------------------------------------*/

function goURL( u)
{
	$w.location.href = u;
}

/*---------------------------------------*/
