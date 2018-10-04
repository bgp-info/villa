<?
$root = $GLOBALS['_SERVER']['DOCUMENT_ROOT']; 
include_once ($root."/../private/func.inc");
?>

<html>
<head>

<script language="JavaScript"  type="text/javascript">
<!--//
var message="Нельзя так делать..."; 
function click(e) {
	if (document.all) {
		if (event.button == 2) {
			return false;
		}
	}
}
if (document.layers) {
document.captureEvents(Event.MOUSEDOWN);}
document.onmousedown=click;
// --> 
</script>
<title>Выставки</title>
<meta http-equiv='Content-Type' content='text/html; charset=windows-1251'>
<link rel=stylesheet href='/css.css' type='text/css'>
</head><body topmargin=0 bottommargin=0 rightmargin=0 leftmargin=0>

<?

$sql = "select * from stend where id = ".$cgi[id];
$sth=my_exec($sql);
$row = mysql_fetch_array($sth);

$size = getimagesize("./picture_stend/pic_".$row[dogovor_id].$row[ext]);
#print_arr($size);

echo "<script language=Javascript>window.resizeTo(".($size[0]+10).",".($size[1]+60).");</script>";
echo "<center><table width=100% height=100% border=0 cellspacing=0 cellpadding=1><tr><td>";
echo "<img src='/picture_stend/pic_".$row[dogovor_id].$row[ext]."' border=0>";
echo "</td></tr></table><br>";

echo "</body></html>\n";
?>