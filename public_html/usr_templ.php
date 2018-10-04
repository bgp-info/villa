<html>
<head>
<title>...Выставки -> Менеджер</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<link rel="stylesheet" href="/css/main.css" type="text/css">
<SCRIPT LANGUAGE="JavaScript">
<!-- 
function w_o(url,w,h,e) {
	param="top=10,left=10,width="+w+",height="+h+",resizable=1,scrollbars=1";
	window.open(url,'',param);
	return false
}
// -->
</SCRIPT>
</head>

<body bgcolor=efefef leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<center>
  <table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr> 
      <td valign=top align=center>
				
        <table border=0 cellspacing=0 cellpadding=0>
          <tr> 
            <td><img src="images/top1.jpg" width="370" height="129"></td>
            <td><img src="images/top2.jpg" width="300" height="129"></td>
            <td><img src="images/top3.jpg" width="331" height="129"></td>
          </tr>
          <tr> 
            <td bgcolor="#FFFFFF">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b> 
              <?=$manager?>
              </b></td>
            <td align=right><img src="images/top4.jpg" width="300" height="44"></td>
            <td bgcolor="#FFFFFF">
						  <table border=0 cellspacing=8 cellpadding=0 width=100%><form method=post name=form_default>
								<tr> 
								<td valign=middle><?=$default?>								
								</td>
								<td align=right><a href="exit.php">Выход</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
								</tr></form>
							</table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <br>
  <table class=small width=95% border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=efefef>
    <tr> 
      <td valign=top bgcolor="white">
        <p align="center"><?=$text_menu?></p><?=$error?>
        <p><?=$body?></p>
      </td>
    </tr>
  </table>
</center>
</body>
</html>
