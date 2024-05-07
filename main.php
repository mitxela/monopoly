<?php require('redirect.php'); 

//$game, $players, $playerNum
$my =& $players[$playerNum];

//Shorthand location notation for spaces you can own
$shortHand=Array(-1,0,-1,1,-1,2,3,-1,4,5,6,7,8,9,10,11,12,-1,13,14,-1,15,-1,16,17,18,19,20,21,22,-1,23,24,-1,25,26,-1,27,-1,28);
// 0:cost to buy, 1-6: rent with 0-5 houses
$sHcost=Array(Array(60,2,10,30,90,160,250),Array(60,4,20,60,180,320,450),Array(200),Array(100,6,30,90,270,400,550),Array(100,6,30,90,270,400,550),Array(120,8,40,100,300,450,600),Array(0),Array(140,10,50,150,450,625,750),Array(150),Array(140,10,50,150,450,625,750),Array(160,12,60,180,500,700,900),Array(200),Array(180,14,70,200,550,750,950),Array(180,14,70,200,550,750,950),Array(200,16,80,220,600,800,1000),Array(220,18,90,250,700,875,1050),Array(220,18,90,250,700,875,1050),Array(240,20,100,300,750,925,1100),Array(200),Array(260,22,110,330,800,975,1150),Array(260,22,110,330,800,975,1150),Array(150),Array(280,22,120,360,850,1025,1200),Array(300,26,130,390,900,1100,1275),Array(300,26,130,390,900,1100,1275),Array(320,28,150,450,1000,1200,1400),Array(200),Array(350,35,175,500,1100,1300,1500),Array(400,50,200,600,1400,1700,2000));


function jsonMerge($json,$arr2){
  $arr1=json_decode($json, true);
  return json_encode( (object)(rec_merge($arr1,$arr2)) );
}

function rec_merge( $arr1, $arr2 ) {
  $keys = array_keys( $arr2 );
  foreach( $keys as $key ) {
    if( isset($arr1[$key]) && is_array($arr1[$key]) && is_array($arr2[$key])  && ($key!="P")) {
      $arr1[$key] = rec_merge( $arr1[$key], $arr2[$key] );
    } else {
      if (($key==="m" or $key==="g") and isset($arr1[$key])) $arr1[$key] .= $arr2[$key];
      else $arr1[$key] = $arr2[$key];
    }
  }
  return $arr1;
}

function bufferUpdates($arr, $meToo=false, $delay=false){
  global $players,$playerNum;  
  foreach ($players as &$n) {
    if ($n['PlayerNum']!=$playerNum || $meToo) 
      $n['Updates']= jsonMerge( $n['Updates'], $arr );
  }
  if (!$delay) setPlayers($players, "Updates");
}

function shutdownUpdates(){
  global $my, $cancel;
  if ($cancel) die();
  
  $up= mysql_fetch_row( mysql_query("SELECT Updates FROM monopolyPlayers WHERE GameID = '$my[GameID]' AND PlayerNum = '$my[PlayerNum]'") );
  if ($up[0]!="{}" && $up[0]!="") {
    echo $up[0];
    poll($my);
  }
}
register_shutdown_function('shutdownUpdates');

function errMsg($e){
  global $cancel; $cancel=true;
  die("{\"err\":\"$e\"}");
}
function consoleLog($e){
  file_put_contents("debug.txt",date("H:i:s")."\t$e\n",FILE_APPEND);
}

function showChoices(){
  global $game,$players,$my,$playerNum,$shortHand;
  if ($game['Turn']==0) {
    if ($my["Roll"]==0) {
      return "d";
    }
  } else if ($game['Turn']==$playerNum) {
    
    if ($my["Roll"]==0) {
      return "d";
      
    } else { //Landed on a space
      $location=$my["Location"];
      
      if (-1!=$shortHand[$location] && $location!=10) { //if an ownable property, not jail
        //if owned
        if ($game['Owned'] & (1<<$shortHand[$location])) {
          if ($my["Own"] & (1<<$shortHand[$location])) return ""; //Our property
          if (getHouseStatus($shortHand[$location])==6) return ""; //mortgaged
          return "p"; //Pay rent
        }else
          return "b"; //Buy
      }
          
          //if tax
            //same as rent
          //if community chest/chance
            //some external theme call.
          //Jail, free parking, gotojail
    
    
    }
    
    
  }
  return "";
}
function getValue($choice){
  //This function is only used to remind the client how much they owe.
  global $my,$shortHand;
  switch ($choice) {
    case "p": return getRent($shortHand[$my["Location"]]);
    //tax etc?
  }
  return "";
}

function addToLog($g){
  global $game;
  bufferUpdates(Array("g"=>$g),true);
  $game['GameLog']=limit20K($game['GameLog'].$g,"<hr>");
  setGame($game,"GameLog");
}
function limit20K($text,$needle){
  if (strlen($text) > 20480) {
    $text= substr( $text, -20480);
    $text= substr( $text, strpos($text, $needle) );
  }
  return $text;
}


function sendUpdate($pNum,$arr,$call=false){
  global $players;
  // This function to be used in conjunction with addToLog. Otherwise set call=true
  $players[$pNum]['Updates']= jsonMerge( $players[$pNum]['Updates'], $arr );
  if ($call) setPlayer($players[$pNum],"Updates");
}

function getProperties($bits){
  global $shortHand; $r=Array();
  for ($i=0;$i<40;$i++) {
    if (($shortHand[$i]!=-1) && ($bits & (1<<$shortHand[$i])))
      $r[]=$i;
  }
  return $r;
}
//For transactions
function getMortStats($bits,$mortBits){
  global $shortHand; 
  $r=Array();
  foreach ($shortHand as $num=>$v)
    if ($v!=-1 &&($bits&(1<<$v))) 
      $r["$num"]=($mortBits&(1<<$v))?6:0;
  return $r;
}

