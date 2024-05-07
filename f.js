w=0;zG=0; board=_cancel=_trading=false; data={"init":1}; cardID=[]; timerID=[];
reqAnim = window.requestAnimationFrame||window.mozRequestAnimationFrame||window.webkitRequestAnimationFrame||window.msRequestAnimationFrame|| function(c){setTimeout(c,17)};
sH=[-1,0,-1,1,-1,2,3,-1,4,5,6,7,8,9,10,11,12,-1,13,14,-1,15,-1,16,17,18,19,20,21,22,-1,23,24,-1,25,26,-1,27,-1,28];
sHcost=[[60,2,10,30,90,160,250],[60,4,20,60,180,320,450],[200],[100,6,30,90,270,400,550],[100,6,30,90,270,400,550],[120,8,40,100,300,450,600],[0],[140,10,50,150,450,625,750],[150],[140,10,50,150,450,625,750],[160,12,60,180,500,700,900],[200],[180,14,70,200,550,750,950],[180,14,70,200,550,750,950],[200,16,80,220,600,800,1000],[220,18,90,250,700,875,1050],[220,18,90,250,700,875,1050],[240,20,100,300,750,925,1100],[200],[260,22,110,330,800,975,1150],[260,22,110,330,800,975,1150],[150],[280,22,120,360,850,1025,1200],[300,26,130,390,900,1100,1275],[300,26,130,390,900,1100,1275],[320,28,150,450,1000,1200,1400],[200],[350,35,175,500,1100,1300,1500],[400,50,200,600,1400,1700,2000]];
sHcolour=[0,0,-1,1,1,1,-1,2,-1,2,2,-1,3,3,3,4,4,4,-1,5,5,-1,5,6,6,6,-1,7,7];

function $(s) {
  return document.getElementById(s);
}
function hide(obj){
  obj.style.display="none";
}
function show(obj){
  obj.style.display="block";
}
function kill(obj){
  obj.parentNode.removeChild(obj);
}
function sC(string){
  return string.charAt(0).toUpperCase() + string.slice(1);
}
function recMerge(a,b){
  a||(a=[]);
  for (var key in b) {
    if (Object.prototype.toString.call(a[key])=="[object Object]") recMerge(a[key],b[key]);
    else a[key] = b[key];
  }
}

//Remove Duplicate array values
function rmDup(arr) {
  var i,out=[],obj={};
  for (i=arr.length;i--;) obj[arr[i]]=0;
  for (i in obj) out.push(i);
  return out;
}


function ajax(url,callback,brute){
  if (sessCheck()) {
    var req=(window.XMLHttpRequest)?new XMLHttpRequest():new ActiveXObject("Microsoft.XMLHTTP");
    
    req.onreadystatechange=function() {
      if (req.readyState==4 && (brute || req.status==200)) {
        if (typeof callback=='string') eval(callback);
        else {
          if(req.responseText){recMerge(data,eval("("+req.responseText+")")); main();}
          callback&&callback();
        }
        //((typeof callback=='function')?callback: new Function("t",callback))(req.responseText);
      }
    }
    var d=new Date();
    req.open("GET",url+"&T="+d.getMinutes()+d.getSeconds(),true);
    req.send();
  } else window.location=window.location;
}


