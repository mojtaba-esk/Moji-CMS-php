<?php
	$SQL = 'SELECT * 
		FROM
			`'. Module::$name . '_main` 
		WHERE
			`lngId` = '. Lang::viewId().'
			AND
				`domId` = '. $_cfg['domain']['id'] .'
		ORDER BY
			`'. Module::$opt[ 'viewOrdrBy'] .'` '. Module::$opt[ 'viewOrdrType'] 
		.' LIMIT 20';

	$rws = DB::load( $SQL);
	
	error_reporting( 0);

	header('Content-type: text/xml;charset=$charset');
	print( '<?xml version="1.0" encoding="UTF-8" ?>');

	$lst = '';
	foreach( $rws as $rw)
	{
		$lst .= '<item><title>'. $rw[ 'title'] .'</title>';
		$lst .= '<description>'. htmlspecialchars( briefStr( isset( $rw['lead']) ? $rw['lead'] : strip_tags( $rw['body']), 500)) .'</description>';
		$lst .= '<link>'. URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name .'&mod=full&id='. $rw['rltdId']) .'</link>';
		$lst .= '<pubDate>'. date( 'Y-M-d G:i', $rw['insrtTime']) .'</pubDate>';
		//$lst .= '<guid>http://rightclick.ir/#Topic-'. $rw[ 'rltdId'] .'</guid>';
		$lst .= "</item>\r\n";

	}//End of foreach( $rws as $rw);

?><rss version="2.0">
<channel>
    <title><?php print( $_cfg['domain']['title']); ?></title>
    <link><?php print( $_cfg['URL']); ?></link>
    <docs><?php print( URL::rw( '?lng='. Lang::$info['shortName'] .'&md='. Module::$name)); ?>rss.xml</docs>
    <description><?php print( $_cfg['domain']['title']); ?></description>
	<?php print( $lst); ?>
</channel>
</rss>
<?php
	exit();
?>