function getHouseStatus($num){
  global $game;
  //We're dealing with so many booleans here, honestly, this was the best way to do it.
  return ((bool)($game["House1"]&(1<<$num)))
        |((bool)($game["House2"]&(1<<$num)) <<1)
        |((bool)($game["House3"]&(1<<$num)) <<2);
}
function setHouseStatus($num,$status){
  global $game;
  $mask=~(1<<$num);
  $game["House1"]=($game["House1"] & $mask) | (((bool)($status&1))<<$num);
  $game["House2"]=($game["House2"] & $mask) | (((bool)($status&2))<<$num);
  $game["House3"]=($game["House3"] & $mask) | (((bool)($status&4))<<$num);
  setGame($game, "House1");
  setGame($game, "House2");
  setGame($game, "House3");
}
function getRent($shl){
  global $sHcost,$players,$my;
  $status=getHouseStatus($shl);
  if ($status==0){
    $set=ownSet($shl);
    switch ($shl){
      case 8: case 21: //Utilities
        return (4+6*$set)*($my["Roll"][0]+$my["Roll"][1]);
      case 2: case 11: case 18: case 26: //Railroads
        return 12.5*(1<<$set);
      default:
        return $sHcost[$shl][1]*(1+$set); //Double rent if they own the set.
    }
  }
  //if ($status!=6) //implicit, this should never be called on a mortgaged property
  return $sHcost[$shl][$status+1];
}
function ownSet($shl, $pNum=0){
  global $players;
  if ($pNum==0) $pNum=findOwner($shl);
  $v= $players[$pNum]["Own"];
  switch ($shl){
    case 0: case 1: //Brown
      return (($v&3)==3);
    case 3: case 4: case 5: //light blue
      return (($v&56)==56);
    case 7: case 9: case 10: //pink
      return (($v&1664)==1664);
    case 12: case 13: case 14: //orange
      return (($v&28672)==28672);
    case 15: case 16: case 17: //red
      return (($v&229376)==229376);
    case 19: case 20: case 22: //yellow
      return (($v&5767168)==5767168);
    case 23: case 24: case 25: //green
      return (($v&58720256)==58720256);
    case 27: case 28: //navy
      return (($v&402653184)==402653184);
    case 8: case 21: //utilities
      return (($v&2097408)==2097408);
    case 2: case 11: case 18: case 26: //railroads
      return (bool)($v&4)+(bool)($v&2048)+(bool)($v&262144)+(bool)($v&67108864);
  //6 = jail
  }
}
function findOwner($shl){ //Return by reference?
  global $players;
  foreach ($players as $v) {
    if ($v["Own"] & (1<<$shl)) return $v["PlayerNum"];
  }
}

function getGroup($shl){
  switch ($shl){
    case 0: case 1: //Brown
      return Array(0,1);
    case 3: case 4: case 5: //light blue
      return Array(3,4,5);
    case 7: case 9: case 10: //pink
      return Array(7,9,10);
    case 12: case 13: case 14: //orange
      return Array(12,13,14);
    case 15: case 16: case 17: //red
      return Array(15,16,17);
    case 19: case 20: case 22: //yellow
      return Array(19,20,22);
    case 23: case 24: case 25: //green
      return Array(23,24,25);
    case 27: case 28: //navy
      return Array(27,28);
    default:
      return Array();
  }
}



