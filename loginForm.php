<?php require('redirect.php'); 

echo HEAD;

?>
<div style='width:100%;height:100%;position:absolute;left:0;top:0;background:rgba(255,255,255,0.6);text-align:center;'>
<div style='width:650px;margin:100px auto;text-align:left;'>

<h1><sup>mitxela's </sup> MONOPOLY</h1>

<?



if (isset($errG)) {

?>
<form method='post'>
Which game would you like to resume playing?<br><br>
<input type='hidden' name='username' value="<?=htmlentities($_POST['username'],ENT_COMPAT,"UTF-8");?>">
<input type='hidden' name='password' value="<?=htmlentities($_POST['password'],ENT_COMPAT,"UTF-8");?>">
<select name='GameID'>
<?php
foreach ($errG as $gid) {
  echo "<option value='$gid'>$gid</option>";
}
?>
</select> 
<input type='submit' name='act' value='Log In'>
</form>
</body></html>
<?php 
die();
}
?><style>
body{margin:0;}
label { 
	display:block;
	float:left;
  clear:left;
	width:150px;
	text-align:right;
  padding:2px;
  margin:5px;
}
input{
  padding:2px;
  margin:5px;
}
canvas {left:50%;top:0;margin-left:-425px;}
</style>
<a style='position:fixed;top:0;right:0;' href='#' onclick='reqAnim=function(){};hide(this);return false;'>stop this madness</a>

<?php

if (!$errL && !$errS) {?>
<div id='ch'>
<a href='#login'>Resume game</a><br/><br/>
<a href='#start'>Start or join a new game</a>
</div><?php
} else echo "<div style='color:red;'>$errS$errL</div><br><br>";
?>
<div id='lg' style='width:400px;<?php if (!$errL) echo "display:none;";?>'>
<form method='post' action='index.php?a=carry'>
Log in to resume game:<br>
<label for='username'>Name: </label><input type='text' name='username'><br>
<label for='password'>Password: </label><input type='password' name='password'><br>
<input type='submit' name='act' value='Log In'>
</form>
<br><a href='#'>Back</a>
</div>

<div id='st' style='width:600px;<? if (!$errS) echo "display:none;";?>'>
<form method='post' action='index.php?a=carry'>
To start a new game, decide on a Game ID with your friends. This is a reference so the program can find the other players. Also, choose a username and password to identify you.
<br>
<label for='GameID'>Game ID: </label><input type='text' name='GameID'><br>
<label for='username'>Name: </label><input type='text' name='username'><br>
<label for='password'>Password: </label><input type='password' name='password'><br>
<input type='submit' name='act' value='Start'>

</form>
<br><a href='#'>Back</a>
</div>

</div></div>


<script>
window.onload=function(){
  colours=["#964818","#00A6EC","#E50083","#F28F00","#E50005","#FFEE00","#019837","#004D9F"]
  board=setBoard(new Image(), new Image(), [], false); 
  board.iso(1.8);
  
  reqAnim(board.animate);
  
<?php if (!$errL && !$errS) { ?>
  window.onhashchange();
}

window.onhashchange=function(){
  switch (location.hash) {
    case "#login":
      hide($('st'));
      show($('lg')); document.forms[0].username.focus();
      hide($('ch'));
    break;
    case "#start":
      show($('st')); document.forms[1].GameID.focus();
      hide($('lg'));
      hide($('ch'));
    break;
    default:
      hide($('st'));
      hide($('lg'));
      show($('ch'));
  }
}
<?php } else echo "}"; ?>
</script>

</body></html>