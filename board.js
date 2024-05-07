function setBoard(img,tokenImg,pT,mouse){
var c1=document.createElement("canvas"), ctx=c1.getContext("2d"),
    c2=document.createElement("canvas"), ctx2=c2.getContext("2d");
c1.width=w=850;c1.height=h=600; 

document.body.appendChild(c1);
if (mouse) {
  $('content').innerHTML="<div id='boardOptions'><button id='opCon'>&gt;&gt;</button><div id='opEx' style='display:none;'><span>Game Board Projection</span><br>Click and drag to rotate.<br>Hold Ctrl to translate and Shift to scale.<br><br><input type='checkbox' onclick='wireFrame=this.checked;' id='opW'><label for='opW'>Wireframe while moving</label><br><br><button onclick='board.top();'>Top Down</button><button onclick='board.iso();'>Isometric</button><br><br><button id='opHi'>Hide Options &lt;&lt;</button></div></div>";
  $('boardOptions').onclick=function(){this.style.zIndex=++zG;}
  $('opCon').onclick=function(){fadeIn($("opEx"));hide($("opCon"));}
  $('opHi').onclick=function(){fadeIn($("opEx"),1,0, function(){hide($("opEx"));show($("opCon"));}  );}
  
  $('opW').checked=wireFrame=true;
}

var
  X=[-93,93,93,-93,-93,-72,-72,-51,-51,-30,-30,-10,-10,10,10,30,30,51,51,72,72,93,93,130,130,93,93,130,130,93,93,130,130,93,93,130,130,93,93,130,130,93,93,72,72,51,51,30,30,10,10,-10,-10,-30,-30,-51,-51,-72,-72,-93,-93,-130,-130,-93,-93,-130,-130,-93,-93,-130,-130,-93,-93,-130,-130,-93,-93,-130,-130,-130,-130,130,130,-130,-130,93,93,72,72,51,51,30,30,-10,-10,-30,-30,-51,-51,-93,-93,-93,-100,-100,-93,-93,-100,-100,-93,-93,-100,-100,-93,-93,-100,-100,-93,-93,-93,-72,-72,-51,-51,-10,-10,93,93,72,72,51,51,10,10,93,100,100,93,93,100,100,93,93,100,100,93,93,100,100,93,110,80,60,40,20,0,-20,-40,-60,-80,-110,-110,-110,-110,-110,-110,-110,-110,-110,-110,-110,-80,-60,-40,-20,0,20,40,60,80,110,110,110,110,110,110,110,110,110,110],
  Y=[-93,-93,93,93,-130,-130,-93,-93,-130,-130,-93,-93,-130,-130,-93,-93,-130,-130,-93,-93,-130,-130,-93,-93,-72,-72,-51,-51,-30,-30,-10,-10,10,10,30,30,51,51,72,72,93,93,130,130,93,93,130,130,93,93,130,130,93,93,130,130,93,93,130,130,93,93,72,72,51,51,30,30,10,10,-10,-10,-30,-30,-51,-51,-72,-72,-93,-130,130,130,-130,-130,-93,93,100,100,93,93,100,100,93,93,100,100,93,93,100,100,93,93,93,72,72,51,51,10,10,-10,-10,-30,-30,-51,-51,-93,-93,-93,-100,-100,-93,-93,-100,-100,-93,-93,-100,-100,-93,-93,-100,-100,-93,-10,-10,-30,-30,-51,-51,-93,-93,93,93,72,72,51,51,30,30,110,110,110,110,110,110,110,110,110,110,110,80,60,40,20,0,-20,-40,-60,-80,-110,-110,-110,-110,-110,-110,-110,-110,-110,-110,-110,-80,-60,-40,-20,0,20,40,60,80],
  s=2,ty=h/2,tx=w/2, theta=phi=imgW=intH=vScale=imgData=0, pL=[], houses=[];

var model=[[[-2,-2,4],[-2,0,6],[-2,2,4],[-2,2,0],[-2,-2,0],[-2,-2,4]],[[2,-2,4],[2,0,6],[2,2,4],[2,2,0],[2,-2,0],[2,-2,4]],[[2,-2,4],[2,-2,0],[-2,-2,0],[-2,-2,4]],[[2,-2,4],[2,0,6],[-2,0,6],[-2,-2,4]],[[2,0,6],[-2,0,6],[-2,2,4],[2,2,4]],[[2,2,4],[2,2,0],[-2,2,0],[-2,2,4]],[[-2,-2,0],[2,-2,0],[2,2,0],[-2,2,0]]],
  numVert=[6,6,4,4,4,4,4];
  
//We know the img has loaded
imgW=img.width;
c2.width=c2.height=intH=Math.ceil(1.414*imgW);
vScale=imgW/300;

if (window.slot&&localStorage[slot+"_b"]) loadBoardPos();


if (mouse){
  var mx=my=oldx=oldy=mousedown=0;
  function getMouse(e){
    oldx=mx;oldy=my;
    if (e.layerX || e.layerX == 0) { // Firefox
      mx = e.layerX;
      my = e.layerY;
    } else if (e.offsetX || e.offsetX == 0) { // Opera
      mx = e.offsetX;
      my = e.offsetY;
    }
  }
  
  c1.onmousedown=function() {mousedown=true; c1.style.zIndex=++zG; return false;};  
  c1.onmouseup=c1.onmouseout=function () {
    mousedown=false; 
    c1.style.zIndex=""; 
    c1.style.cursor="default"; 
    setTimeout(draw,10);
    saveBoardPos();
  }
  c1.onmousemove=function (e) {
    getMouse(e);
    if (mousedown) {
      if (e.ctrlKey) {
        c1.style.cursor="move";
        tx+=(mx-oldx);
        ty+=(my-oldy);
      } else if (e.shiftKey) {
        c1.style.cursor="se-resize";
        s=Math.max(0.05,s+(s*(mx-oldx+my-oldy))/250);
      } else {
        c1.style.cursor="crosshair";
        theta-=(mx-oldx)/(50*s);
        phi=Math.min(0,Math.max(-1.57,phi+(my-oldy)/(50*s)));
      }
      draw(wireFrame);
    }
  };
}

function draw(vector,cache){
  var i,j,c, cp=Math.cos(phi),tp=Math.tan(phi),st=1.15*Math.sin(theta),ct=1.15*Math.cos(theta), col=colours;
  
  ctx.clearRect(0,0,w,h);
  if (!cache || !imgData) {
    if (vector) {
      for (i=8;i--;) {
        ctx.fillStyle=col[i];
        ctx.beginPath();
        for (j=8;j--;){
          c=transform(85+i*8+j,st,ct,cp,tp);
          ctx.lineTo(c[0],c[1]);
        }
        ctx.fill(); 
      }
      ctx.beginPath();
      for (i=85;i--;) {
        c=transform(i,st,ct,cp,tp);
        ctx.lineTo(c[0],c[1]);
      }
      ctx.closePath();
      ctx.stroke();

    } else {
      ctx2.save();
      ctx2.clearRect(0,0,intH,intH);
      ctx2.translate(intH/2,intH/2);
      ctx2.rotate(theta);
      ctx2.translate(-imgW/2,-imgW/2);
      ctx2.drawImage(img, 0,0);
      ctx2.restore();
    
      var dist,dy;
      for (var i=intH;i--;) {
        dist=s*h/((i-intH/2)*cp*tp+h*vScale);
        dy=ty+(i-intH/2)*cp*dist;
         
        if (dy>-s && dy<600)
          ctx.drawImage(c2, 0,i,intH,1, tx-0.5*intH*dist, dy, intH*dist,s );
      }
    }
    imgData=ctx.getImageData(0,0,w,h);
  } else ctx.putImageData(imgData,0,0);
  
  
  ctx.fillStyle="rgba(0,180,0,0.5)";
  var stack=[];
  
  for (var i=pL.length;i--;)
    stack.push([drawToken,X[149+pL[i]],Y[149+pL[i]],i]);

  for (var i=houses.length;i--;)
    stack.push([drawHouse,houses[i][0],houses[i][1],houses[i][2]]);
  
  stack.sort(function(a, b){
    return (st*b[1]+ct*b[2])-(st*a[1]+ct*a[2]);
  });
  
  while (c=stack.pop()) (c[0])(c[1],c[2],c[3],st,ct,cp,tp);
}
function transform(i,st,ct,cp,tp){
var x= ct*X[i] - st*Y[i],
    y=(st*X[i] + ct*Y[i])*cp,
    dist=s*h/(y*tp+h);
  return [tx + x*dist, ty + y*dist];
}
function transform2(x,y,z,st,ct,cp,tp){
var nx= ct*x - st*y,
    ny=(st*x + ct*y)*cp,
    dist=s*h/(ny*tp+h);
  return [tx + nx*dist, ty + ny*dist + dist*cp*tp*z, dist];
}
function drawHouse(x,y,hotel,st,ct,cp,tp){
  if (hotel) ctx.fillStyle="rgba(220,0,0,0.6)";
  var c,k=ctx,m=model,si=numVert,scale=1+hotel;
  for (var i=7;i--;){
    k.beginPath();
    for (var j=si[i];j--;) { //Would this be faster if we forced it to work all in triangles?
      c=transform2(x+scale*m[i][j][0],y+scale*m[i][j][1],scale*m[i][j][2],st,ct,cp,tp);
      k.lineTo(c[0],c[1]);
    }
    k.closePath();
    k.stroke();
    k.fill();
  }
  if (hotel) ctx.fillStyle="rgba(0,180,0,0.5)";
}
function drawToken(x,y,i,st,ct,cp,tp){
  var c=transform2(x,y,7,st,ct,cp,tp),
      d=8*c[2];
  ctx.drawImage(tokenImg,pT[i]*64,0,64,64, c[0]-d,c[1]-d,2*d,2*d);
}
function getHousePosition(num, offset, h) {
  var 
  n=40-num,
  dir=~~(n/10),
  x= dir%2 + (dir==3?-2:0),
  y=(dir+1)%2 + (dir==2?-2:0),
  o= (5 - n%10)*21 + offset;

  return [ x*o+y*98, y*o-x*98, h||0, num ];
}
function saveBoardPos(){
  localStorage[slot+"_b"]=[s,ty,tx,theta,phi,wireFrame||""].join(",");
}
function loadBoardPos(){
  var d=localStorage[slot+"_b"].split(",");
  s=parseFloat(d[0])||2;
  ty=parseFloat(d[1])||h/2;
  tx=parseFloat(d[2])||w/2;
  theta=parseFloat(d[3])||0;
  phi=parseFloat(d[4])||0;
  $('opW').checked=wireFrame=!!d[5];
}

draw(!mouse);

function rot(){reqAnim(rot); theta+=0.01;phi+=0.005;draw(1);};

return {
  "draw":draw,
  "animate":rot,
  "top":function(){s=2;ty=h/2;tx=w/2;theta=phi=0; draw(0); saveBoardPos();},
  "iso":function(p){theta=0.7854;phi=-1.1;s=p||2;ty=h/2.5;tx=w/2;draw(!mouse); mouse&&saveBoardPos();},
  "setPlayers":function(p){pL=p;draw(0,1);},
  
  "setHouses":function(num, status) {
    for (var i=houses.length;i--;)
      if (houses[i][3]==num) houses.splice(i,1);
    switch (status) {
      case 5: houses.push(getHousePosition(num,0,1)); break;
      case 4: houses.push(getHousePosition(num,7.5,0));
      case 3: houses.push(getHousePosition(num,-7.5,0));
      case 2: houses.push(getHousePosition(num,2.5,0));
      case 1: houses.push(getHousePosition(num,-2.5,0));
    }
   }
  };
}