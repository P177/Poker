function kontrola(formular)
{
	re = new RegExp("^[^@\.]([\.]?[^@\.]+)*@([^@\.]+[\.]{1}[^@\.]+)+$");
	if (formular.jmeno.value=="") {
		alert ("<?php  if ($_GET['lang'] == "cz"){?>Napište prosím své jméno.<?php  } else {?>Please write your name.<?php  }?>");
		return false;}
	else if (!re.test(formular.email.value)) {
		alert ("<?php  if ($_GET['lang'] == "cz"){?>Vaše emailová adresa je neplatná! \n\ Zadejte ji, prosím, správně.<?php  } else {?>Your email is icorrect! \n\ Please type correct.<?php  }?>");
		return false;}
	else
	return true;
}
/***********************************
*   http://javascripts.vbarsan.com/
*   This notice may not be removed
***********************************/

//-- Begin Scroller's Parameters and messages -->
//scroller's width
var swidth=678;

//scroller's height
var sheight=20;

//scroller's speed
var sspeed=3;
var restart=sspeed;
var rspeed=sspeed;

//scroller's pause
var spause=2000;

//scroller's background
var sbcolor="";

//messages: change to your own; use as many as you'd like; set up Hyperlinks to URLs as you normally do: <a target=... href="... URL ...">..message..</a>
var singletext=new Array();
<?php
	$res_fortuna = mysql_query("SELECT news_text FROM $db_news WHERE news_id=2691") or die ("<strong>"._ERROR." 2</strong> ".mysql_error());
	$ar_fortuna = mysql_fetch_array($res_fortuna);
	$fortuna_text = PrepareFromDB($ar_fortuna['news_text']);
?>


singletext[0]='<nobr align="center" class=tabb><?php echo $fortuna_text;?></nobr>';
//end Parameters and message

// begin: Scroller's Algorithm
var ii=0;var gekso=0;if(navigator.product&&navigator.product=="Gecko"){var agt = navigator.userAgent.toLowerCase();var rvStart = agt.indexOf('rv:');var rvEnd = agt.indexOf(')', rvStart);var check15 = agt.substring(rvStart+3, rvEnd);if(parseFloat(check15)<1.8) gekso=1;}var operbr=0; operbr=navigator.userAgent.toLowerCase().indexOf('opera');
function goup(){if(sspeed!=rspeed*16){sspeed=sspeed*2;restart=sspeed;}}
function godown(){if(sspeed>rspeed){sspeed=sspeed/2;restart=sspeed;}}
function start(){
if(document.getElementById){ns6div=document.getElementById('iens6div');if(operbr!=-1)operdiv=document.getElementById('operaslider');ns6div.style.left=swidth+"px";ns6div.innerHTML=singletext[0];sizeup=ns6div.offsetWidth;if(operbr!=-1&&sizeup==swidth){operdiv.innerHTML=singletext[0];sizeup=operdiv.offsetWidth;}ns6scroll();}
else
if(document.layers){ns4layer=document.ns4div.document.ns4div1;ns4layer.left=swidth;ns4layer.document.write(singletext[0]);ns4layer.document.close();sizeup=ns4layer.document.width;ns4scroll();}
else
if(document.all){iediv=iens6div;iediv.style.pixelLeft=swidth+"px";iediv.innerHTML=singletext[0];sizeup=iediv.offsetWidth;iescroll();}}
function iescroll(){if(iediv.style.pixelLeft>0&&iediv.style.pixelLeft<=sspeed){iediv.style.pixelLeft=0;setTimeout("iescroll()",spause);}else
if(iediv.style.pixelLeft>=sizeup*-1){iediv.style.pixelLeft-=sspeed+"px";setTimeout("iescroll()",100);} else {if(ii==singletext.length-1)ii=0;else ii++;iediv.style.pixelLeft=swidth+"px";iediv.innerHTML=singletext[ii];sizeup=iediv.offsetWidth;iescroll();}}
function ns4scroll(){if(ns4layer.left>0&&ns4layer.left<=sspeed){ns4layer.left=0;setTimeout("ns4scroll()",spause);}else
if(ns4layer.left>=sizeup*-1){ns4layer.left-=sspeed;setTimeout("ns4scroll()",100);} else {if(ii==singletext.length-1)ii=0;else ii++;ns4layer.left=swidth;ns4layer.document.write(singletext[ii]);ns4layer.document.close();sizeup=ns4layer.document.width;ns4scroll();}}
function ns6scroll(){if(parseInt(ns6div.style.left)>0&&parseInt(ns6div.style.left)<=sspeed){ns6div.style.left=0;setTimeout("ns6scroll()",spause);}else if(parseInt(ns6div.style.left)>=sizeup*-1){ns6div.style.left=parseInt(ns6div.style.left)-sspeed+"px";setTimeout("ns6scroll()",100);}
else{if(ii==singletext.length-1)ii=0;else ii++;
ns6div.style.left=swidth+"px";ns6div.innerHTML=singletext[ii];sizeup=ns6div.offsetWidth;if(operbr!=-1&&sizeup==swidth){operdiv.innerHTML=singletext[ii];sizeup=operdiv.offsetWidth;}ns6scroll();}}
// end Algorithm

/*************************************************
*	Menu
*************************************************/
var menuAlignment = 'left';	// Align menu to the left or right?
var topMenuSpacer = 0; // Horizontal space(pixels) between the main menu items
var activateSubOnClick = false; // if true-> Show sub menu items on click, if false, show submenu items onmouseover

var activeMenuItem = false;	// Don't change this option. It should initially be false
var activeTabIndex = 0;	// Index of initial active tab	(0 = first tab) - If the value below is set to true, it will override this one.

