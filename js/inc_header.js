function kontrola(formular){re=new RegExp("^[^@\.]([\.]?[^@\.]+)*@([^@\.]+[\.]{1}[^@\.]+)+$");if(formular.jmeno.value==""){alert("");return false;}else if(!re.test(formular.email.value)){alert("");return false;}else return true;}var swidth=678;var sheight=20;var sspeed=3;var restart=sspeed;var rspeed=sspeed;var spause=2000;var sbcolor="";var singletext=new Array();singletext[0]='<nobr align="center" class=tabb></nobr>';var ii=0;var gekso=0;if(navigator.product&&navigator.product=="Gecko"){var agt=navigator.userAgent.toLowerCase();var rvStart=agt.indexOf('rv:');var rvEnd=agt.indexOf(')',rvStart);var check15=agt.substring(rvStart+3,rvEnd);if(parseFloat(check15)<1.8)gekso=1;}var operbr=0;operbr=navigator.userAgent.toLowerCase().indexOf('opera');function goup(){if(sspeed!=rspeed*16){sspeed=sspeed*2;restart=sspeed;}}function godown(){if(sspeed>rspeed){sspeed=sspeed/2;restart=sspeed;}}function start(){if(document.getElementById){ns6div=document.getElementById('iens6div');if(operbr!=-1)operdiv=document.getElementById('operaslider');ns6div.style.left=swidth+"px";ns6div.innerHTML=singletext[0];sizeup=ns6div.offsetWidth;if(operbr!=-1&&sizeup==swidth){operdiv.innerHTML=singletext[0];sizeup=operdiv.offsetWidth;}ns6scroll();}else if(document.layers){ns4layer=document.ns4div.document.ns4div1;ns4layer.left=swidth;ns4layer.document.write(singletext[0]);ns4layer.document.close();sizeup=ns4layer.document.width;ns4scroll();}else if(document.all){iediv=iens6div;iediv.style.pixelLeft=swidth+"px";iediv.innerHTML=singletext[0];sizeup=iediv.offsetWidth;iescroll();}}function iescroll(){if(iediv.style.pixelLeft>0&&iediv.style.pixelLeft<=sspeed){iediv.style.pixelLeft=0;setTimeout("iescroll()",spause);}else if(iediv.style.pixelLeft>=sizeup*-1){iediv.style.pixelLeft-=sspeed+"px";setTimeout("iescroll()",100);}else{if(ii==singletext.length-1)ii=0;else ii++;iediv.style.pixelLeft=swidth+"px";iediv.innerHTML=singletext[ii];sizeup=iediv.offsetWidth;iescroll();}}function ns4scroll(){if(ns4layer.left>0&&ns4layer.left<=sspeed){ns4layer.left=0;setTimeout("ns4scroll()",spause);}else if(ns4layer.left>=sizeup*-1){ns4layer.left-=sspeed;setTimeout("ns4scroll()",100);}else{if(ii==singletext.length-1)ii=0;else ii++;ns4layer.left=swidth;ns4layer.document.write(singletext[ii]);ns4layer.document.close();sizeup=ns4layer.document.width;ns4scroll();}}function ns6scroll(){if(parseInt(ns6div.style.left)>0&&parseInt(ns6div.style.left)<=sspeed){ns6div.style.left=0;setTimeout("ns6scroll()",spause);}else if(parseInt(ns6div.style.left)>=sizeup*-1){ns6div.style.left=parseInt(ns6div.style.left)-sspeed+"px";setTimeout("ns6scroll()",100);}else{if(ii==singletext.length-1)ii=0;else ii++;ns6div.style.left=swidth+"px";ns6div.innerHTML=singletext[ii];sizeup=ns6div.offsetWidth;if(operbr!=-1&&sizeup==swidth){operdiv.innerHTML=singletext[ii];sizeup=operdiv.offsetWidth;}ns6scroll();}}var menuAlignment='left';var topMenuSpacer=0;var activateSubOnClick=false;var activeMenuItem=false;var activeTabIndex=0;var MSIE=navigator.userAgent.indexOf('MSIE')>=0?true:false;var Opera=navigator.userAgent.indexOf('Opera')>=0?true:false;var navigatorVersion=navigator.appVersion.replace(/.*?MSIE ([0-9]\.[0-9]).*/g,'$1')/1;function showHide(){if(activeMenuItem){activeMenuItem.className='inactiveMenuItem';var theId=activeMenuItem.id.replace(/[^0-9]/g,'');document.getElementById('submenu_'+theId).style.display='none';var img=activeMenuItem.getElementsByTagName('IMG');if(img.length>0)img[0].style.display='none';if(img.length>0)img[1].style.display='none';}var img=this.getElementsByTagName('IMG');if(img.length>0)img[0].style.display='inline';if(img.length>0)img[1].style.display='inline';activeMenuItem=this;this.className='activeMenuItem';var theId=this.id.replace(/[^0-9]/g,'');document.getElementById('submenu_'+theId).style.display='block';}function initMenu(){var mainMenuObj=document.getElementById('menu_main');var menuItems=mainMenuObj.getElementsByTagName('A');if(document.all){mainMenuObj.style.visibility='hidden';document.getElementById('menu_sub').style.visibility='hidden';}var currentLeftPos=15;for(var no=0;no<menuItems.length;no++){if(activateSubOnClick)menuItems[no].onclick=showHide;else menuItems[no].onmouseover=showHide;menuItems[no].id='mainMenuItem'+(no+1);if(menuAlignment=='left')menuItems[no].style.left=currentLeftPos+'px';else menuItems[no].style.right=currentLeftPos+'px';currentLeftPos=currentLeftPos+menuItems[no].offsetWidth+topMenuSpacer;var img=menuItems[no].getElementsByTagName('IMG');if(img.length>0){img[0].style.display='none';img[1].style.display='none';if(MSIE&&!Opera&&navigatorVersion<7){img[0].style.bottom='-1px';img[0].style.right='-1px';}}if(no==activeTabIndex){menuItems[no].className='activeMenuItem';activeMenuItem=menuItems[no];var img=activeMenuItem.getElementsByTagName('IMG');if(img.length>0)img[0].style.display='inline';if(img.length>0)img[1].style.display='inline';}else menuItems[no].className='inactiveMenuItem';if(!document.all)menuItems[no].style.bottom='-1px';if(MSIE&&navigatorVersion<6)menuItems[no].style.bottom='-2px';}var mainMenuLinks=mainMenuObj.getElementsByTagName('A');var subCounter=1;var parentWidth=mainMenuObj.offsetWidth;while(document.getElementById('submenu_'+subCounter)){var subItem=document.getElementById('submenu_'+subCounter);if(subCounter==(activeTabIndex+1)){subItem.style.display='block';}else{subItem.style.display='none';}subCounter++;}if(document.all){mainMenuObj.style.visibility='visible';document.getElementById('menu_sub').style.visibility='visible';}document.getElementById('menu_sub').style.display='block';}var casovani;function RotateBest(id,state){if(state=="on"){for(var i=0;i<5;i++){document.getElementById('top_article_button_'+i).style.backgroundColor='#006fbf';document.getElementById('top_article_button_'+i).style.color='#ffffff';document.getElementById('article_'+i).style.visibility='hidden';}document.getElementById('article_'+id).style.visibility='visible';document.getElementById('top_article_button_'+id).style.color='#006fbf';document.getElementById('top_article_button_'+id).style.backgroundColor='#ffffff';if(id==4){id=-1;}casovani=setTimeout("RotateBest("+id+" + 1,'on')",10*500);}if(state=="off"){clearTimeout(casovani);for(var i=0;i<5;i++){document.getElementById('top_article_button_'+i).style.backgroundColor='#006fbf';document.getElementById('top_article_button_'+i).style.color='#ffffff';document.getElementById('article_'+i).style.visibility='hidden';}document.getElementById('article_'+id).style.visibility='visible';document.getElementById('top_article_button_'+id).style.color='#006fbf';document.getElementById('top_article_button_'+id).style.backgroundColor='#ffffff';}}var intImage=new Array();function swapImage(id){switch(intImage[id]){case 1:document.getElementById(id).src="http://localhost/esuba/skins/basic/images/sys_icon_minus_2.gif";intImage[id]=2;return;case 2:document.getElementById(id).src="http://localhost/esuba/skins/basic/images/sys_icon_plus_2.gif";intImage[id]=1;return;}}