function htmlEntities(str) {
    return String(str).replace(/([^\s]{31})/g, "$1 ").replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function makeDraggable(obj,l,t,a,store) {
  obj.onmousedown = function(e) {
    if (e.button != 2) obj.style.zIndex=++zG;
    var t= (e.target || e.srcElement);
    if (t.tagName!="LEGEND" && !obj.anyWhere) return;
    obj.style.cursor="move";
    disableSelection();
    dx=e.clientX-(px=nx=parseInt(obj.style.left+0));
    dy=e.clientY-(py=ny=parseInt(obj.style.top+0));
    
    timerID[obj]=0;
    document.onmousemove = function(e) {

      px=nx;py=ny;
      nx=e.clientX-dx;
      ny=e.clientY-dy;
      
      obj.style.left= Math.max(0,nx) + 'px';
      obj.style.top = Math.max(0,ny) + 'px';
    };
    document.onmouseup = function(e) {
      if (Math.abs(nx-px)>2 || Math.abs(ny-py)>2) 
        momentum(obj,nx,ny,nx-px,ny-py,20);
      else savePosition(obj,e.button!=2);
      
      obj.style.cursor="";
      document.onmousemove= document.onmouseup= null;
      enableSelection();
      obj.releaseCapture&&obj.releaseCapture();
    };
    obj.setCapture&&obj.setCapture();
  };
  obj.oncontextmenu=function(e){
    e.preventDefault();
    if (nx==px && ny==py) obj.style.zIndex--;
    return false;
  }
  obj.className+=' draggable';
  obj.style.left=l+'px';
  obj.style.top=t+'px';
  obj.style.zIndex=++zG;
  obj.anyWhere=a||0;
  obj.store=store||0;
  return obj;
}

function momentum(obj,x,y,dx,dy,t) {
  timerID[obj]=1;
  (function f(){
    x+=dx; dx*=.9;
    y+=dy; dy*=.9;
    obj.style.left= Math.max(0,x) + 'px';
    obj.style.top = Math.max(0,y) + 'px';
    (timerID[obj]&&t--)?reqAnim(f):savePosition(obj);
  })();
}

function savePosition(obj,toTop) {
  var s=obj.store, k=s+"|"+obj.style.left+"|"+obj.style.top;
  if (!s) return;
  if (!localStorage[slot]) localStorage[slot]=k;
  else {
    var all=localStorage[slot].split(",");
    for (var i=all.length;i--;){
      if (all[i].split("|")[0]==s) {
        if (toTop) all.splice(i,1);
        else all[i]=k;
        break;
      }
    }
    if (toTop) all.push(k);

    localStorage[slot]=all.join(",");
  }
}
function loadPositions(){
  if (!localStorage[slot]) return;
  var c,obj,all=localStorage[slot].split(",");
  for (var i=zG=all.length;i--;){
    c=all[i].split("|");
    if (obj=cardID[c[0]]){
      obj.style.left=c[1];
      obj.style.top =c[2];
      obj.style.zIndex=i;
    }
  }
  zG=++i;
}
function simAlert(text,h,callback){
  var o=$('alertBox'),k=$('alertInner'),j=$('Alert');
  if (!text) {hide(o);_cancel&&_cancel();return;}
  o.style.zIndex=100+zG;
  k.style.height=h+"px";
  k.style.marginTop=(-h/2)+"px";
  show(o);
  _confirm=callback;
  if ("object"===typeof callback) {
    text+="<div class='alertOps'><br>";
    for (var c in callback) text+="<button onclick='_confirm[&quot;"+c+"&quot;]();'>"+c+"</button> ";
    text+="</div>";
  }else if (callback) text+="<div class='alertOps'><br><button onclick='simAlert();_confirm()'>OK</button> <button onclick='simAlert();'>Cancel</button></div>";
  j.innerHTML=text;
  return j;
}

function fadeIn(obj,a,b,callback){
  clearTimeout(timerID[obj]);
  b=(typeof(b)==='undefined')?1:b; a=a||0; var t=20;
  (function f(){
    a+=(b-a)/t;
    obj.style.opacity=a;
    if (t--) timerID[obj]=setTimeout(f,17);
    else callback&&callback();
  })();
  show(obj);
}

function scrollDown(obj){
  clearTimeout(timerID[obj]);
  var a=obj.scrollTop, b=obj.scrollHeight,t=30;
  if (b-a>500) a=b-500;
  (function f(){
    a+=(b-a)/20;
    obj.scrollTop = a;
    if (t--) timerID[obj]=setTimeout(f,20);
  })();
}

function setMoney(obj,a){
  if (typeof oldMoney==='undefined') oldMoney=1*a;
  clearTimeout(timerID[obj]);
  var t=Math.abs(oldMoney-a);
  (function f(){
    oldMoney+=Math.max(-1,Math.min(1,a-oldMoney));
    obj.innerHTML=oldMoney;
    if (t--) timerID[obj]=setTimeout(f,20);
  })();
}

function disableSelection() {
  document.onselectstart = function() {return false;} // ie
  document.onmousedown = function() {return false;} // others
}
function enableSelection() {
  document.onselectstart = null;
  document.onmousedown = null;
}


function generateCard(num, r, noInt) {
if (sH[num]==-1) return;
var c,a="", stat=(data&&data.houseStatus)?data.houseStatus[num]:0;

if (stat==6)
  a="<div class='overCard mortgaged'><br><br><br><br><span>"+names[num]+"</span><br><br><br>Mortgaged for "+moneyUnit+sHcost[sH[num]][0]/2
   +"<br><br><a href='#' onclick='houseX("+num+",&quot;j&quot;);return false;'>Unmortgage ("+moneyUnit+~~(sHcost[sH[num]][0]*(noInt?0.5:0.55))+")</a></div>";
else if (stat==5)
  a="<div class='houseDisplay'><a href='#' onclick='houseX("+num+",&quot;e&quot;);return false;' title='Sell this "+buildings[2]+" back to the bank'><img class='Hotel' src='t.gif' alt='"+sC(buildings[2])+"'></a></div>";
else if (stat>0) {
  a="<div class='houseDisplay'>";
  while (stat--) {a+="<a href='#' onclick='houseX("+num+",&quot;e&quot;);return false;' title='Sell this "+buildings[0]+" back to the bank'><img class='House' src='t.gif' alt='"+sC(buildings[0])+"'></a>";}
  a+="</div>";
}
if (sHcost[sH[num]].length>1)
a+="<div class='deedHead' style='background-color:"
  +colours[sHcolour[sH[num]]]+";'>title deed<br><span>"
  +names[num]+"</span></div><span>Rent "+moneyUnit+sHcost[sH[num]][1]
  +"</span><br>With 1 "+sC(buildings[0])+" "+moneyUnit+sHcost[sH[num]][2]
  +"<br>With 2 "+sC(buildings[1])+" "+moneyUnit+sHcost[sH[num]][3]
  +"<br>With 3 "+sC(buildings[1])+" "+moneyUnit+sHcost[sH[num]][4]
  +"<br>With 4 "+sC(buildings[1])+" "+moneyUnit+sHcost[sH[num]][5]
  +"<br>With "+buildings[2].toUpperCase()+" "+moneyUnit+sHcost[sH[num]][6]
  +"<br><br><a href='#' onclick='houseX("+num+",&quot;h&quot;);return false;'>Mortgage</a> Value "+moneyUnit+(sHcost[sH[num]][0]/2)
  +"<br><a href='#' onclick='houseX("+num+",&quot;w&quot;);return false;'>"+sC(buildings[1])+"</a> cost "+moneyUnit+(50*(Math.ceil(num/10)))
  +"<br><a href='#' onclick='houseX("+num+",&quot;w&quot;);return false;'>"+sC(buildings[3])+"</a> "+moneyUnit+(50*(Math.ceil(num/10)))
  +" plus 4 "+buildings[1]+"<br><div class='deedTerms'>If a player owns all the lots of any color group, the rent is doubled on unimproved lots in that group.</div>";
else {
  var utilText="<br>If one &quot;Utility&quot; is owned, rent is 4 times amount shown on dice.<br><br>If both &quot;Utilities&quot; are owned, rent is 10 times amount shown on dice.<br><br><a href='#' onclick='houseX("+num+",&quot;h&quot;);return false;'>Mortgage</a> Value: "+moneyUnit+"75<br><br>";
  switch (num) {
    case 28: 
      a+="<div class='deedHead'><span>"+names[28]+"</span></div><img src='"+cardImg[0]+"'>"+utilText; break;
    case 12:
      a+="<div class='deedHead'><span>"+names[12]+"</span></div><img src='"+cardImg[1]+"'>"+utilText; break;
    case 10:
      a+="<div class='deedHead'><br><span>Get out of "+names[10]+" free</span><br><br></div>This card may be kept until needed or sold."; break;
    case 5: case 15: case 25: case 35:
      a+="<div class='deedHead'><span>"+names[num]+"</span></div><img src='"+cardImg[2]+"'><br>Rent "
      +moneyUnit+"25<br>If 2 stations are owned "
      +moneyUnit+"50<br>If 3 stations are owned "
      +moneyUnit+"100<br>If 4 stations are owned "
      +moneyUnit+"200<br><br><span><a href='#' onclick='houseX("+num+",&quot;h&quot;);return false;'>Mortgage</a> Value "
      +moneyUnit+"100<br>";
  }
}
if (!r){
  c = document.createElement("div");
  c.className='card';
  c.innerHTML=a;
  makeDraggable(c,200,500-sH[num]*15,1,num);
  document.body.appendChild(c);
} else c=a;
return c;
}

function formatTime(t){
  return ~~(t/86400)+" days "
       + ~~(t/3600)%24 +" hours " 
       + ~~(t/60)%60 +" minutes "
       + t%60 +" seconds";
}


function playerStats(){
  var tl=[],a="",z;
  for (var num in data.p) {
    z=data.p[num];
    a+= "<div class='pStat'>"+drawToken(z.t)+"Player "+num+": <b>"+z.n +"</b> "
       +(z.a&&z.a==0?"(not ready) ":"")
       +(z.i&&z.i>100?"<span style='color:grey;'>(offline)</span> ":"")+"<br>";
    
    if (data.L)
      a+="Location: <span class='logLocation' style='background:"+locColour(data.L[num])+";'>"+names[data.L[num]]
        +"</span><br>Money: "+moneyUnit+data.M[num]+"<br>";
    
    if (z.P) {
      a+="<div style='float:right;width:229px;'>";
      for (var i=z.P.length;i--;) a+=miniCard(1*z.P[i]);
      a+="</div>";
    }
    
    a+="</div>";
    
    tl.push(z.t);
  }
  if (tl.length!=rmDup(tl).length) a+="<br>Token Conflict!";

  return a;
}

function miniCard(n,status){
  status=(status||data.houseStatus)[n];
  var c=locColour(n);
  return (n!=10)?'<div class="miniCard"><div class="miniCardTitle"><span style="background:'+c+';">'+names[n]
   +(status==6?' (mortgaged)</span></div><div style="position:absolute;color:red;">X</div>' : '</span></div>')
   +'<div style="background:'+c+';"></div></div>'
   :'<div class="miniCard" style="height:10px;width:15px;"><div class="miniCardTitle"><span>Get out of '+names[n]+' Free</span></div></div>';
}


function showStats(){
  var j=simAlert("<div style='padding:200px'><img src='loading.gif' title='Loading...' alt='Loading...'></div>",480);
  ajax("?a=p&load=stats", function(){
    var unowned=""; 
    if (data.un) {
      unowned="<br>Unowned properties:<br>";
      for (var i=data.un.length;i--;) unowned+=miniCard(1*data.un[i]);
    }
    j.innerHTML=
       "<h3>Game Stats</h3>Game ID: "+data.id+" <br>Duration: "+formatTime(data.st)
      +"<br><br>"+(data.tu!=0?"It's "+data.p[[data.tu]].n+"'s turn.<br>":"")
      +"<h3>Player Stats</h3>"+playerStats()+unowned+"<br><br><br>";
  });
}



function drawToken(t){
  return "<div style='float:left;margin:-5px 5px 0px -5px;width:64px;height:64px;background:url("+tokenImgSrc+") -"+t*64+"px 0px;'></div>";
}


function houseX(num, act){
  var m= 
    act=='e'? "Sell this "+buildings[(data.houseStatus[num]==5?2:0)]+" back to the bank?":
    act=='w'? "Build a "+buildings[(data.houseStatus[num]==4?2:0)]+" on "+names[num]+"?":
    "Do you want to "+(act=='j'?"un":"")+"mortgage "+names[num]+"?";
  
  if (confirm(m)){
    cardLoader(num);
    ajax('?a=x&j='+act+'&h='+num,function(){
      if (data.err){
        cardID[num].innerHTML=generateCard(num,1);
        alert(data.err.replace(/<H(\d)>/g,  function(a,p) {return buildings[p];}));
        data.err="";
      }
    });
  }
}

function animLoc(num){
  drawn[num-1]=(drawn[num-1]+1)%40;
  
  board.setPlayers(drawn);
  if (data.L[num]!=drawn[num-1]) tokenAnimID[num]=setTimeout(function(){animLoc(num)},200);
  else tokenAnimID[num]=false;
}

function locColour(num, def){
  return (sH[num]==-1||sHcolour[sH[num]]==-1)?def||"#fff":colours[sHcolour[sH[num]]];
}

function cardLoader(num){
  cardID[num].innerHTML="<div class='overCard' style='line-height:200px;z-index:2;cursor:wait;'><img src='loading.gif'></div>"+cardID[num].innerHTML;
}
function loader(o){
  o.parentNode.innerHTML="<img src='loading.gif' title='Loading...' alt='Loading...'>";
}

function rolldice(o){
  ajax('?a=q&q=d');
  o.innerHTML="<img src='rolling.gif' alt='Rolling...' title='Rolling...'>";
}


//Transactions
function tradeError(){
  if (data.err) alert(data.err),data.err=false;
}
function showOffers(first){
  var off=data.o[first],anyM=[0],
  yP=condenseP(off[1],off[5],anyM),
  mP=condenseP(off[3],off[5],anyM),
  m="<h3>Offer</h3><b>"+data.p[off[0]].n+"</b> has made you an offer of:<br><br>";
  
  (off[2]>0)&&yP.push(moneyUnit+off[2]);
  (off[4]>0)&&mP.push(moneyUnit+off[4]);
  
  m+=englishArray(yP)+"<br><br>in exchange for<br><br>"+englishArray(mP)+(anyM[0]?"<br><br>Note: any mortgaged properties you receive will require a 10% interest payment to the bank.":"");
  
  _cancel=function(){ajax("?a=t&t=d&id="+first,tradeError);_cancel=false;}
  simAlert(m,480,{
    "Accept":function(){
      if (data.y[first]!=5) ajax("?a=t&t=a&id="+first,tradeError);
      else alert(data.p[off[0]].n+" has withdrawn the offer.");
      _cancel=false;simAlert();
    },
    "Refuse":function(){ajax("?a=t&t=r&id="+first,tradeError);_cancel=false;simAlert();},
    "Defer" :function(){_cancel();simAlert();}
  });
  delete data.o[first];
}
function showTrades(){
  tradeError();
  _trading=true;
  _cancel=function(){_trading=_cancel=false;};
  simAlert("<div style='padding:100px'><img src='loading.gif' title='Loading...' alt='Loading...'></div>",480);
  ajax("?a=p&load=stats", showTradeList);
}
function showTradeList(){  
    var s="",a="<h3>Trades</h3>Make a deal with: <select id='toTradeWith'>";
    for (var num in data.p) {
      if (num!=playerNum) a+="<option value='"+num+"'>"+data.p[num].n+"</option>";
    }
    
    if (data.z)
    for (var num in data.z){
      var u=data.z[num];
      s+="<tr><td>"+data.p[u[0]].n+"</td><td>"
        +miniOffer(u[3],u[4],u[5])+"</td><td>"
        +miniOffer(u[1],u[2],u[5])+"</td><td>"+miniOfferStatus(num)+"</td></tr>";
    }
    if (data.t)
    for (var num in data.t) {
      var u=data.t[num];
      s+="<tr><td>"+data.p[u[0]].n+"</td><td>"
        +miniOffer(u[1],u[2],u[5])+"</td><td>"
        +miniOffer(u[3],u[4],u[5])+"</td><td>"+miniTradeStatus(num)+"</td></tr>";
    }
    
    $('Alert').innerHTML=a+"</select><button onclick='makeTrade()'>Go</button><br><br>"
      +(s?"<table><tr><th>Player</th><th>You give</th><th>You get</th><th>Status</th></tr>"+s+"</table>":"[no offers]");
}
function miniOffer(p,m,s){
  for (var a="",i=p.length;i--;) a+=miniCard(1*p[i],s);
  return m>0?a+(a&&" &amp; ")+moneyUnit+m:a;
}
function miniOfferStatus(id){
  switch (1*data.y[id]){
    case 1:case 2:return "<a href='#' onclick='changeOffer(this,&quot;a&quot;,"+id+");return false;'>[Accept]</a> <a href='#' onclick='changeOffer(this,&quot;r&quot;,"+id+");return false;'>[Refuse]</a>";
    case 3:return "Completed";
    case 4:return "Declined";
    case 5:return "Expired";
  }
  return "Error";
}
function miniTradeStatus(id){
  var withdraw=" <a href='#' onclick='changeOffer(this,&quot;w&quot;,"+id+");return false;'>[withdraw]</a>";
  switch (1*data.y[id]){
    case 1:return "Pending"+withdraw;
    case 2:return "Deferred"+withdraw;
    case 3:return "Accepted";
    case 4:return "Refused";
    case 5:return "Withdrawn";
  }
  return "Error";
}
function changeOffer(obj,act,id){
  obj.style.cursor='wait';
  ajax("?a=t&t="+act+"&id="+id,showTrades);
}
function makeTrade(){
  _trading=false;
  tradeOp=$('toTradeWith').value;

  simAlert(
  "<h3>Trade:</h3>You can select multiple items by holding ctrl / command.<br>You cannot trade properties which have buildings on them.<br><br>"
  +"<div style='float:left;text-align:center;'>"+propertySelector(playerNum)+"</div>"
  +"<div style='float:right;text-align:center;'>"+propertySelector(tradeOp)+"</div>"
  +"<div style='padding:100px;text-align:center;font-size:14pt'><b>for</b></div> <br style='clear:both;'><br><br><button style='margin:5px 30px;' onclick='confirmTrade()'>Confirm and submit offer</button><button onclick='showTrades()'>Cancel</button>"
  ,480);
}


function propertySelector(num){
  var p=data.p[num].P, a="<select multiple id='pSel"+num+"'>";
  for (var i=p.length;i--;) 
    if (!data.houseStatus[p[i]] || data.houseStatus[p[i]]==6) 
      a+="<option style='color:"+locColour(p[i],"#000")+";' value='"+p[i]+"'>"+names[p[i]]+"</option>";
  a+="</select><br><b>and</b> "+moneyUnit+"<input id='pMon"+num+"' type='text' size='5' onblur='checkNumber(this,"+data.M[num]+")'>";
  return a;
}

function checkNumber(obj,max){
  obj.value= Math.min(max,Math.max(0,parseInt(obj.value,10)||0));
}

function confirmTrade(){
  var mP=condenseSelect($('pSel'+playerNum).options),
      yP=condenseSelect($('pSel'+tradeOp).options),
      mM=$('pMon'+playerNum).value,
      yM=$('pMon'+tradeOp).value;
  if (mM&&mM>0) mP[1].push(moneyUnit+mM);
  if (yM&&yM>0) yP[1].push(moneyUnit+yM);
  

  if (mP[1].length&&yP[1].length)
    var j=simAlert("Make an offer to "+data.p[tradeOp].n+" of <br><br>"+englishArray(mP[1])+"<br><br> for <br><br>"+englishArray(yP[1])+"?",300,function(){
      simAlert("<div style='padding:100px'><img src='loading.gif' title='Loading...' alt='Loading...'></div>");
      ajax("?a=t&t=n&p="+tradeOp+"&mP="+mP[0].join()+"&mM="+mM+"&yP="+yP[0].join()+"&yM="+yM, function(){
        data.t=data.y=data.z=false;
        ajax('?a=t&t=c&id='+data.dt, showTrades);
      });
    });
  else alert("Please select items and/or money to trade.");
}
function condenseSelect(ops){
  var r=[],n=[],j;
  for(var i=ops.length;i--;)
    if (ops[i].selected) {
      r.push(j=ops[i].value);
      n.push("<span class='logLocation' style='background:"+locColour(j,"#eee")+"'>"+names[j]+(data.houseStatus[j]==6?" (mortgaged)":"")+"</span>");
    }
  return [r,n];
}
function condenseP(p,s,anyM){
  var n=[];
  for (var i=p.length;i--;) 
    n.push("<span class='logLocation' style='background:"+locColour(p[i],"#eee")+"'>"+names[p[i]]+(s[p[i]]==6?(anyM[0]=1," (mortgaged)"):"")+"</span>");
  return n;
}
function englishArray(a){
  if (a.length>1) {
    var last=a.pop();
    return a.join(', ')+" and "+last;
  } return a&&a[0];
}

