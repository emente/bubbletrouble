<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>A Bubble In Trouble</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
            <link rel="stylesheet" type="text/css" href="bt.css" />

            <script type="text/javascript" src="jquery.min.js"></script>

            <script type="text/javascript">

                function getNode(name) {
                    return $('#'+name);
                }
                function rand(m) {
                    return Math.round(Math.random()*m);
                }
                function decorateSounds() {
                    for (var i in sounds) {
                        sounds[i].stop = function() {
                            try{
                                this.pause();
                                this.currentTime = 0;
                                this.src = this.src;
                            } catch(e) {}
                        }
                        sounds[i].trigger = function() {
                            try{
                                this.pause();
                                this.currentTime = 0;
                                this.play();
                            } catch(e) {}
                        }
                    }
                }

                function setNewWaterColors() {
                    var i,c = HSV2RGB(rand(180),0.5,0.2);
                    for (i=0;i<7;i++) {
                        var j=(i*0.4)+1;
                        getNode('waterbar'+(6-i)).css('background-color','rgb('+Math.round(c.r*j)+','+Math.round(c.g*j)+','+Math.round(c.b*j)+')');
                    }

                    c = HSV2RGB(rand(180),0.4,0.2);
                    for (i=0;i<6;i++) {
                        var j=4+(0.7*(7-i));
                        getNode('skybar'+i).css('background-color','rgb('+Math.round(c.r*j)+','+Math.round(c.g*j)+','+Math.round(c.b*j)+')');
                    }
                }

                function checkForKeyPress(e,v) {
                    var evt=(e)?e:window.event;
                    switch(evt.keyCode) {
                        case 39:
                            dude.kl=v;
                            break;
                        case 40:
                            dude.ku=v;
                            break;
                        case 37:
                            dude.kr=v;
                            break;
                    }
                }

                function HSV2RGB(h,s,v){
                    //***h (hue) should be a value from 0 to 360
                    //***s (saturation) and v (value) should be a value between 0 and 1
                    //***The .r, .g, and .b properties of the returned object are all in the range 0 to 1
                    var r,g,b,i,f,p,q,t;
                    while (h<0) h+=360;
                    h%=360;
                    s=s>1?1:s<0?0:s;
                    v=v>1?1:v<0?0:v;

                    if (s==0) r=g=b=v;
                    else {
                        h/=60;
                        f=h-(i=Math.floor(h));
                        p=v*(1-s);
                        q=v*(1-s*f);
                        t=v*(1-s*(1-f));
                        switch (i) {
                            case 0:
                                r=v;
                                g=t;
                                b=p;
                                break;
                            case 1:
                                r=q;
                                g=v;
                                b=p;
                                break;
                            case 2:
                                r=p;
                                g=v;
                                b=t;
                                break;
                            case 3:
                                r=p;
                                g=q;
                                b=v;
                                break;
                            case 4:
                                r=t;
                                g=p;
                                b=v;
                                break;
                            case 5:
                                r=v;
                                g=p;
                                b=q;
                                break;
                        }
                    }
                    return {
                        r:255*r,
                        g:255*g,
                        b:255*b
                    };
                }

                /////////////////////////////////////////////////////

                var dude = {
                    x:0,
                    y:0, 
                    kl:0, //keyleft
                    kr:0, //keyright
                    kd:0,  //keydown
                    bubbles:0,
                    l:3,
                    score:0,
                    high:0,
                    el:null
                };
                var croc = [
                    {
                        x:0,
                        y:0,
                        s:0,
                        el:null,
                        dx:0,
                        dy:0,
                        t:0,
                        i:null,
                        ldx:0
                    },
                    {
                        x:0,
                        y:0,
                        s:0,
                        el:null,
                        dx:0,
                        dy:0,
                        t:0,
                        i:null,
                        ldx:0
                    }
                ];
                var soap = {
                    y:0,
                    x:0,
                    delay:0,
                    down:true,
                    el:null
                };
                var duck = {
                    x:0,
                    right:true,
                    el:null
                };
                var spider = {
                    h:0,
                    down:true,
                    delay:10,
                    el:null,
                    el2:null
                };
                var bubbles = [
                    {x:0,y:0,vis:0},
                    {x:0,y:0,vis:0},
                    {x:0,y:0,vis:0},
                    {x:0,y:0,vis:0},
                    {x:0,y:0,vis:0},
                    {x:0,y:0,vis:0},
                    {x:0,y:0,vis:0},
                    {x:0,y:0,vis:0},
                    {x:0,y:0,vis:0},
                    {x:0,y:0,vis:0},
                    {x:0,y:0,vis:0}
                ];
                var plug = {
                    el:null
                };
                var stream = {
                    el:null
                };
        
                var topbar = {
                    el:[],
                    str: '                     ',
                    xdim:16,
                    ydim:11,
                    charstable: '####################'+
                        '§###################'+
                        '()*+,-./0123456789:;'+
                        '<=>?@ABCDEFGHIJKLMNO'+
                        'PQRSTUVWXYZ[/]^_"abc'+
                        'defghijklmnopqrstuvw'+
                        'xyz{|}~#############'+
                        '##################ß#'
                };
                
                var level;
                var tick;
                var sounds={};

                function setChars() {
                    for (var i=0;i<21;i++) {
                        var pos = topbar.charstable.indexOf(topbar.str.charAt(i));
                        var y = Math.floor(pos / 20);
                        var x = pos % 20;
                        topbar.el[i].css('background-position-x',(320-(x*topbar.xdim))+'px');
                        topbar.el[i].css('background-position-y',(87-(y*topbar.ydim))+'px');
                    }
                }


                function dudeKi() {
                    if (dude.ku) {
                        dude.y+=2;
                    } else {
                        dude.y-=1.7;
                    }
                    if (dude.kl) dude.x+=2;
                    if (dude.kr) dude.x-=2;

                    if (dude.x<0) dude.x=0;
                    if (dude.x>254) dude.x=254;
                    if (dude.y>95) dude.y=95;
                    if (dude.y<0 && dude.bubbles<9) dude.y=0;
                    dude.el.css({top:88+dude.y,left:34+dude.x});
                }

                function crocKi() {
                    for (var i=0;i<2;i++) {
                        croc[i].t--;
                        if (croc[i].t<=0) {
                            croc[i].t=20+rand(50);

                            croc[i].dx = Math.random()>0.5 ? 1 : -1;
                            croc[i].dy = Math.random()>0.5 ? Math.random() : -Math.random();
                        }

                        croc[i].x += croc[i].dx;
                        croc[i].y += croc[i].dy;

                        croc[i].el.css({top:88+croc[i].y,left:34+croc[i].x});

                        if (croc[i].x<10) croc[i].dx=1;
                        if (croc[i].x>240) croc[i].dx=-1;
                        if (croc[i].y<10) croc[i].dy=1;
                        if (croc[i].y>85) croc[i].dy=-1;

                        if (croc[i].ldx != croc[i].dx) {
                            croc[i].el.attr('src',croc[ croc[i].dx>0 ? 1: 0 ].i.src);
                            croc[i].ldx = croc[i].dx;
                        }
                    }
                }

                function getNewBubbleX() { 	//8-288, mod 28
                    var radius=100+(30*level);
                    if (radius>200) radius=200;
                    var x = dude.x+(Math.random()*radius)-(Math.random()*radius);
                    if (x<0) x=0;
                    if (x>250) x=250;
                    return x;
                }
                
                function setNewSoapX() {
                    soap.x = rand(255);
                    soap.el.css('left',34+soap.x);
                }

                function isDudeCollision(x,y,w,h) { //dude=14x10
                    var left1, left2, right1, right2, top1, top2, bottom1, bottom2;

                    left1 = dude.x;
                    left2 = x;
                    right1 = dude.x + 14;
                    right2 = x+w;
                    top1 = dude.y;
                    top2 = y;
                    bottom1 = dude.y + 10;
                    bottom2 = y + h;

                    if (bottom1 < top2) return false;
                    if (top1 > bottom2) return false;

                    if (right1 < left2) return false;
                    if (left1 > right2) return false;

                    return true;
                }

                function duckKi() {
                    if (duck.x>255) {
                        duck.right = false;
                        duck.el.addClass('mirror');
                    }
                    if (duck.x<0) {
                        duck.right=true;
                        duck.el.removeClass('mirror');
                    }
                    if (duck.right) {
                        duck.x++;
                    } else {
                        duck.x--;
                    }
                    duck.el.css('margin-left',duck.x);
                }

                function bubblesKi() {
                    for (i=0;i<11;i++) {
                        if (i<10) {
                            bubbles[i].y-=1;
                            if (bubbles[i].y<=0) {
                                bubbles[i].y=105+rand(30);
                            }
                        } else {
                            bubbles[i].y-=1.7;
                            if (bubbles[i].y<=0) {
                                bubbles[i].x=getNewBubbleX();
                                bubbles[i].y=105+rand(20*level);
                            }
                        }
                        bubbles[i].el.offset({left:bubbles[i].x+34});
                        if ((bubbles[i].y<0) || (bubbles[i].y>105)) {
                            bubbles[i].el.offset({top:-100});
                        } else {
                            bubbles[i].el.offset({top:bubbles[i].y+88});
                        }
                    }
                }

                function soapKi() {
                    soap.delay--;
                    if (soap.delay<0) {
                        if (soap.delay===-1) {
                            sounds.soap.play();
                        }
                        if (soap.y>175) {
                            soap.down=false;
                        }
                        if (soap.down) {
                            soap.y+=3;
                        } else {
                            soap.y-=3;
                            if (soap.y<-30) {
                                setNewSoapX();
                                soap.down=true;
                                soap.delay=100;
                            }
                        }
                        soap.el.css('top',soap.y);
                    }                    
                }

                // dude=14x10, croc=16x14
                function crocCollision() {
                    for (var i=0;i<2;i++) {
                        if (isDudeCollision(croc[i].x, croc[i].y+3, 16, 14-6)) {
                            killAnim();
                        }
                    }
                }

                // duck=16x16
                function duckCollision() {
                    if ((dude.y<2) &&
                        (duck.x+12>=dude.x) &&
                        (duck.x-2<=duck.x)) {
                        killAnim();
                    }
                }

                
                function updateScore() {
                    var i;
                    topbar.str = '';
                    
                    if (dude.high<dude.score) {
                        dude.high = dude.score;
                    }
                    
                    for (i=0;i<3;i++) {
                        if (dude.l > i) {
                            topbar.str+='@';
                        }else {
                            topbar.str+=' ';
                        }
                    }
                    topbar.str+=' ';
                    
                    var l = 6-String(dude.score).length;
                    for (i=0;i<l;i++) {
                        topbar.str+='0';
                    }
                    topbar.str+=dude.score;
                    
                    topbar.str+=' ';
                    topbar.str+=dude.bubbles;
                    
                    topbar.str+=' HI';
                    
                    var l = 5-String(dude.high).length;
                    for (i=0;i<l;i++) {
                        topbar.str+='0';
                    }
                    topbar.str+=dude.high;
                    
                    setChars();
                } 

                function soapCollision() {
                    if (isDudeCollision(soap.x, soap.y-88, 16, 20)) {
                        resetSoap();
                        gotBubble();
                    }
                }
                
                function spiderKi() {
                    spider.delay--;
                    
                    if (spider.delay<0) {
                        if (spider.h>110) {
                            spider.down=false;
                        }
                        if (spider.down) {
                            spider.h+=3;
                        } else {
                            spider.h-=3;
                            if (spider.h<0) {
                                spider.down=true;
                                spider.delay=150;
                            }
                        }
                        spider.el.css('height',50+spider.h);
                    }                       
                }
                       
                function spiderCollision() {
                    if (isDudeCollision(172, spider.h+7, 3, spider.h)) {
                        killAnim();
                    }
                }
                
                function gotBubble() {
                    dude.bubbles+=1;
                    dude.score+=10;
                    sounds.good.trigger();
                    updateScore();
                }

                // dude=14x10 1=4x2 0=8x5
                function bubblesCollision() {
                    for (i=0;i<10;i++) {
                        if ((bubbles[i].x+3>=dude.x) &&
                            (bubbles[i].x-12<=dude.x) &&
                            (bubbles[i].y+1>=dude.y) &&
                            (bubbles[i].y-8<=dude.y)) {
                            bubbles[i].y=105+rand(30);
                            dude.bubbles-=(dude.bubbles>0?1:0);
                            updateScore();
                            sounds.bad.trigger();
                        }
                    }

                    if ((bubbles[i].x+7>=dude.x) &&
                        (bubbles[i].x-12<=dude.x) &&
                        (bubbles[i].y+1>=dude.y) &&
                        (bubbles[i].y-10<=dude.y)) {
                        gotBubble();
                        bubbles[i].y=105+rand(20*level);
                        bubbles[i].x=getNewBubbleX();
                    }
                }

                var killColorStore={};
                function killAnim(state) {
                    if (tick) { 
                        dude.l--;
                        updateScore();
                        
                        sounds.boom.play();
                        sounds.soap.stop();
                        sounds.atmo.stop();
                        resetSoap();
                        window.clearInterval(tick); 
                        tick=null; 
                        for (var i=0;i<7;i++) {
                            killColorStore[i] = getNode('waterbar'+i).css('background-color');
                        }
                    }
                    
                    if (state>=40) {
                        state=0;
                        for (var i=0;i<7;i++) {
                            getNode('waterbar'+i).css('background-color',killColorStore[i]);
                        }
                    
                        if (dude.l<=0) {
                            gameOver();
                            return;
                        }
                    
                        levelInit();
                        return;
                    }

                    for (var i=0;i<7;i++) {
                        getNode('waterbar'+(6-i)).css('background-color','rgb('+Math.round(Math.random()*255)+','+Math.round(Math.random()*255)+','+Math.round(Math.random()*255)+')');
                    }

                    window.setTimeout(function() {
                        killAnim(state?state+1:1);
                    },20);
                }
                
                function hideEverything() {
                    for (var i in bubbles) {
                        bubbles[i].el.hide();
                    }
                    dude.el.hide();
                    duck.el.hide();
                    soap.el.hide();
                    croc[0].el.hide();
                    croc[1].el.hide();
                    spider.el.hide();
                    spider.el2.hide();
                }

                function showEverything() {
                    for (var i in bubbles) {
                        bubbles[i].el.show();
                    }
                    dude.el.show();
                    duck.el.show();
                    soap.el.show();
                    croc[0].el.show();
                    croc[1].el.show();
                    spider.el.show();
                    spider.el2.show();
                }
                
                function gameOver() {
                    hideEverything();
                    waterOut(function() {
                        dude.l=3;
                        startScreen();
                    });
                }
            
                function startScreen(idx) {
                    dude.l = 3;
                    dude.score = 0;
                    dude.bubbles = 0;
                    level=1;
                    duck.x = 0;
                    duck.el.css('margin-left',duck.x);

                    if (idx === undefined) {
                        idx=0;
                        sounds.title.play();
                        hideEverything();
                    } else {
                        idx++;
                    }
                    var scrollTxt = '                     *** A BUBBLE IN TROUBLE *** A 1 DAY GAME BY MNT FOR FLAREGAMES GAMEJAM 2012 *** PRESS CURSOR KEYS TO PLAY *** CATCH SMALL BUBBLES AND SOAP, AVOID EVERYTHING ELSE ***                     ';
                    topbar.str=scrollTxt.substr((idx/2) % scrollTxt.length,21);
                    setChars();
                    
                    if (dude.kd || dude.kl || dude.kr || dude.ku) {
                        sounds.title.stop();
                        updateScore();
                        waterIn(function() { levelInit(); });                    
                        return;
                    }
                
                    window.setTimeout(function(){
                        startScreen(idx);   
                    },50);
                    setNewWaterColors();
                }

                function waterOut(cb,state) {
                    if (undefined === state) {
                        plug.el.css('background-position','0 15px');
                        hideEverything();
                        sounds.plug.play();
                        for (var i=0;i<7;i++) {
                            getNode('waterbar'+i).show();
                        }
                        window.setTimeout(function() {
                            waterOut(cb,1);
                        },1000);
                        return;
                    }
                    
                    if (state<5) {
                        plug.el.css('background-position','0 '+(15-(state*4))+'px');
                        window.setTimeout(function() {
                            waterOut(cb,state+1);
                        },750);
                        return;
                    }
                    if (state==5) {
                        sounds.drain.play();
                    }
                    
                    var bar=(state-5);
                    console.log(bar,state);
                    getNode('waterbar'+bar).hide();
                    
                    if (state<12) {
                        window.setTimeout(function() {
                            waterOut(cb,state+1);
                        },740);
                        return;
                    }
                    
                    cb();
                }


                function waterIn(cb,state) {
                    if (undefined === state) {
                        plug.el.css('background-position','0 0px');
                        hideEverything();
                        sounds.plug.play();
                        for (var i=0;i<7;i++) {
                            getNode('waterbar'+i).hide();
                        }
                        window.setTimeout(function(){
                            waterIn(cb,1);
                        },1000);
                        return;
                    }
                    
                    if (state<5) {
                        plug.el.css('background-position','0 '+(state*4)+'px');
                        window.setTimeout(function() {
                            waterIn(cb,state+1);
                        },750);
                        return;
                    }
                    if (state==5) {
                        stream.el.css('display','block');
                        sounds.fill.play();
                    }
                    
                    var bar=6-(state-5);
                    getNode('waterbar'+bar).show();
                    
                    if (state<12) {
                        window.setTimeout(function() {
                            waterIn(cb,state+1);
                        },740);
                        return;
                    }
                    
                    stream.el.css('display','none');
                    cb();
                }
                
                function levelNext(state) {
                    if (tick) { 
                        window.clearInterval(tick); 
                        tick=null; 
                        
                        level++;
                        dude.bubbles=0;
                        dude.score+=250;
                        updateScore();
                    }
                    if (state>40) {
                        levelInit();
                        return;
                    }

                    //score hochzählen

                    setNewWaterColors();
                    window.setTimeout(function() {
                        levelNext(state?state+1:1);
                    },20);
                }

                function resetSoap() {
                    setNewSoapX();
                    sounds.soap.stop();
                    soap.delay = 10;
                    soap.y=-35;
                    soap.el.css('top',-35);
                }

                function levelInit() {
                    dude.x=112; 
                    dude.y=0; 
                    dude.bubbles=0;
                    
                    duck.x = 0;
                    duck.right = true;
                    duck.el.removeClass('mirror');

                    resetSoap();
                    
                    spider.delay=150;
                    spider.down=true;
                    spider.h=0;
                    
                    for (var i=0;i<10;i++) {
                        bubbles[i].x=8+(28*i);
                        bubbles[i].y=8*rand(11);
                    }

                    for (i=0;i<1;i++) {
                        croc[i].x=40;
                        croc[i].y=80+(80*i);
                        croc[i].t=0;
                        croc[i].d=0;
                    }

                    if (level>3) {
                        spider.el2.show();
                        spider.el.show();
                    } else {
                        spider.el2.hide();
                        spider.el.hide();
                    }

                    showEverything();
                    sounds.atmo.play();

                    tick = window.setInterval('levelTick()',20);
                }

                function levelTick() { 
                    dudeKi();
                    crocKi();
                    bubblesKi();
                    soapKi();
                    
                    bubblesCollision();
                    crocCollision();
                    soapCollision();
                    
                    if (level>1) {
                        duckKi();
                    }
                    duckCollision();
                    if (level>2) {
                        spiderKi();
                        spiderCollision();
                    }

                    if (dude.y<-90) {
                        levelNext();
                    }
                }

                function init() {
                    document.onkeydown = function(e){checkForKeyPress(e,1)}
                    document.onkeyup = function(e){checkForKeyPress(e,0)}

                    //cache dom-nodes
                    dude.el = getNode('dude');
                    for (var i=0;i<2;i++) {
                        croc[i].el=getNode('croc'+i);
                        croc[i].i = new Image();
                        croc[i].i.src = 'croc'+i+'.gif';
                    }
                    duck.el=getNode('duck');
                    soap.el=getNode('soap');
                    spider.el=getNode('web');
                    spider.el2=getNode('spider');
                    for (i=0;i<10;i++) {
                        bubbles[i].el=getNode('bubble'+i);
                    }
                    bubbles[i].el=getNode('plus');
                    plug.el = getNode('stoepsel');
                    stream.el=getNode('stream');
            
                    sounds.good = getNode('sound_good')[0];
                    sounds.bad = getNode('sound_bad')[0];
                    sounds.atmo = getNode('sound_atmo')[0];
                    sounds.boom = getNode('sound_boom')[0];
                    sounds.fill = getNode('sound_fill')[0];
                    sounds.plug = getNode('sound_plug')[0];
                    sounds.drain = getNode('sound_drain')[0];
                    sounds.soap = getNode('sound_soap')[0];
                    sounds.gameover = getNode('sound_gameover')[0];
                    sounds.title = getNode('sound_title')[0];
                    
                    for (var i=0;i<22;i++) {
                        topbar.el[i]=getNode('txt'+i);
                    }

                    decorateSounds();

                    dude.el.css('left',-1000);
                    for (var i in bubbles) {
                        bubbles[i].el.css('left',-1000);
                    }
                    croc[0].el.css('left',-1000);
                    croc[1].el.css('left',-1000);

                    setNewWaterColors();

                    for (var i=0;i<7;i++) {
                        getNode('waterbar'+i).hide();
                    }
                    updateScore();
                    startScreen();                
                }

            </script>
    </head>
    <body onload="init();">

        <?
        for ($i = 0; $i < 20; $i++) {
            echo '<div class="txt" id="txt' . $i . '" style="left:' . (($i * 16) + 8) . 'px;;"></div>';
        }
        ?>

        <img src="back.gif" style="position:absolute;z-index:5;top:0;left:0;" />
        <?
        echo '<div id="waterbar" style="position:absolute;left:0px;top:84px;height:156px;width:336px"></div>' . "\n";
        echo '<img id="waves" style="position:absolute;left:32px;top:85px;height:2px;width:272px;z-index:4" src="waveanim.gif"></div>' . "\n";
        for ($i = 0; $i < 7; $i++) {
            echo '<div id="waterbar' . $i . '" style="position:absolute;left:32px;top:' . (85 + ($i * 16)) . 'px;height:' . ($i == 7 ? 22 : 16) . 'px;width:272px"></div>' . "\n";
        }
        for ($i = 0; $i < 6; $i++) {
            echo '<div id="skybar' . $i . '" style="position:absolute;left:0;top:' . ($i == 0 ? 0 : 29 + ($i * 9)) . 'px;height:' . ($i == 0 ? 39 : 10) . 'px;width:336px"></div>' . "\n";
        }
        ?>

        <div id="stoepsel"></div>

        <div id="main">
            <img src="duck.gif" id="duck" style="top:76px;left:34px;" />
            <img src="croc0.gif" id="croc0" />
            <img src="croc1.gif" id="croc1" />
            <img src="dude.gif" id="dude" />
            <img src="spider.gif" id="spider" />
            <?
            for ($i = 0; $i < 10; $i++) {
                echo '<img src="1.gif" id="bubble' . $i . '" class="bub" />';
            }
            ?>
            <img src="0.gif" id="plus" class="bub" />
            <img src="soap.gif" id="soap" style="top:-32px" />
            <div id="stream"></div>
            <div id="web"></div>
        </div>
        
        <div class="grey">cursor keys to start</div>

        <audio id="sound_boom" src="boom.mp3" type="audio/mp3" preload="true" ></audio>
        <audio id="sound_atmo" src="bubbles.mp3" type="audio/mp3" preload="true" loop="true" ></audio>
        <audio id="sound_fill" src="faucet.mp3" type="audio/mp3" preload="true" ></audio>
        <audio id="sound_good" src="dading.mp3" type="audio/mp3" preload="true" ></audio>
        <audio id="sound_bad" src="bad.mp3" type="audio/mp3" preload="true" ></audio>
        <audio id="sound_plug" src="diesel.mp3" type="audio/mp3" preload="true" ></audio>
        <audio id="sound_drain" src="drain.mp3" type="audio/mp3" preload="true" ></audio>
        <audio id="sound_soap" src="soap.mp3" type="audio/mp3" preload="true" ></audio>
        <audio id="sound_gameover" src="gameover.mp3" type="audio/mp3" preload="true" ></audio>
        <audio id="sound_title" src="music.mp3" type="audio/mp3" preload="true" loop="true" ></audio>
        

    </body>
</html>
