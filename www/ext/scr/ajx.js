/*-----------------------*/
/* Ajax Prepare the objects*/

function xpr()
{
	$('a').unbind('click', goURL);
	$('a').click( goURL);

	$('form').unbind( 'submit', xfrm);
	$('form').submit( xfrm);
	
	$("form input[type='submit']").click( function(){
		$(this.form).append("<input type='hidden' name='"+ this.name +"' value='"+ this.value +"' />");
	});
}

/*-----------------------*/

/* Ajax Load URL's hash*/
function xld(u)
{
	u = u.replace(/(.)*#/gi,'');
	if( !u.length) return;
	u += '&ajx=1';
	$('#maincontent').fadeOut( 500, function(){
		$('#maincontent').html( $('#prldr').html());
		$('#maincontent').fadeIn( 500, function(){
			$.get( u, function(r){
				$('#maincontent').fadeOut( 500, function(){
					$('#maincontent').html(r);
					xpr();
					$('#maincontent').fadeIn( 500);
				});
			});
		});
	});
}

/*-----------------------*/

/* Ajax Submit and load the form data*/
function xfrm()
{
	var u = $w.location.hash.replace(/(.)*#/gi,'');
	var file = false;
	$(this).find("input[type='file']").each(function(){
		if($(this).val() == '') return;
		file = true;
	});

	if( file || u.length == 0){ $(this).attr('action', u); return true;}

	var p = $(this).serialize();
	var m = $(this).attr('method');
	u += '&ajx=1';
	$('#maincontent').fadeOut( 500, function(){
		$('#maincontent').html( $('#prldr').html());
		$('#maincontent').fadeIn( 500, function(){
			if( m == 'post')
			{
				$.post( u, p, function(r){
						$('#maincontent').fadeOut( 500, function(){
							$('#maincontent').html(r);
							xpr();
							$('#maincontent').fadeIn( 500);
						});
				});
			}else{
				$.get( u, p, function(r){
						$('#maincontent').fadeOut( 500, function(){
							$('#maincontent').html(r);
							xpr();
							$('#maincontent').fadeIn( 500);
						});
				});
			}
		});			
	});
	return false;
}

/*-----------------------*/
/*Overload this fundtion for Ajax loading purposes*/

function goURL(u)
{
	if( typeof(u) != 'string') u = $(this).attr('href');
	$w.location.hash = u;
	return false;
}

/*-----------------------*/
/*Handelling Back and Forward options*/
var cu;
function BF()
{
	if( cu != $w.location.hash) xld( cu = $w.location.hash);
	setTimeout( 'BF()', 100);
}
/*-----------------------*/
/*Ready*/

$(document).ready(function(){ 
	xpr();
	BF();
});