var MSIE = navigator.userAgent.indexOf('MSIE')>=0?true:false;
var Opera = navigator.userAgent.indexOf('Opera')>=0?true:false;
var navigatorVersion = navigator.appVersion.replace(/.*?MSIE ([0-9]\.[0-9]).*/g,'$1')/1;

function showHide()	{
	if(activeMenuItem){
		activeMenuItem.className = 'inactiveMenuItem';
		var theId = activeMenuItem.id.replace(/[^0-9]/g,'');
		document.getElementById('submenu_'+theId).style.display='none';
		var img = activeMenuItem.getElementsByTagName('IMG');
		if(img.length>0)img[0].style.display='none'; // Zobrazi obrazek pred odkazem
		if(img.length>0)img[1].style.display='none'; // Zobrazi obrazek za odkazem
	}

	var img = this.getElementsByTagName('IMG');
	if(img.length>0)img[0].style.display='inline'; // Zobrazi obrazek pred odkazem
	if(img.length>0)img[1].style.display='inline'; // Zobrazi obrazek za odkazem

	activeMenuItem = this;
	this.className = 'activeMenuItem';
	var theId = this.id.replace(/[^0-9]/g,'');
	document.getElementById('submenu_'+theId).style.display='block';
}

function initMenu()	{
	var mainMenuObj = document.getElementById('menu_main');
	var menuItems = mainMenuObj.getElementsByTagName('A');
	if(document.all){
		mainMenuObj.style.visibility = 'hidden';
		document.getElementById('menu_sub').style.visibility='hidden';
	}
	var currentLeftPos = 15; // Odsazeni celeho menu z leva
	for(var no=0;no<menuItems.length;no++){
		if(activateSubOnClick)menuItems[no].onclick = showHide; else menuItems[no].onmouseover = showHide;
		menuItems[no].id = 'mainMenuItem' + (no+1);
		if(menuAlignment=='left')
			menuItems[no].style.left = currentLeftPos + 'px';
		else
			menuItems[no].style.right = currentLeftPos + 'px';
		currentLeftPos = currentLeftPos + menuItems[no].offsetWidth + topMenuSpacer;

		var img = menuItems[no].getElementsByTagName('IMG');
		if(img.length>0){
			img[0].style.display='none'; // Zobrazi obrazek pred odkazem
			img[1].style.display='none'; // Zobrazi obrazek za odkazem
			if(MSIE && !Opera && navigatorVersion<7){
				img[0].style.bottom = '-1px';
				img[0].style.right = '-1px';
			}
		}

		if(no==activeTabIndex){
			menuItems[no].className='activeMenuItem';
			activeMenuItem = menuItems[no];
			var img = activeMenuItem.getElementsByTagName('IMG');
			if(img.length>0)img[0].style.display='inline'; // Zobrazi obrazek pred odkazem
			if(img.length>0)img[1].style.display='inline'; // Zobrazi obrazek za odkazem

		}else menuItems[no].className='inactiveMenuItem';
		if(!document.all)menuItems[no].style.bottom = '-1px';
		if(MSIE && navigatorVersion < 6)menuItems[no].style.bottom = '-2px';
	}

	var mainMenuLinks = mainMenuObj.getElementsByTagName('A');

	var subCounter = 1;
	var parentWidth = mainMenuObj.offsetWidth;
	while(document.getElementById('submenu_' + subCounter)){
		var subItem = document.getElementById('submenu_' + subCounter);
		if(subCounter==(activeTabIndex+1)){
			subItem.style.display='block';
		} else {
			subItem.style.display='none';
		}

		subCounter++;
	}
	if(document.all){
		mainMenuObj.style.visibility = 'visible';
		document.getElementById('menu_sub').style.visibility='visible';
	}
	document.getElementById('menu_sub').style.display='block';
}
/*************************************************
*	Rotovani 5ti poslednich novinek
*************************************************/
var casovani;
function RotateBest(id,state){
	if (state == "on"){
		for (var i=0;i<5;i++){
			document.getElementById('top_article_button_'+i).style.backgroundColor='#006fbf';
			document.getElementById('top_article_button_'+i).style.color='#ffffff';
			document.getElementById('article_'+i).style.visibility='hidden';
		}
		document.getElementById('article_'+id).style.visibility='visible';
		document.getElementById('top_article_button_'+id).style.color='#006fbf';
		document.getElementById('top_article_button_'+id).style.backgroundColor='#ffffff';
		if (id == 4){id = -1;}
		casovani = setTimeout("RotateBest("+id+" + 1,'on')", 10 * 500);
	}
	if (state == "off"){
		clearTimeout(casovani);
		for (var i=0;i<5;i++){
			document.getElementById('top_article_button_'+i).style.backgroundColor='#006fbf';
			document.getElementById('top_article_button_'+i).style.color='#ffffff';
			document.getElementById('article_'+i).style.visibility='hidden';
		}
		document.getElementById('article_'+id).style.visibility='visible';
		document.getElementById('top_article_button_'+id).style.color='#006fbf';
		document.getElementById('top_article_button_'+id).style.backgroundColor='#ffffff';
	}
}
var intImage = new Array();
//intImage['mnu_img_1'] = 1;
//intImage['mnu_img_2'] = 1;
function swapImage(id) {
	switch (intImage[id]) {
		case 1:
	   	document.getElementById(id).src = "http://localhost/esuba/skins/basic/images/sys_icon_minus_2.gif";
	   	intImage[id] = 2;
		return;
		case 2:
		document.getElementById(id).src = "http://localhost/esuba/skins/basic/images/sys_icon_plus_2.gif";
		intImage[id] = 1;
		return;
	}
}