switch ($_GET['a']) {
  case 'logout':
    $my["PollTime"]=time()-101;
    setPlayer($my,"PollTime");
    setCookie("monopoly","",-1);
  case 'carry':
    header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/index.php");
  break;
  
  case "DEBUG":
    function debug($r, $level="  "){
      $bitwise=Array("Owned","House1","House2","House3","Own","noMortFees","FromP","ToP","Mortgaged");
      foreach ($r as $k=>$v) {
        if (is_array($v)) {
          $d.= "$level$k\n".debug($v,"$level  ");
        } else {
          if (in_array($k,$bitwise)) $v=str_pad(decbin($v), 32, "0", STR_PAD_LEFT);
          $k=str_pad($k,13);
          $d.= "$level$k$v\n";
        }
      }
      return $d;
    }
    $game["Messages"]="[Messages]";
    $game["GameLog"]="[GameLog]";
    $query=mysql_query("SELECT * FROM monopolyTransactions WHERE GameID = '$game[GameID]'");
    while ($row=mysql_fetch_assoc($query)) $trades[]=$row;
    die("<pre>GAME\n".debug($game)."\n\nPLAYERS\n".debug($players)."\n\nTRADES\n".debug($trades)."</pre>");
  break;
  
  case "m": 
    $m=htmlentities(preg_replace("/([^\s]{31})/u", "$1 ", $my["Name"].": ".$_GET['m']), ENT_COMPAT, "UTF-8")."<br>";
    $game['Messages']=limit20K($game['Messages'].$m, "<br>");
    bufferUpdates(Array("m"=>$m));
    setGame($game,"Messages");
    $cancel=true;
  break;

  case "o": //Options before game init
    if ($game["Started"]==1) die();
    
    
    if ($_GET['J']=='0' or $_GET['J']=='1') {
      $game["Options"]&=~1;
      $game["Options"]|=($_GET['J']<<0);
      bufferUpdates(Array("O"=>Array("J"=>$_GET['J'])) ,true);
      setGame($game,"Options");
    }
    if ($_GET['B']=='0' or $_GET['B']=='1') {
      $game["Options"]&=~2;
      $game["Options"]|=($_GET['B']<<1);
      bufferUpdates(Array("O"=>Array("B"=>$_GET['B'])) ,true);
      setGame($game,"Options");
    }
    if ($_GET['A']=='0' or $_GET['A']=='1' or $_GET['A']=='2') {
      bufferUpdates(Array("O"=>Array("A"=>$_GET['A'])) ,true);
      if ($_GET['A']=='2') $_GET['A']=3;
      $game["Options"]&=~12;
      $game["Options"]|=($_GET['A']<<2);
      setGame($game,"Options");
    }
    if (is_numeric($_GET['N']) && $_GET['N']>=0 && $_GET['N']<NUM_THEMES) {
      $game["Theme"]=$_GET['N'];
      bufferUpdates(Array("O"=>Array("T"=>$_GET['N'])) ,true);
      setGame($game,"Theme");
    }
    
    
    if ($_GET['r']=='0' or $_GET['r']=='1') {
      $my['Ready']=$_GET['r'];
      bufferUpdates(Array("p"=>Array("$playerNum"=>Array("a"=>$_GET['r']))) ,true);
      setPlayer($my,"Ready");
    }
    if (is_numeric($_GET['t'])) {
      if ($_GET['t']>NUM_TOKENS) die();
    
      $my['Token']=$_GET['t'];
      bufferUpdates(Array("p"=>Array("$playerNum"=>Array("t"=>$_GET['t']))) ,true);
      setPlayer($my,"Token");
    }
    
    if (count($players)>=2) {
      foreach ($players as &$n) {
        if ($n['Ready']!="1") die();
        if(++$tl[$n['Token']] > 1) die();
      }
      $game["Started"]=1;
      setGame($game,"Started");  
      $g="Welcome! To begin, everyone should roll their dice to see who goes first.<br>";
      $game['GameLog'].=$g;
      setGame($game,"GameLog");
      //Need to also send everything that you would on load=all,started.... ?
      //We know everyone's first choice is dice roll
      for ($i=count($players);$i--;) {
        $locations[$i+1]=0;
        $monies[$i+1]=1500;
      }
      bufferUpdates(Array("g"=>$g,"s"=>"1","q"=>"d","L"=>$locations,"M"=>$monies),true);
    }
  break;
  
  case "q": //Make a choice
    $advanceTurn=false; $g="";
    
    if ($game["Started"]==0) die();
    if ($game["Turn"]!=0 and $game["Turn"]!=$playerNum) die();
    $choices=showChoices();
    
    $shl=$shortHand[$my["Location"]];
    
    if ($_GET['q']=="d") {
      if ($my["Roll"]!=0) die(); 
     
      if ($game["Turn"]==0) {
        do{  //Ensure we don't roll the same as someone else
          $conflict=false;
          $d1=mt_rand(1,6);
          $d2=mt_rand(1,6);
          foreach ($players as $player) {
            if ($d1+$d2== $player["Roll"][0]+$player["Roll"][1]) $conflict=true;
          }
        } while ($conflict);
      } else {
        $d1=mt_rand(1,6);
        $d2=mt_rand(1,6);
      }
      $my["Roll"]=$d1.$d2; //$d1+$d2;
      setPlayer($my,"Roll");
      sendUpdate($playerNum, Array("q"=>""));
      $g.="<A$playerNum> rolls a <D$d1> and a <D$d2>.<br>";
      
      
      if ($game["Turn"]==0) {
        $roll=Array(0,0,0,0,0,0,0,0,0,0,0,0);
        foreach ($players as &$n) {
          if ($n["Roll"]==0) {addToLog($g);die();} //someone hasn't rolled yet
          $roll[$n["Roll"][0] + $n["Roll"][1]]= $n['PlayerNum'];
        }
        $roll=array_filter(array_reverse($roll,true));
        
        $game['TurnOrder']=$order=implode($roll);
        setGame($game, "TurnOrder");
        
        foreach ($roll as $p) { //Make this more efficient at some point.
          $rNames[]="<A$p>";
        } 

        $game["Turn"]=$order[0];
        setGame($game, "Turn");
        foreach ($players as &$n){
          $n["Roll"]=0;
          setPlayer($n,"Roll");
        }
        sendUpdate($game["Turn"], Array("q"=>"d"));
        $g.="The order has been decided!<br>".implode(" &rarr; ",$rNames)."<hr>It's $rNames[0]'s turn.<hr>";
          
      }else{
        //Main action when dice are rolled
        //Set new location.
        $my["Location"]+=$d1+$d2;
        
        $g.="<A$playerNum> advances to <B".($my["Location"]%40).">.<hr>";
        if ($my["Location"]>=40) {
          $my["Location"]-=40;
          $my["Money"]+=200;
          $g.="<A$playerNum> collects <M>200 for passing <B0>.<hr>";
          
          setPlayer($my,"Money");
        }
        setPlayer($my,"Location");
        
        $choices=showChoices();
        
        if ($my['noMortFees']) {
          $my['noMortFees']=0;
          setPlayer($my, 'noMortFees');
          sendUpdate($playerNum, Array("u"=>Array()));
        }
        
        sendUpdate( $playerNum, 
            Array("M"=>Array("$playerNum"=>$my["Money"]),
                  "L"=>Array("$playerNum"=>$my["Location"]),
                  "q"=>$choices,
                  "v"=>getValue($choices)
            ));
        bufferUpdates(Array("L"=>Array("$playerNum"=>$my["Location"])),false,true);
        
        if ($choices=="") $advanceTurn=true;
      }
     } else if (isset($_GET["b"])) {
       if ($choices!="b") die();

       if ($_GET['b']=="1") {
         //Buy property
         if ($my["Money"]>$sHcost[$shl][0]) {
           $g.="<A$playerNum> buys <B".$my["Location"]."><hr>";
           $game['Owned'] |= (1<<$shl);
           setGame($game,'Owned');
           $my['Own'] |= (1<<$shl);
           setPlayer($my,"Own");
           $my["Money"]-=$sHcost[$shl][0];
           setPlayer($my,"Money");
           
           sendUpdate( $playerNum, Array(
             "P"=>getProperties($my['Own']),
             "M"=>Array("$playerNum"=>$my["Money"])
             ) );
         } else $g.="<A$playerNum> wants to buy <B".$my["Location"]."> but can't afford it.<hr>";
       } else {
         //Don't buy. Put up for auction if enabled.
     
         $g.="<A$playerNum> chooses not to buy <B".$my["Location"]."><hr>";
       
       }
       $advanceTurn=true;
       
     } else if ($_GET['q']=='p') {
       if ($choices!="p") die();
       //Pay rent
       
       
       $rent=getRent($shl);
       if ($my["Money"]>=$rent){
         $owner=findOwner($shl);
         $my["Money"]-=$rent;
         $players[$owner]["Money"]+=$rent;
         setPlayer($my,"Money");
         setPlayer($players[$owner],"Money");
         sendUpdate( $playerNum, Array("M"=>Array("$playerNum"=>$my["Money"])) );
         sendUpdate( $owner, Array("M"=>Array("$owner"=>$players[$owner]["Money"])) );
         $advanceTurn=true;
         $g.="<A$playerNum> pays <A$owner> <M>$rent in rent.<hr>";
       }
     }
     
     
     if ($advanceTurn) {
       $my["Roll"]=0;
       setPlayer($my,"Roll");
       sendUpdate( $playerNum, Array("q"=>"") );
      
       $order=str_split($game['TurnOrder']);       
       $game["Turn"]=$order[ (array_search($game["Turn"],$order)+1) % $game["NumPlayers"] ];

       setGame($game, "Turn");
       sendUpdate($game["Turn"], Array("q"=>"d"));
       $g.="It's <A".$game["Turn"].">'s turn.<br>";
     }
     addToLog($g);
     
  
  break;
  
  case "x": //Change the state of a property
    $loc=$_GET['h'];
    $act=$_GET['j'];
    if (!is_numeric($loc)) die();
    $shl=$shortHand[$loc];
    if (!($my['Own']& (1<<$shl))) die();
    $status=getHouseStatus($shl);
    
    if ($act=='h') {
      if ($status!=0) errMsg("You must dismantle all buildings before you can mortgage a property.");    
      //Mortgage it
      setHouseStatus($shl, 6);
      $my["Money"]+=$sHcost[$shl][0]/2;
      setPlayer($my,"Money");
      sendUpdate( $playerNum, Array("H"=>Array( "$loc" => 6 ), "M"=>Array("$playerNum"=>$my["Money"])) );
      addToLog("[ <A$playerNum> has just mortgaged <B$loc>. ]<hr>");
    } else if ($act=='j' && $status==6 && $my["Money"]>$sHcost[$shl][0]*0.55) {
      //unMortgage it
      setHouseStatus($shl, 0);
      $uMC=0.55;
      if ($my["noMortFees"] & (1<<$shl)){
        $uMC-=0.05;
        $my["noMortFees"] ^= (1<<$shl);
        setPlayer($my,"noMortFees");
        //Send nointerest update....
        sendUpdate($playerNum, Array("u"=>getProperties($my["noMortFees"])));
      }
      $my["Money"]-=floor($sHcost[$shl][0]*$uMC);
      setPlayer($my,"Money");
      sendUpdate( $playerNum, Array("H"=>Array( "$loc" => 0 ), "M"=>Array("$playerNum"=>$my["Money"])) );
      addToLog("[ <A$playerNum> has just unmortgaged <B$loc>. ]<hr>");
      
    } else if ($act=='w' && $shl!=8 && $shl!=21 && $shl!=6) {
      //Buy house
      $cost=50*(ceil($loc/10));
      if ($my["Money"] > $cost) {
        if (true!==ownSet($shl, $playerNum)) errMsg("You must own the complete group to start building.");
        //Apparently you can build at any time.
        //if ($game["Turn"]!=$playerNum || $my["Roll"]!=0) errMsg("You can only build at the start of your go.");
        if ($status==5) errMsg("You cannot build any more on this property.");
        
        if ($status<4 && $game["HousesInUse"]>=32) errMsg("Limited building supplies. There are already 32 <H1> on the board.");
        if ($status==4 && $game["HotelsInUse"]>=12) errMsg("Limited building supplies. There are already 12 <H3> on the board.");
        
        $group=getGroup($shl);
        foreach ( $group as $num ) {
          if (($st=getHouseStatus($num)) ==6) errMsg("You cannot build if a property in the group is mortgaged.");
          if ($st<$status) errMsg("You must build evenly across the group.");
        }
        
        if ($status==4) {$game["HotelsInUse"]++; setGame($game,"HotelsInUse"); $game["HousesInUse"]-=4;}
        else $game["HousesInUse"]++;
        setGame($game,"HousesInUse");
        
        setHouseStatus($shl, 1+$status);
        $my["Money"]-=$cost;
        setPlayer($my,"Money");
        sendUpdate( $playerNum, Array( "M"=>Array("$playerNum"=>$my["Money"])) );
        bufferUpdates(Array("H"=>Array( "$loc" => 1+$status )),true,true);
        addToLog("<A$playerNum> builds a <H".($status==4?2:0)."> on <B$loc>.<hr>");
      } else errMsg("Insufficient Funds");
    
    } else if ($act=='e') {
      //Sell house
      $selling=1;
      if ($status>0 && $status<6) {
        
        $group=getGroup($shl);
        foreach ( $group as $num ) {
          if (getHouseStatus($num)>$status) errMsg("You must dismantle buildings evenly across the group.");
        }
        if ($status==5) {
          $game["HotelsInUse"]--; setGame($game,"HotelsInUse");
          $selling+=min(4,max($game["HousesInUse"]-28,0));
          $game["HousesInUse"]+=5-$selling;
        } else $game["HousesInUse"]--;
        setGame($game,"HousesInUse");
        
        $cost=$selling*25*(ceil($loc/10));
        setHouseStatus($shl, $status-$selling);
        $my["Money"]+=$cost;
        setPlayer($my,"Money");
        $r=Array( "M"=>Array("$playerNum"=>$my["Money"]));
        if ($selling>1) $r["err"]="Note: Due to building shortages, you received the monetary equivalent for ".($selling-1)." <H".($selling==2?0:1).">.";
        sendUpdate( $playerNum, $r );
        bufferUpdates(Array("H"=>Array( "$loc" => $status-$selling )),true,true);
        addToLog("<A$playerNum> sells a <H".($status==5?2:0)."> on <B$loc> back to the bank.<hr>");
      }
    }
    
    
  break;
  
  
  case "t": //Transaction
    if ($game["Started"]==0) die();
  
    function getBits($properties,$ownbits){
      global $shortHand;
      $bits=0;
      for ($i=count($properties);$i--;) {
        $loc=(int)$properties[$i];
        if ($loc<1 or $loc >39 or $shortHand[$loc]==-1) return false;
        $bit=(1<<$shortHand[$loc]);
        if (!($ownbits&$bit)) return false;
        $bits|= $bit;
      }
      return $bits;
    }
    //A snapshot of the mortgage status of the properties being traded
    function getMortBits($bits){
      global $shortHand; 
      $r=0;
      foreach ($shortHand as $num=>$v) {
        if ($v!=-1 && (($bits&(1<<$v))==(1<<$v))){
          $stat=getHouseStatus($v);
          if ($stat==6) $r|=(1<<$v);
          else if ($stat>0) return false;
        }
      }
      return $r;
    }
    function resyncTrades() {
      global $cancel; $cancel=true;    
      die('{"err":"Transaction error","t":0,"z":0}');
    }
  
    if ($_GET['t']=="n"){ //New transaction
      
      //Valid player to interact with
      $p=(int)$_GET['p'];
      if ($p<1 or $p>$game['NumPlayers'] or $p==$playerNum) die();
      //All properties ownable and owned by respective
      $mP=$_GET['mP']?getBits(explode(",",$_GET['mP']),$my["Own"]):0;
      $yP=$_GET['yP']?getBits(explode(",",$_GET['yP']),$players[$p]["Own"]):0;
      $mortBits=getMortBits($mP|$yP);

      //Money valid amounts
      $mM=(int)($_GET['mM']);
      $yM=(int)($_GET['yM']);
      //Both parties must be trading something
      if ($mM<0 or $mM> $my["Money"] or $yM<0 or $yM> $players[$p]["Money"]
        or ($mM==0 and $mP==0) or ($yM==0 and $yP==0) or $yP===false or $mP===false or $mortBits===false) 
          errMsg("Invalid offer. (One or both players' assets have changed?)");
      
      
      //remove any completed transactions with the same players
      mysql_query("DELETE FROM monopolyTransactions WHERE pFrom=$playerNum AND pTo=$p AND (Status>2 OR Status=0)");
      
      mysql_query("INSERT INTO monopolyTransactions (GameID, pFrom, pTo, FromP, FromM, ToP, ToM, Status, Mortgaged) "
                 ."VALUES ('$game[GameID]', $playerNum, $p, $mP, $mM, $yP, $yM, 0, $mortBits)");
      $id=mysql_insert_id();
      
      //Propagate this to another request, to ensure this isn't a clickjacking attempt
      $cancel=true;
      die("{\"dt\":$id}");

    }
    
    $id=(int)$_GET['id'];
    $trade=mysql_fetch_assoc( mysql_query("SELECT * FROM monopolyTransactions WHERE id = '$id'") );
    if ($trade['GameID']!==$game['GameID']) die();
    
    switch ($_GET['t']) {
      case "c": //Confirm transaction
        if ($trade['pFrom']==$playerNum && $trade["Status"]==0) {
          mysql_query("UPDATE monopolyTransactions SET Status=1 WHERE id=$id");
          sendUpdate($trade['pTo'], Array(
            "y"=>Array("$id"=>1), 
            "o"=>Array("$id"=> Array(
              $playerNum,
              getProperties($trade['FromP']),$trade['FromM'],
              getProperties($trade['ToP']),  $trade['ToM'], 
              getMortStats((int)$trade['ToP']| (int)$trade['FromP'],$trade['Mortgaged'])
              ))
            ),true);
        } else resyncTrades();
      break;
      case "w": //Withdraw Transaction
        if ($trade['pFrom']==$playerNum && ($trade["Status"]==1 || $trade["Status"]==2)) {
          mysql_query("UPDATE monopolyTransactions SET Status=5 WHERE id=$id");
          sendUpdate($trade['pTo'], Array("y"=>Array("$id"=> 5 )),true);
        } else resyncTrades();
      break;
      case "r": //Refuse Transaction
        if ($trade['pTo']==$playerNum && ($trade["Status"]==1 || $trade["Status"]==2)) {
          mysql_query("UPDATE monopolyTransactions SET Status=4 WHERE id=$id");
          sendUpdate($trade['pFrom'],Array( "y"=>Array("$id"=> 4 ), "R"=>"$id"),true);
        } else resyncTrades();
      break;
      case "d": //Defer Transaction
        if ($trade['pTo']==$playerNum && $trade["Status"]==1) {
          mysql_query("UPDATE monopolyTransactions SET Status=2 WHERE id=$id");
          sendUpdate($trade['pFrom'],Array( "y"=>Array("$id"=> 2 )),true);
        } else resyncTrades();
      break;
      case "a": //Accept transaction
        if ($trade['pTo']==$playerNum && ($trade["Status"]==1 || $trade["Status"]==2)) {
        
          function tFail($msg="Invalid offer. (One or both players' assets have changed?)"){
            global $trade, $id;
            mysql_query("UPDATE monopolyTransactions SET Status=6 WHERE id=$id");
            
            sendUpdate($trade['pFrom'],Array( "y"=>Array("$id"=> 6 ) ),true);
            sendUpdate($playerNum,     Array( "y"=>Array("$id"=> 6 ), "err"=>$msg),true);
            die();
          }
        
          $yo=& $players[$trade['pFrom']];
          $trade['ToP']=(int)$trade['ToP'];
          $trade['FromP']=(int)$trade['FromP'];
          
          
          if ((($trade['ToP'] & $my["Own"]) !=$trade['ToP'])
           or (($trade['FromP']&$yo["Own"]) !=$trade['FromP'])) tFail();
          
          function getMortCosts($bits){
            global $shortHand,$sHcost;
            $mC=0;
            $r=getProperties($bits);
            for ($i=count($r);$i--;){
              $shl=$shortHand[$r[$i]];
              $stat=getHouseStatus($shl);
              if ($stat==6) $mC+=floor($sHcost[$shl][0]*0.05);
              else if ($stat>0) return false;
            }
            return $mC;
          }
          if ((false===($mortCostFrom=getMortCosts($trade['FromP'])))
           or (false===($mortCostTo = getMortCosts( $trade['ToP'] ))) ) tFail("Transaction Error: One or more properties are developed.");
          
          if (getMortBits($trade['ToP']|$trade['FromP'])!=$trade['Mortgaged']) tFail();
          
          //You pay the mortgage interest on the property you are receiving.
          if ($my['Money']< $mortCostFrom + $trade['ToM']) errMsg("Insufficient Funds.");
          if ($yo['Money']< $mortCostTo + $trade['FromM']) errMsg("Other party has insufficient funds.");
          
          //Complete transaction
          $my['Money']+=  $trade['FromM'] - $trade['ToM'] - $mortCostFrom;
          $yo['Money']+=  $trade['ToM'] - $trade['FromM'] - $mortCostTo;
          
          $bits=$trade['FromP']|$trade['ToP'];
          $my['Own']^=$bits;
          $yo['Own']^=$bits;

          $my["noMortFees"]=getMortBits($trade['FromP']);
          $yo["noMortFees"]=getMortBits($trade['ToP']);

          setPlayer($my, 'Money'); setPlayer($my, 'Own'); setPlayer($my, 'noMortFees');
          setPlayer($yo, 'Money'); setPlayer($yo, 'Own'); setPlayer($yo, 'noMortFees');
          
          mysql_query("UPDATE monopolyTransactions SET Status=3 WHERE id=$id");
          sendUpdate($trade['pFrom'],Array( 
            "y"=>Array("$id"=> 3 ),
            "M"=>Array("$trade[pFrom]"=>$yo["Money"]),
            "P"=>getProperties($yo['Own']),
            "H"=>getMortStats($bits,$trade['Mortgaged']),
            "u"=>getProperties($yo["noMortFees"]),
            "R"=>"$id"
            ));
          sendUpdate($playerNum,Array( 
            "y"=>Array("$id"=> 3 ),
            "M"=>Array("$playerNum"=>$my["Money"]),
            "P"=>getProperties($my['Own']),
            "H"=>getMortStats($bits,$trade['Mortgaged']),
            "u"=>getProperties($my["noMortFees"])
            ));
          
          addToLog("[<A$playerNum> and <A$trade[pFrom]> have made a deal.]<hr>");
        } else resyncTrades();
      break;
    }
  
  
  break;
  
  
  
  case "p": //Generic Poll
    $cancel=true;
    
    if ($_GET['load']=="all") {
      //Messages have already been escaped.
      $r=Array("m"=>$game['Messages'],"g"=>$game['GameLog']);
      
      if ($game['Started']) {
        $r["s"]="1";
        $r["q"]=showChoices();
        $r["v"]=getValue($r["q"]);
        $r["P"]=getProperties($my['Own']);
        $r["u"]=getProperties($my["noMortFees"]);

        foreach ($shortHand as $num=>$v)
          if ($v!=-1 && ($stat=getHouseStatus($v))) 
            $r["H"]["$num"]=$stat;
        
        //load relevant player data
        foreach ($players as $n) {
          $r["p"][$n["PlayerNum"]]=Array(
            "n"=>htmlentities($n["Name"], ENT_COMPAT, "UTF-8"),
            "t"=>$n["Token"],
            );
          $r["L"][$n["PlayerNum"]]=$n["Location"];
          $r["M"][$n["PlayerNum"]]=$n["Money"];
        }
        
        //Load all offers to me where status=1
        $result=mysql_query("SELECT * FROM monopolyTransactions WHERE GameID = '$game[GameID]' AND pTo = '$playerNum' AND Status = 1");
        while ($row=mysql_fetch_assoc($result)) {
          $r["o"][$row['id']]=Array(
            $row['pFrom'],
            getProperties($row['FromP']),$row['FromM'],
            getProperties($row['ToP']),  $row['ToM'],
            getMortStats((int)$row['ToP']|(int)$row['FromP'],$row['Mortgaged'])
            );
          $r["y"][$row['id']]=1;
        }

      } else {
        //Load=all when game hasn't started probably indicates a new player.
        bufferUpdates(Array("p"=>Array("$playerNum"=>Array(
            "n"=>htmlentities($my["Name"], ENT_COMPAT, "UTF-8"),
            "a"=>$my["Ready"],
            "t"=>$my["Token"],
            ))));
        
        
        
        $r["O"]=Array(
            "T"=>$game["Theme"],
            "J"=>(int)(bool)($game["Options"]&1),
            "B"=>(int)(bool)($game["Options"]&2),
            "A"=>(int)(bool)($game["Options"]&4)+(int)(bool)($game["Options"]&8)
            );
        
        $r["s"]="0";
        foreach ($players as $n) {
          $r["p"][$n["PlayerNum"]]=Array(
            "n"=>htmlentities($n["Name"], ENT_COMPAT, "UTF-8"),
            "a"=>$n["Ready"],
            "t"=>$n["Token"],
            );
        }
      }
      echo json_encode($r);
      
    } else if ($_GET["load"]=="stats"){
      if ($game['Started']==0) die();
    
      $r["id"]=$game["GameID"];
      $r["st"]=time()-$game["StartTime"];
      $r["tu"]=$game["Turn"];
      //Properties that are not owned, hide jail (bit 6).
      $r["un"]=getProperties( ($game["Owned"] ^ 0x1FFFFFBF) );
      
      foreach ($players as $n) {
        $r["p"][$n["PlayerNum"]]["P"]=getProperties($n['Own']);
        $r["p"][$n["PlayerNum"]]["i"]=time()-$n["PollTime"];
        $r["M"][$n["PlayerNum"]]=$n["Money"];
      }
      
      //Need to know the mortgaged ones - probably don't need to update houses?
      foreach ($shortHand as $num=>$v)
        if ($v!=-1 && (6==getHouseStatus($v))) 
          $r["H"]["$num"]=6;
    
      //Load all transactions and offers
      $result=mysql_query("SELECT * FROM monopolyTransactions WHERE GameID = '$game[GameID]' AND pTo = '$playerNum'");
      while ($row=mysql_fetch_assoc($result)) {
        $r["z"][$row['id']]=Array($row['pFrom'],
          getProperties($row['FromP']),$row['FromM'],
          getProperties($row['ToP']),  $row['ToM'],
          getMortStats((int)$row['ToP']|(int)$row['FromP'],$row['Mortgaged']));
        $r["y"][$row['id']]=$row['Status'];
      }
      $result=mysql_query("SELECT * FROM monopolyTransactions WHERE GameID = '$game[GameID]' AND pFrom = '$playerNum'");
      while ($row=mysql_fetch_assoc($result)) {
        $r["t"][$row['id']]=Array($row['pTo'],
          getProperties($row['FromP']),$row['FromM'],
          getProperties($row['ToP']),  $row['ToM'],
          getMortStats((int)$row['ToP']|(int)$row['FromP'],$row['Mortgaged']));
        $r["y"][$row['id']]=$row['Status'];
      }
    
    
      echo json_encode($r);
      
    } else {
      $query="SELECT Updates FROM monopolyPlayers WHERE GameID = '$game[GameID]' AND PlayerNum = '$playerNum'";
      $out=$my['Updates'];
      /* //Long poll
      for ($i=75;$i--;) {
        if ($out!="" and $out!="{}") {echo $out; break;}
        usleep(400000);
        $out=mysql_fetch_row(mysql_query($query));
        $out=$out[0]; 
      }
      */
      
      if ($out!="" and $out!="{}") {echo $out;}
      else {
        $out=mysql_fetch_row(mysql_query($query));
        $out=$out[0]; 
        if ($out!="" and $out!="{}") {echo $out;}
      }
    
    }
    
    poll($my);
  break;
    
    
/*\
|*|  TTD
|*|  
|*|  
|*|  
|*|  Tax. Free parking Jackpot.
|*|  
|*|  Jail.
|*|  Bankruptcy.
|*|  Community chest and Chance cards.
|*|  Auctions

?- change fieldsets and legends into just divs, sort out borders
?- All instances of shorthand=-1, check jail too.


Investigate [OR REMOVE] setGame, setPlayer condense - might not even be needed but make sure it's working.



|*|
\*/
  case "reload":
    $cancel=true;
    require("theme.php");
    echo "data.init=1;main();";
  break;

  default:
    $cancel=true;
    echo HEAD;
?>
<div style='display:none;'>
<!--Preload images-->
<img src='dice.png'>
<img src='rolling.gif'>
<img src='loading.gif'>
</div>

<div id='pageLoading'>Loading...</div>

<div id='alertBox' onclick='if(event.target.id==this.id) {simAlert();}'><div id='alertInner'>
  <a href='#' id='close' onclick='simAlert();return false;'>X</a>
  <div id='Alert'></div>
</div></div>

<div id='statusBox' onmousedown='this.style.zIndex=++zG;' class='bb'>
  <div style='float:right;text-align:right;'>
    <a href='?a=logout'>Log Out</a><br>
    <a id='statLink' style='display:none;' href='#' onclick='showStats();return false;'>Game Stats</a>
    <a href='#' onclick='showTrades();return false;'>Trades</a>
  </div>
  <div id='status'></div>
  <div id='choices'></div>
</div>


<fieldset id="chatBox"><legend class='bb'>Chat</legend>
 <div id='msg' class='bb'></div>
 <form id='chatForm'>
  <input id='chatSend' type='submit' value='Send'>
  <div style='overflow:hidden;'>
   <input id='chatInput' type='text'>
  </div>
 </form>
</fieldset>

<fieldset id="historyBox"><legend class='bb'>Game Log</legend>
 <div id='glog' class='bb'></div>
</fieldset>

<br><br><br><br><br>
<div id='content'></div>

<script>
var 
  slot="<?=$game["GameID"].$playerNum?>",
  playerNum=<?=$playerNum?>, pNpattern=/<A<?=$playerNum?>>'s/g,
  sessCheck=function(){ return (-1!=document.cookie.indexOf("monopoly=<?=$_COOKIE['monopoly'];?>"));};


<?php require("theme.php"); ?>


window.onload=function(){
  cardID[50]=makeDraggable($('chatBox'),600,350,0,50);
  cardID[51]=makeDraggable($('historyBox'),600,70,0,51);
  
  $('chatForm').onsubmit=function(){
    var c=$('chatInput');
    if (c.value) {
      ajax('?a=m&m='+encodeURIComponent(c.value),"$('msg_"+w+"').style.color='black';");
      $('msg').innerHTML+="<span id='msg_"+(w++)+"' style='color:grey;'>"+data.p[playerNum].n+": "+htmlEntities(c.value)+"<br></span>";
      scrollDown($('msg'));
      c.value='';
    }
    c.focus();
    return false;
  }

  ajax('?a=p&load=all',function(t){
    poll();
    kill($('pageLoading'));
    });
}
function poll(){
  ajax('?a=p',function(){ setTimeout(poll,1000); },true);
}


function main(){
  if (!!data.m){ //Messages
    $('msg').innerHTML+=data.m;
    data.m="";
    scrollDown($('msg'));
  }
  if (!!data.g){ //Game Log
    $('glog').innerHTML+=
     data.g.replace(pNpattern,"your"
      ).replace(/<A(\d)>/g,  function(a,p) {return "<span class='logPlayer'>"+data.p[p].n+"</span>";}
      ).replace(/<B(\d+)>/g, function(a,p) {return "<span class='logLocation' style='background:"+locColour(p)+"'>"+names[p]+"</span>";}
      ).replace(/<D(\d)>/g,  function(a,p) {return "<img src='t.gif' style='background:url(dice16.png) -"+(p-1)*16+"px 0px;' alt='"+p+"'>";}
      ).replace(/<H(\d)>/g,  function(a,p) {return buildings[p];}
      ).replace(/<M>/g, moneyUnit);
    
    data.g="";
    scrollDown($('glog'));
  }
  
  
  if (data.s=='0'){
    
    if (data.init) {
      var a="<form><h3>Game Options:</h3>Theme: <select id='cTheme'>";
      for (var i in themes)
        a+="<option value='"+i+"'>"+themes[i]+"</option>"; 
      a+="</select><br>Auctions: <select id='cAuction'><option value='0'>Off</option><option value='1'>On (wait for everyone)</option><option value='2'>On (time limit)</option></select>"
        +"<br><input id='cJackpot' type='checkbox'><label for='cJackpot'>Free Parking Jackpot</label>"
        +"<br><input id='cBankrupt' type='checkbox'><label for='cBankrupt'>Immediate payments</label>"
      a+="<br><h3>Player Options:</h3><select id='cToken'>";
      for (var i in tokens)
        a+="<option value='"+i+"'>"+tokens[i]+"</option>";
  
      a+="</select> <input id='cReady' type='checkbox'><label for='cReady'>I am ready</label><br> </form>";
      a+="<br><br><div style='width:600px;' id='Playerlist'></div>";
      $('content').innerHTML=a;
      
      $('cJackpot').onchange= function(){ajax('?a=o&J='+(this.checked?1:0));}
      $('cBankrupt').onchange=function(){ajax('?a=o&B='+(this.checked?1:0));}
      $('cReady').onchange=function(){ajax('?a=o&r='+(this.checked?1:0));}
      $('cToken').onchange=function(){ajax('?a=o&t='+ this.value);}
      $('cTheme').onchange=function(){ajax('?a=o&N='+ this.value);}
      $('cAuction').onchange=function(){ajax('?a=o&A='+ this.value);}
    }
    
    if ((typeof data.O.T!=="undefined")&&data.O.T!=data.iTh) {
      var d=new Date(), script = document.createElement('script');
      script.setAttribute('type', 'text/javascript');
      script.setAttribute('src', '?a=reload&T='+d.getMinutes()+d.getSeconds());
      document.getElementsByTagName('head')[0].appendChild(script);
    }
    
    $('Playerlist').innerHTML=playerStats();
    
    $('cJackpot').checked=(data.O.J=="1");
    $('cBankrupt').checked=(data.O.B=="1");
    $('cAuction').selectedIndex=data.O.A;
    $('cTheme').selectedIndex=data.O.T;
    $('cReady').checked=(data.p[playerNum].a=="1");
    $('cToken').selectedIndex=data.p[playerNum].t;
    
  } else {
    if (!board) {
      pT=[]; drawn=[]; tokenAnimID=[];
      for (var num in data.p) {
        pT[num-1]=1*data.p[num].t%(tokens.length);
        drawn[num-1]=1*data.L[num];
      }
      board=setBoard(img, tokenImg, pT, true); 
      board.setPlayers(drawn);
      data.houseStatus={};
      data.z={};
      show($('statLink'));
    }
    var a="[waiting for other players]";
    switch (data.q) {
      case "d": a="<button onclick='rolldice(this);'><img src='dice.png' alt='Roll Dice' title='Roll Dice'></button>"; break;
      case "p": a="<button onclick='ajax(&quot;?a=q&q=p&quot;);loader(this);'>Pay Rent ("+moneyUnit+data.v+")</button>"; break;
      case "b": 
         a="<button"+(sHcost[sH[data.L[playerNum]]][0]>data.M[playerNum]?" disabled title='Insufficient Funds'":"")
          +" onclick='ajax(&quot;?a=q&b=1&quot;);loader(this);'>Buy ("+moneyUnit+sHcost[sH[data.L[playerNum]]][0]+")</button> "
          +"<button onclick='ajax(&quot;?a=q&b=0&quot;);loader(this);'>Don't Buy</button>";
      break;
    } 
    $('choices').innerHTML=a;

    $('status').innerHTML=drawToken(pT[playerNum-1])
      +"Location: <span class='logLocation' style='background:"+locColour(data.L[playerNum])+";'>"+names[data.L[playerNum]]
      +"</span><br>Money: "+moneyUnit+"<span id='money'></span><br>";
    setMoney($('money'),data.M[playerNum]);
    
    for (var num in data.L)
      if (1*data.L[num]!=drawn[num-1] && !tokenAnimID[num]) animLoc(num);
    
    if (!!data.P) //IE9 error?
    for (var i=40,j=data.P.length;i--&&j+1;)
      if (cardID[i]) {
        if (data.P[j-1]==i) j--;
        else {document.body.removeChild(cardID[i]); cardID[i]=null;}
      } else if (data.P[j-1]==i) {cardID[i]=generateCard(i); j--;}
    
    if (data.H) {
      for (var num in data.H) {
        data.houseStatus[num]=data.H[num];
        if (sH[num]!=-1) board.setHouses(1*num, data.H[num]);
        if (cardID[num]) cardID[num].innerHTML=generateCard(1*num,1);
      }
      board.draw(0,1);
      data.H=false;
    }
    
    if (data.o)
      for (var i in data.o) {
        if(data.z[i]=data.o[i]){showOffers(i); break;}
      }
    if (_trading) showTradeList();
    if (data.u) {
      for (var i=40;i--;)
        if (cardID[i]&&data.houseStatus[i]==6)
          cardID[i].innerHTML=generateCard(i,1,(data.u.indexOf(i)!=-1));
      data.u=false;
    }
    if (data.R) {
      alert(data.p[(data.t[data.R])[0]].n+(data.y[data.R]==3?" accepted":" refused")+" your offer.");
      data.R=false;
    }
    
    if (data.init) loadPositions();
  } 
  
  data.init=0;
}





</script>


</body></html>
<?php
}
die();
?>