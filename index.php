<?php
include('mysql.php');
$db=mysql_connect('localhost','USERNAME','PASSWORD');

define('HEAD','<!DOCTYPE html><html><head><title>Monopoly</title><script src="f.js"></script><script src="board.js"></script><link rel="stylesheet" type="text/css" href="style.css"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>');
mysql_select_db("mitxelaMain", $db); 
mysql_set_charset('utf8');
header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-cache, no-store, must-revalidate");
header('Expires: 0');

define('NUM_THEMES',4);
define('NUM_TOKENS',12);

if (get_magic_quotes_gpc()) {
  $process = array(&$_GET, &$_POST, &$_COOKIE);
  while (list($key, $val) = each($process)) {
    foreach ($val as $k => $v) {
      unset($process[$key][$k]);
      if (is_array($v)) {
        $process[$key][stripslashes($k)] = $v;
        $process[] = &$process[$key][stripslashes($k)];
      } else {
        $process[$key][stripslashes($k)] = stripslashes($v);
      }
    }
  }
  unset($process);
}
if(get_magic_quotes_runtime()) {
    set_magic_quotes_runtime(false);
}


if ($_POST['act']=='Start') {
  if (!isset($_POST['username']) or $_POST['username']=="") $errS="Please enter a username.";
  if (is_id($_POST["GameID"])) {
    function newPlayer($num){
      $data=Array(
        "GameID"=>clean($_POST['GameID']),
        "PlayerNum"=>$num,
        "Name"=>clean($_POST['username']),
        "PollTime"=>time(),
        "Password"=>clean(salt($_POST['password']))
      );
      return mysql_query("INSERT INTO monopolyPlayers ("
               .implode(", ",array_keys($data)).") VALUES ("
               .implode(", ",array_values($data)).")");
    }
    $game=getGame($_POST["GameID"]);
    if ($game){
      if ($game["Started"]=='1') {
        $errS="Error: That Game ID is already in use.";
      } else {
        $players=getPlayers($game["GameID"]);
        foreach ($players as $player) {
          if ($player["Name"]==$_POST['username']) $errS="Error: That username is already in use.";
        }
        if ($game["NumPlayers"]>=8) $errS="Error: The game you have tried to join already has 8 players.";
        if (!$errS) {
          newPlayer(++$game["NumPlayers"]);
          setGame($game, "NumPlayers");
          $_POST['act']='Log In';
        }
      }
    } else {
      if (!$errS) {
        mysql_query("INSERT INTO monopoly (GameID, NumPlayers, Started, StartTime) VALUES ('$_POST[GameID]',1,0,".time().")");
        newPlayer(1);
        $_POST['act']='Log In';
      }
    }
  } else {
    $errS="Error: Game ID can only be letters and numbers, up to 25 characters long.";
  }
}

// Log in
if ($_POST['act']=='Log In') {
  $pass=salt($_POST['password']);
  $user=$_POST['username'];
  $g=(isset($_POST['GameID']))?"AND GameID=".clean($_POST['GameID']):"";
  $result=mysql_query("SELECT * FROM monopolyPlayers WHERE Name=".clean($user)." AND Password='$pass' $g");
  switch (mysql_num_rows($result)) {
    case 1: 
      $player=mysql_fetch_assoc($result);
      bake($player['GameID'].$player['PlayerNum'].$player['Password']);
    break;
    case 0:
      $errL="Unrecognised username/password.";
    break;
    default:
      $errG=Array();
      while ($row=mysql_fetch_assoc($result)) {
        $errG[]=$row["GameID"];
      }
  }  
}


if ($_COOKIE['monopoly']) {
  $a=substr($_COOKIE['monopoly'],0,-33);
  $playerNum=substr($_COOKIE['monopoly'],-33,1);
  $c=substr($_COOKIE['monopoly'],-32,32);
  
  if (is_id($a) and is_numeric($playerNum) and is_hash($c)){
    if (getPlayer($a, "AND PlayerNum='$playerNum' AND Password='$c'")){ 
      $game=getGame($a);
      $players=getPlayers($game["GameID"]);
      require('main.php');
    }
  }
} 

require('loginForm.php');



//////////

function bake($value){
  setCookie("monopoly",$value);
  $_COOKIE['monopoly']=$value;
}
function is_id($text){
  return (preg_match('|^[0-9a-zA-Z]{1,25}$|', $text));
}
function is_hash($text){
  return (preg_match('|^[0-9a-fA-F]{32}$|', $text));
}
function salt($text){
  return (md5($text."j83uAyc"));
}
//We may need to protect is_numeric from eg 0123
function clean($value){
  if (!is_numeric($value)) $value = "'".mysql_real_escape_string($value)."'"; 
  return $value;  
}
function condense($data, $only){
  //Implode associative array for UPDATE query
  if ($only!="") return $only." = ".clean($data[$only]);
  $r="";
  $keys = array_keys($data);
  $values = array_values($data);
  for ($i=count($values); $i--;) {
    $r.=$keys[$i]." = ".clean($values[$i]).($i==0?"":", ");
  }
  return $r;
}


function getGame($id){
  return mysql_fetch_assoc( mysql_query("SELECT * FROM monopoly WHERE GameID = '$id'") );
}
function setGame($data, $only="") {
  return mysql_query("UPDATE monopoly SET ".condense($data, $only)." WHERE GameID = '$data[GameID]'");  
}

//getPlayer("GameID", "AND PlayerNum='2'")
//getPlayer("GameID", "AND Name='Bob'")
function getPlayer($id,$cond=""){
  return mysql_fetch_assoc( mysql_query("SELECT * FROM monopolyPlayers WHERE GameID = '$id' $cond") );
}
function setPlayer($data, $only=""){
  return mysql_query("UPDATE monopolyPlayers SET ".condense($data, $only)." WHERE GameID = '$data[GameID]' AND PlayerNum = '$data[PlayerNum]'");
}
function poll($data){
  return mysql_query("UPDATE monopolyPlayers SET PollTime='".time()."', Updates='{}' WHERE GameID = '$data[GameID]' AND PlayerNum = '$data[PlayerNum]'");
}

function getPlayers($id){
  $ret=Array();
  $result=mysql_query("SELECT * FROM monopolyPlayers WHERE GameID = '$id'");
  while ($row=mysql_fetch_assoc($result)) {
    $ret[$row['PlayerNum']]=$row;
  }
  return $ret;
}

function setPlayers($data, $only=""){
  $success=true;
  foreach ($data as $player) {
    $success&=setPlayer($player, $only);
  }
  return $success;
}

?>
