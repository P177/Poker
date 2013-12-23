<?php /*ř*/
$eden_cfg['www_dir'] = dirname(__FILE__);
$eden_cfg['www_dir_cms'] = $eden_cfg['www_dir']."/edencms/";
$eden_cfg['www_dir_cms_files'] = $eden_cfg['www_dir']."/edencms_files/";
$eden_cfg['www_dir_lang'] = $eden_cfg['www_dir']."/lang/";
$eden_cfg['ip'] = $_SERVER["REMOTE_ADDR"];

require_once($eden_cfg['www_dir_cms']."eden_init.php");
if (!isset($title_maintanance)){$title_maintanance = "";}
/******* Overeni POST a GET proti SSI a Javascript utokum - START *******/
require_once($eden_cfg['www_dir_cms']."eden_sec.php");
Kontrola_hlavicek();

/*
function GetMicrotime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}
$time_start = GetMicrotime();
*/
//setcookie('visit', 'PAGEVIEW', mktime(23,59,59, date('m'), date('d'), date('Y')), '/');

/******* Nacteni zakladnich nastaveni a sessions - START *******/
$project = "poker";
$_GET['lang'] = "cz";
require_once($eden_cfg['www_dir_lang']."lang_cz.php");
require_once($eden_cfg['www_dir_cms']."eden_lang_cz.php");
require_once($eden_cfg['www_dir_cms']."db.poker.inc.php");
require_once($eden_cfg['www_dir_cms']."sessions.php");
require_once($eden_cfg['www_dir_cms']."functions_frontend.php");
require_once($eden_cfg['www_dir_cms']."eden_forum.php");

/******* Nacteni cokies - START *******/
	if ((AGet($_COOKIE,$project."_autologin") == 1) && (AGet($_SESSION,'login') != $_COOKIE[$project."_name"]) && (AGet($_SESSION,'login_status') != "true")){
		$_GET['action'] = "login";
	}
	$link_adm = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	setcookie($project.'_link', '', time() - 186400);
	setcookie($project.'_link', $link_adm, time() + 186400);

// nastavenipro guestbook
$gid = 1;
if (AGet($_GET,'action') == "cups"){$_GET['page_mode'] = "cups";}
if (empty($_GET['action'])){$_GET['action'] = "article";}

/* Kdyz je uzivatel prihlaseny */
if (AGet($_SESSION,'loginid') != ""){
	/* Nacteni informaci o uzivateli */
	$res_admin = mysql_query("SELECT a.admin_nick, a.admin_lang, a.admin_priv, ai.admin_info_filter, ai.admin_info_customize_skin FROM $db_admin AS a, $db_admin_info AS ai WHERE a.admin_id=".(integer)AGet($_SESSION,'loginid')." AND ai.aid=".(integer)AGet($_SESSION,'loginid')) or die ("<strong>"._ERROR."</strong> ".mysql_error());
	$ar_admin = mysql_fetch_array($res_admin);
	
	// Zalozime ucet pro pozadovanou hru
	LeagueAddPlayer(AGet($_SESSION,'loginid'),0,1,0,0,0,0,0);
	
	$res2 = mysql_query("SELECT groups_secret_guestbook_look FROM $db_groups WHERE groups_id=".(float)$ar_admin['admin_priv']) or die ("<strong>"._ERROR." </strong> ".mysql_error());
	$ar2 = mysql_fetch_array($res2);
}

$res_setup = mysql_query("SELECT * FROM $db_setup") or die ("<strong>"._ERROR."</strong> ".mysql_error());
$ar_setup = mysql_fetch_array($res_setup);

//if (count($admin_custom) == 0){$admin_custom = array();}

if (empty($ar_admin['admin_info_filter'])){
	$filter_ar = array("all");
	$filter_ar = array_flip($filter_ar);
} else {
	$filter_ar = explode ("||", $ar_admin['admin_info_filter']);
	$filter_ar = array_flip($filter_ar);
}

// Zabanovani uzivatele, ktery je v seznamu
UserBan();
if (AGet($_GET,'action') == "allow_rg"){AllowReg($_GET['rg_code']);} /* Zobrazi oznameni o aktivaci uctu */
if (AGet($_GET,'action') == "allow_change_email"){AllowChangeEmail($_GET['rg_code']);} /* Zobrazi oznameni o zmene emailove adresy */
if (AGet($_GET,'action') == "login" || AGet($_POST,'action') == "login"){Login('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],"index.php?lang=".AGet($_GET,'lang')."&amp;filter=".AGet($_GET,'filter')."","index.php?lang=".AGet($_GET,'lang')."&amp;filter=".AGet($_GET,'filter')."");}
if (AGet($_GET,'action') == "logout"){Logout();}
if (AGet($_GET,'action') == "reg_scr" || AGet($_POST,'action') == "reg_scr"){$_GET['action'] = "reg";} /* Zajisti zobrazeni registracniho formulare pri chybe */
if (AGet($_GET,'action') == "edit_user"){$_GET['action'] = "user_edit";} /* Zajisti zobrazeni registracniho formulare pri chybe */

/******* TITLE *******/
if (AGet($_GET,'action') == "clanek" || AGet($_GET,'action') == "komentar"){
	if($_GET['modul'] == "news"){
		$res_title = mysql_query("SELECT news_headline FROM $db_news WHERE news_id=".(float)$_GET['id']) or die ("<strong>"._ERROR."</strong> ".mysql_error());
		$ar_title = mysql_fetch_array($res_title);
		$title = "Aktuality - ".TreatText($ar_title['news_headline'],"150")." - Pokerteam.cz";
	}elseif($_GET['modul'] == "poll"){
		$res_title = mysql_query("SELECT poll_questions_question FROM $db_poll_questions WHERE poll_questions_id=".(float)$_GET['id']) or die ("<strong>"._ERROR."</strong> ".mysql_error());
		$ar_title = mysql_fetch_array($res_title);
		$title = "Anketa - ".TreatText($ar_title['poll_questions_question'],"150")." - Pokerteam.cz";
	} else {
		$res_title = mysql_query("SELECT article_headline FROM $db_articles WHERE article_id=".(float)$_GET['id']) or die ("<strong>"._ERROR."</strong> ".mysql_error());
		$ar_title = mysql_fetch_array($res_title);
		$title = "Clánek - ".TreatText($ar_title['article_headline'],"150")." - Pokerteam.cz";
	}
}elseif (AGet($_GET,'action') == "forum"){
	$title = "Fórum - Pokerteam.cz";

}elseif (AGet($_GET,'action') == "kdehrat"){
	$title = "Kde hrát - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "kdehrat_kluby"){
	$title = "Kde hrát - Kluby a herny - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "kdehrat_online"){
	$title = "Kde hrát - Online herny - Pokerteam.cz";

}elseif (AGet($_GET,'action') == "liga_faq"){
	$title = "Pokerteam liga - FAQ - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "liga_pravidla"){
	$title = "Pokerteam liga - Pravidla - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "liga_zpravy"){
	$title = "Pokerteam liga - Zprávy z ligy - Pokerteam.cz";

}elseif (AGet($_GET,'action') == "online_poker"){
	$title = "Online poker - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "profily_hracu"){
	$title = "Profily hrácu - Pokerteam.cz";

}elseif (AGet($_GET,'action') == "strategie"){
	$title = "Strategie - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "strategie_cash_games"){
	$title = "Strategie - Cash Games - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "strategie_turnaje"){
	$title = "Strategie - Turnaje - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "strategie_obecne"){
	$title = "Strategie - Obecné - Pokerteam.cz";

}elseif (AGet($_GET,'action') == "turnaje"){
	$title = "Reportáže z turnaju - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "turnaje_ept"){
	$title = "Reportáže z turnaju - European Poker Tour - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "turnaje_wsop"){
	$title = "Reportáže z turnaju - World Series of Poker - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "turnaje_wpt"){
	$title = "Reportáže z turnaju - World Poker Tour - Pokerteam.cz";
}elseif (AGet($_GET,'action') == "turnaje_ostatni"){
	$title = "Reportáže z turnaju - Ostatní - Pokerteam.cz";

}elseif (AGet($_GET,'action') == "zaciname"){
	$title = "Začínáme s pokerem - Pokerteam.cz";

}elseif (AGet($_GET,'action') == "zajimavosti"){
	$title = "Pokerové zajímavosti - Pokerteam.cz";
} else {
	$title = "Pokerteam.cz - Nejlépe rozdané karty na webu";
}

/* Vyber zobrazeni veci c levem a pravem panelu */
if (AGet($_GET,'action') == "player" || AGet($_GET,'action') == "league_team" || AGet($_GET,'action') == "league_team_reg" || AGet($_GET,'action') == "team_list" || AGet($_GET,'action') == "user_edit"){$panels = "low";}else{ $panels = "full";}

/******* VYBER KATEGORII PRO ZOBRAZENI *******/
$poker_categories['poker_all_clanky']		= "2:6:7:8:10:11:12:14:15:16:18:20:33:34:37:38:39:40";
$poker_categories['poker_all_aktuality']	= "19:24:28:29:31:35";

$poker_categories['poker_liga'] = "2";
$poker_categories['poker_zaciname'] = "6:7:8";
$poker_categories['poker_strategie'] = "10:11:12";
$poker_categories['poker_kdehrat'] = "14:15:16";
$poker_categories['poker_turnaje'] = "37:38:39:40";

/******* SKIN *******/
$eden_project_skin = CheckSkin();
if ($eden_project_skin != ""){ $eden_project_skin .= "/";}

$res_pm = mysql_query("SELECT COUNT(*) FROM $db_forum_pm WHERE forum_pm_recipient_id=".(integer)AGet($_SESSION,'loginid')." AND forum_pm_del<>".(integer)AGet($_SESSION,'loginid')."" ) or die ("<strong>"._ERROR." </strong> ".mysql_error());
$num_pm = mysql_fetch_array($res_pm);
$res_pm_log = mysql_query("SELECT forum_pm_log_posts FROM $db_forum_pm_log WHERE forum_pm_log_admin_id=".(integer)AGet($_SESSION,'loginid')) or die ("<strong>"._ERROR." </strong>".mysql_error());
$ar_pm_log = mysql_fetch_array($res_pm_log);
$res_sgb = mysql_query("SELECT COUNT(*) FROM $db_forum_posts WHERE forum_posts_pid=44") or die ("<strong>"._ERROR." </strong> ".mysql_error());
$num_sgb = mysql_fetch_array($res_sgb);
$res_sgb_log = mysql_query("SELECT forum_posts_log_posts FROM $db_forum_posts_log WHERE forum_posts_log_forum_topic_id=44 AND forum_posts_log_admin_id=".(integer)AGet($_SESSION,'loginid')) or die ("<strong>"._ERROR." </strong>".mysql_error());
$ar_sgb_log = mysql_fetch_array($res_sgb_log);
$res_online_all = mysql_query("SELECT COUNT(*) FROM $db_sessions WHERE sessions_pages='".mysql_real_escape_string($eden_cfg['misc_web'])."' GROUP BY sessions_user") or die ("<strong>"._ERROR." </strong> ".mysql_error());
$num_online_all = mysql_fetch_array($res_online_all);
$res_online_usr = mysql_query("SELECT sessions_pages FROM $db_sessions WHERE sessions_pages='".mysql_real_escape_string($eden_cfg['misc_web'])."' GROUP BY sessions_user") or die ("<strong>"._ERROR." </strong> ".mysql_error());
$num_online_usr = mysql_num_rows($res_online_usr);

echo "<!doctype html public \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
echo "<head>\n";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
echo "<title>".$title_maintanance.$title."</title>\n";
echo "<link href=\"".$eden_cfg['url_skins'].$eden_project_skin."eden-common.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\">\n";
echo "<link href=\"".$eden_cfg['url_skins'].$eden_project_skin."poker.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\">\n";
echo "<meta name=\"keywords\" lang=\"cs\" content=\"pokerteam, pokerstars, online, poker, turnaj, texas, holdem, hold'em, liga, wsop\">\n";
echo "<meta name=\"description\" lang=\"cs\" content=\"Vše o pokeru\">\n";
echo "<meta name=\"generator\" content=\"EDEN Content Management System - www.blackfoot.cz/eden/\">\n";
echo "<meta name=\"author\" content=\"Pokerteam.cz\">\n";
echo "<meta name=\"robots\" content=\"all,follow\">\n";
echo "<meta name=\"copyright\" content=\"© 2010, Pokerteam.cz\">\n";
echo "<meta http-equiv=\"content-language\" content=\"cs\">\n";
echo "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">\n";
echo "<meta http-equiv=\"Content-Script-Type\" content=\"text/javascript\">\n";
echo "<script type=\"text/javascript\" src=\"".$eden_cfg['url']."js/animatedcollapse.js\"></script>";
echo "<script type=\"text/javascript\" src=\"".$eden_cfg['url']."js/jquery.js\"></script>\n";
include ($eden_cfg['www_dir_cms']."eden_js.php");
if (AGet($_GET,'action') == "clanek" || AGet($_GET,'action') == "komentar"){
  echo "<style type=\"text/css\">\n";
	echo "h1 {font-size:20px;}\n";
	echo "h1 A {font-size:20px;}\n";
	echo "h1 A:visited {font-size:20px;}\n";
	echo "h1 A:hover {font-size:20px;}\n";
  echo "</style>\n";
}
echo "<script type=\"text/javascript\">";
echo "<!-- \n";
echo "var uniquepageid=\"".$project."\";"; /* AnimatedColapse */
echo "var v_form_name=\""; if (AGet($_GET,'lang') == "cz"){ echo "Napište prosím své jméno.";  } else { echo "Please write your name."; } echo "\";";
echo "var v_form_email=\""; if (AGet($_GET,'lang') == "cz"){ echo "Vaše emailová adresa je neplatná!  Zadejte ji, prosím, správně."; } else { echo "Your email is icorrect! Please type correct."; } echo "\";";
echo "var v_skin_path_plus=\"".$eden_cfg['url_skins'].$eden_project_skin."images/sys_icon_plus_2.gif\";";  /* AnimatedColapse */
echo "var v_skin_path_minus=\"".$eden_cfg['url_skins'].$eden_project_skin."images/sys_icon_minus_2.gif\";\n"; /* AnimatedColapse */
echo "var v_chcolor=\""; if ($ar_sgb_log['forum_posts_log_posts'] < $num_sgb[0] && ($_SESSION['u_status'] == "admin")){ echo "1";} else {echo "0";} echo "\";";/* Skript pro blikani secret Guestbooku */
echo "//-->\n";
echo "</script>\n";
echo "<script type=\"text/javascript\" src=\"".$eden_cfg['url']."js/poker.js\"></script>\n";
echo "<script type=\"text/javascript\" src=\"".$eden_cfg['url']."js/eden.js\"></script>\n";
echo "</head>\n";
echo "<body>"; /* onLoad=\" if (AdminCustom(10) == 1){echo "start();";} if (!array_key_exists(10,$admin_custom)){echo "start();";} if (AGet($_SESSION,'loginid') == 100000000000000 || AGet($_SESSION,'loginid') == 6666666666666666){ makerequest('ajax_data.php?ajx=clanwars_left&amp;aid=".AGet($_SESSION,'loginid')."','cw_l'); } */ 
echo "<div id=\"container\">\n";
echo "<a name=\"top\"></a>";
echo "<div id=\"menu_custom\">\n";
echo "	<div id=\"menu_custom_left\">"; if (AGet($_SESSION,'loginid') != ""){ echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:MM_swapImgRestore();MM_swapImage('customize','','".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_icon_minus.gif',1);collapse1.slideup();collapse2.slideit()\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_icon_plus.gif\" width=\"12\" height=\"12\" name=\"customize\"  style=\"position:relative;top:2px;\" alt=\".\">&nbsp;&nbsp;"._MENU_CUS_CUS."</a>&nbsp; &nbsp;"; } echo "<a href=\"javascript:MM_swapImgRestore();MM_swapImage('search','','".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_icon_minus.gif',1);collapse2.slideup();collapse1.slideit();\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_icon_plus.gif\" width=\"12\" height=\"12\" style=\"position:relative;top:2px;\" name=\"search\" alt=\".\">&nbsp;&nbsp;"._MENU_CUS_SEARCH."</a></div>\n";
echo "	<div id=\"menu_custom_right\">";
	echo "<form action=\"index.php?lang=".AGet($_GET,'lang')."&amp;filter=".AGet($_GET,'filter')."\" method=\"post\">";
	echo "<ul id=\"layout_login\">";
	if ($_SESSION['u_status'] == "" || $_SESSION['u_status'] == "vizitor"){
		unset($login);
		echo "<li><input type=\"hidden\" name=\"action\" value=\"login\"><input tabindex=\"3\" type=\"submit\" id=\"login_submit\" value=\"\" title=\""._LOGIN."\"></li>\n";
		echo "<li><input tabindex=\"2\" type=\"password\" id=\"login_pass\" name=\"pass\" value=\"password\" onFocus=\"if (this.value=='password') this.value='';\" onBlur=\"if (this.value=='') this.value='password';\" size=\"10\"></li>\n";
		echo "<li><input tabindex=\"1\" type=\"text\" id=\"login_name\" name=\"login\" value=\"username\" onFocus=\"if (this.value=='username') this.value='';\" onBlur=\"if (this.value=='') this.value='username';\"  onMouseDown=\"this.value=''\" size=\"10\"></li>";
	}
	echo "<li>";
	if ($_SESSION['u_status'] != "vizitor" && $_SESSION['nick'] != ""){
		echo "<a href=\"index.php?action=player&amp;mode=player_acc&amp;id=".AGet($_SESSION,'loginid')."&amp;lang=".AGet($_GET,'lang')."&amp;filter=".AGet($_GET,'filter')."\">".stripslashes($ar_admin['admin_nick'])."</a>";
		if (($_SESSION['u_status'] == "user" || $_SESSION['u_status'] == "admin") && $ar_pm_log['forum_pm_log_posts'] < $num_pm[0]){
			echo "<a href=\"index.php?project=".$_SESSION['project']."&amp;action=forum&amp;faction=pm\"><img src=\"images/sys_message_new.gif\" alt=\""._PM."\" title=\""._PM."\" width=\"15\" height=\"10\" border=\"0\" style=\"position:relative;top:2px;\"> ("; echo $num_pm[0] - $ar_pm_log['forum_pm_log_posts']; echo ")</a>";
		} else {
			echo "<a href=\"index.php?project=".$_SESSION['project']."&amp;action=forum&amp;faction=pm\"><img src=\"images/sys_message.gif\" alt=\""._PM."\" title=\""._PM."\" width=\"15\" height=\"10\" border=\"0\" style=\"position:relative;top:2px;\"></a>";
		}
		echo "&nbsp;<span class=\"menu_custom_divider\">|</span>&nbsp;ID: ".AGet($_SESSION,'loginid');
	}
	if ($_SESSION['u_status'] == "" || $_SESSION['u_status'] == "vizitor"){
		echo "<a href=\"index.php?action=reg_scr&amp;filter=".AGet($_GET,'filter')."\">"._REG."</a>";
		echo "&nbsp;<span class=\"menu_custom_divider\">|</span>&nbsp;<a href=\"index.php?action=forgotten_pass&amp;project=".$_SESSION['project']."&amp;filter=".AGet($_GET,'filter')."\">"._LOGIN_FORGOTTEN_PASS."</a>&nbsp;&nbsp;";
	}
	if ($_SESSION['u_status'] != "vizitor" && $_SESSION['login'] != ""){
		echo "&nbsp;<span class=\"menu_custom_divider\">|</span>&nbsp;<a href=\"index.php?action=user_edit&amp;filter=".AGet($_GET,'filter')."&amp;mode=edit_user\">"._USER_EDIT."</a>\n";
		echo "&nbsp;<span class=\"menu_custom_divider\">|</span>&nbsp;<a href=\"index.php?action=forum&amp;project=".$_SESSION['project']."&amp;faction=friends&amp;filter=".AGet($_GET,'filter')."\">"._USER_FRIENDS."</a>\n";
		if ($ar2['groups_secret_guestbook_look'] == 1){ echo "&nbsp;<span class=\"menu_custom_divider\">|</span>&nbsp;<a href=\"index.php?action=forum&amp;filter=".AGet($_GET,'filter')."&amp;project=esuba&amp;faction=posts&amp;id0=42&amp;id1=43&amp;id2=44&amp;page=1\" "; if ($ar_sgb_log['forum_posts_log_posts'] < $num_sgb[0]){echo "id=\"sgb\"";} echo ">Secret GB</a>"; }
		echo "&nbsp;<span class=\"menu_custom_divider\">|</span>&nbsp;"._CMN_ONLINE.": <a title=\"Online\">".$num_online_all[0]."</a>/<a href=\"index.php?action=whoisonline&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Users\">".$num_online_usr."</a>\n";
		echo "&nbsp;<span class=\"menu_custom_divider\">|</span>&nbsp;<a href=\"index.php?action=logout&amp;project=".$_SESSION['project']."&amp;filter=".AGet($_GET,'filter')."\">"._LOGOUT."</a>&nbsp;&nbsp;";
	}
echo "		</li>\n";
echo "		</ul>\n";
echo "		</form>\n";
echo "	</div>\n";
echo "</div>\n";
echo "<div id=\"cat1\" style=\"width:964px;height:50px;\">\n";
echo "	<div style=\"padding: 0px 0px 0px 5px;\">\n";
echo "		<div align=\"center\" style=\"padding-left:10px; padding-top:0px;\">&nbsp;"; Search(20,0,1); echo "</div>\n";
echo "	</div>\n";
echo "</div>\n";
echo "<div id=\"cat2\" style=\"width:964px;height:210px;\">\n";
echo "	<div style=\"padding:0px 0px 0px 5px;\">\n";
echo "		<form action=\"eden_save.php?lang=".AGet($_GET,'lang')."&amp;filter=".AGet($_GET,'filter')."\" method=\"post\">\n";
echo "		<table style=\"width:940px;height:200px;\" cellspacing=\"2\" cellpadding=\"5\" border=\"0\">\n";
echo "			<tr>\n";
echo "				<td colspan=\"6\"><strong>"._ESUBA_CUSTOM_PANELS;
				/*
  				id_1 = Ankety
				id_2 = Slovnik
				id_3 = Best article
				id_4 = Co znamena
				id_5 = Pokerovi profici
				*/
echo "			</strong></td>\n";
echo "			</tr>\n";
echo "			<tr>\n";
echo "				<td style=\"background-color:#f6f6f6\"><input type=\"hidden\" name=\"admin_custom[id_1]\" value=\"1\"><input type=\"checkbox\" name=\"admin_custom[custom_1]\" value=\"1\" "; if (AdminCustom(1) == 1){echo "checked=\"checked\"";} echo "> "._MENU_CUS_CUS_POLL."</td>\n";
echo "				<td style=\"background-color:#f6f6f6\"><input type=\"hidden\" name=\"admin_custom[id_2]\" value=\"2\"><input type=\"checkbox\" name=\"admin_custom[custom_2]\" value=\"1\" "; if (AdminCustom(2) == 1){echo "checked=\"checked\"";} echo "> "._MENU_CUS_CUS_DICT."</td>\n";
echo "				<td style=\"background-color:#f6f6f6\"><input type=\"hidden\" name=\"admin_custom[id_3]\" value=\"3\"><input type=\"checkbox\" name=\"admin_custom[custom_3]\" value=\"1\" "; if (AdminCustom(3) == 1){echo "checked=\"checked\"";} echo "> Nejnovější články</td>\n";
echo "				<td style=\"background-color:#f6f6f6\"><input type=\"hidden\" name=\"admin_custom[id_4]\" value=\"4\"><input type=\"checkbox\" name=\"admin_custom[custom_4]\" value=\"1\" "; if (AdminCustom(4) == 1){echo "checked=\"checked\"";} echo "> Co znamená</td>\n";
echo "				<td style=\"background-color:#f6f6f6\"><input type=\"hidden\" name=\"admin_custom[id_5]\" value=\"5\"><input type=\"checkbox\" name=\"admin_custom[custom_5]\" value=\"1\" "; if (AdminCustom(5) == 1){echo "checked=\"checked\"";} echo "> Pokeroví profíci</td>\n";
echo "			</tr>\n";
echo "			<tr>\n";
echo "				<td colspan=\"6\"><input value=\"Odeslat\" type=\"submit\" class=\"eden_button\">\n";
echo "					<input type=\"hidden\" name=\"mode\" value=\"admin_custom_save\">\n";
echo "					<input type=\"hidden\" name=\"project\" value=\"".$project."\">\n";
echo "				</td>\n";
echo "			</tr>\n";
echo "		</table>\n";
echo "		</form>\n";
echo "	</div>\n";
echo "</div>\n";
echo "<script type=\"text/javascript\">\n";
echo "<!--\n";
echo "var collapse1=new animatedcollapse(\"cat1\", 300, false)\n";
echo "var collapse2=new animatedcollapse(\"cat2\", 300, false)\n";
echo "//-->\n";
echo "</script>\n";
echo "<div id=\"menu_logo\">\n";
echo "	<div id=\"menu_logo_1\"></div>\n";
echo "	<div id=\"menu_logo_link\" onclick=\"document.location.href='".$eden_cfg['url']."index.php?action=&amp;filter=".AGet($_GET,'filter')."'\">&nbsp;</div>\n";
echo "	<div id=\"menu_logo_2\">";
?>
<script language="javascript" type="text/javascript">
   var p = document.location.protocol;
   if (!p || p == null) p = "";
   var s = (p.toLowerCase().indexOf("http") == 0 ? p : "http:") + "//pokerstars.com/cs/ad/10171288/468x60.js";
   var r = Math.floor(Math.random()*999999)+''+Math.floor(Math.random()*999999);
   var c = document.createElement("script");
   c.type = "text/javascript";
   c.src = s+"?r="+r;
   c.id = ""+r;
   c.async = true;
   var a = document.getElementsByTagName("script");
   var t = a[a.length-1];
   t.parentNode.insertBefore(c, t);
</script>
<noscript><a href="http://pokerstars.com/cs/ad/10171288/468x60fd.gif.click?rq=noscript&vs="><img src="http://pokerstars.com/cs/ad/10171288/468x60fd.gif?rq=noscript&vs=" width="468" height="60" alt="" border="0"/></a></noscript>
<?php
/* Reklama(43);*/
echo "</div>\n";
echo "</div>\n";
echo "<!-- This is the start of the menu -->\n";
echo "<div id=\"menu_main\">\n";
echo "	<a href=\"".$eden_cfg['url']."index.php?action=&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Novinky\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_left.gif\" width=\"12\" height=\"27\" alt=\"Novinky\" title=\"Novinky\" align=\"middle\">&nbsp;Novinky&nbsp;<img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_right.gif\" width=\"12\" height=\"27\" align=\"middle\" alt=\".\"></a>\n";
echo "	<a href=\"".$eden_cfg['url']."index.php?action=league_news&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Pokerteam liga\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_left.gif\" width=\"12\" height=\"27\" alt=\"Pokerteam liga\" title=\"Pokerteam liga\" align=\"middle\">&nbsp;Pokerteam liga&nbsp;<img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_right.gif\" width=\"12\" height=\"27\" align=\"middle\" alt=\".\"></a>\n";
echo "	<a href=\"".$eden_cfg['url']."index.php?action=zaciname&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Zacínáme s pokerem\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_left.gif\" width=\"12\" height=\"27\" alt=\"Začínáme s pokerem\" title=\"Začínáme s pokerem\" align=\"middle\">&nbsp;Začínáme s pokerem&nbsp;<img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_right.gif\" width=\"12\" height=\"27\" align=\"middle\" alt=\".\"></a>\n";
echo "	<a href=\"".$eden_cfg['url']."index.php?action=strategie&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Strategie\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_left.gif\" width=\"12\" height=\"27\" alt=\"Strategie\" title=\"Strategie\" align=\"middle\">&nbsp;Strategie&nbsp;<img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_right.gif\" width=\"12\" height=\"27\" align=\"middle\" alt=\".\"></a>\n";
echo "	<a href=\"".$eden_cfg['url']."index.php?action=kdehrat&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Kde hrát?\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_left.gif\" width=\"12\" height=\"27\" alt=\"Kde hrát?\" title=\"Kde hrát?\" align=\"middle\">&nbsp;Kde hrát?&nbsp;<img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_right.gif\" width=\"12\" height=\"27\" align=\"middle\" alt=\".\"></a>\n";
echo "	<a href=\"".$eden_cfg['url']."index.php?action=turnaje&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Turnaje\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_left.gif\" width=\"12\" height=\"27\" alt=\"Turnaje\" title=\"Turnaje\" align=\"middle\">&nbsp;Reportáže z turnajů&nbsp;<img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_right.gif\" width=\"12\" height=\"27\" align=\"middle\" alt=\".\"></a>\n";
echo "	<a href=\"".$eden_cfg['url']."index.php?action=forum&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\""._MENU_FORUM_HELP."\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_left.gif\" width=\"12\" height=\"27\" alt=\"Fórum\" title=\"Fórum\" align=\"middle\">&nbsp;Fórum&nbsp;<img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_menu_tabmenu_right.gif\" width=\"12\" height=\"27\" align=\"middle\" alt=\".\"></a>\n";
echo "</div>\n";
echo "<div id=\"menu_sub\">\n";
echo "	<div id=\"submenu_1\" class=\"menu_submenu\">\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Hlavní stránka\">Hlavní stránka</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=zajimavosti&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Pokerové zajímavosti\">Pokerové zajímavosti</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=profily_hracu&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Profily hráčů\">Profily hráčů</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=online_poker&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Online poker\">Online poker</a>\n";
echo "	</div>\n";
echo "	<div id=\"submenu_2\" class=\"menu_submenu\">\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=league_team_reg&amp;gid=1&amp;lid=1&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Registrace týmu\">Registrace týmu</a>&nbsp;&nbsp;|&nbsp;&nbsp;	\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=league_about&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"O lize\">O lize</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=league_rules&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Pravidla\">Pravidla</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=league_results_teams&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Ligová tabulka\">Ligová tabulka</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=league_results_players&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Pořadí hráčů\">Pořadí hráčů</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=league_results_rounds&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Výsledky\">Výsledky</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?lang=cz&amp;filter=".AGet($_GET,'filter')."&amp;action=clanek&amp;id=3417\" target=\"_self\" title=\"Harmonogram\">Harmonogram</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=league_team&amp;mode=team_list&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Seznam týmu\">Seznam týmu</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?lang=cz&amp;filter=".AGet($_GET,'filter')."&amp;action=clanek&amp;id=3408\" target=\"_self\" title=\"Ceny\">Ceny</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?lang=cz&amp;filter=".AGet($_GET,'filter')."&amp;action=clanek&amp;id=3410\" target=\"_self\" title=\"FAQ\">FAQ</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=league_team&amp;mode=team_draft&amp;lang=".AGet($_GET,'lang')."&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Draft hráčů\">Draft hráčů</a>\n";
echo "	</div>\n";
echo "	<div id=\"submenu_3\" class=\"menu_submenu\">\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=zaciname&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Zacínáme s pokerem\">Začínáme s pokerem</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?lang=cz&amp;filter=".AGet($_GET,'filter')."&amp;action=clanek&amp;id=3407\" target=\"_self\" title=\"Jak se zaregistrovat na PokerStars\">Jak se zaregistrovat na PokerStars</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=dict&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Slovník\">Slovník</a>\n";
echo "	</div>\n";
echo "	<div id=\"submenu_4\" class=\"menu_submenu\">\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=strategie_obecne&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Obecné\">Obecné</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=strategie_turnaje&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Turnaje\">Turnaje</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=strategie_cash_games&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Cash Games\">Cash Games</a>\n";
echo "	</div>\n";
echo "	<div id=\"submenu_5\" class=\"menu_submenu\">\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=kdehrat_online&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Online herny\">Online herny</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=kdehrat_kluby&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Kluby a herny\">Kluby a herny</a>\n";
echo "	</div>\n";
echo "	<div id=\"submenu_6\" class=\"menu_submenu\">\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=turnaje_ept&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"European Poker Tour\">European Poker Tour</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=turnaje_wsop&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"World Series of Poker\">World Series of Poker</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=turnaje_wpt&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"World Poker Tour\">World Poker Tour</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=turnaje_ostatni&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Ostatní\">Ostatní</a>\n";
echo "	</div>\n";
echo "	<div id=\"submenu_7\" class=\"menu_submenu\">\n";
echo "		<a href=\"".$eden_cfg['url']."index.php?action=forum&amp;filter=".AGet($_GET,'filter')."\" target=\"_self\" title=\"Ostatní\">Fórum</a>\n";
echo "	</div>\n";
echo "</div>\n";
echo "<div style=\"height:5px;width:950px;\"><img src=\"images/bod.gif\" width=\"1\" height=\"1\" alt=\".\" onload=\"javascript:initMenu()\"></div>\n";
if (!empty($_GET['msg']) || AGet($_GET,'action') == "msg"){
?>
<script>
// prepare the form when the DOM is ready 
$(document).ready(function() { 
    $('#content_err_col').delay(7000).fadeOut(400); 
});
</script>
<?php
	echo "<div id=\"content_err_col\" class=\"clearfix\">\n";
	echo "	<div id=\"content_err_col_cont\">\n";
	echo "		<div id=\"content_err_col_text\"><br><strong>"; Msg($_GET['msg']); echo "</strong><br><br></div>\n";
	echo "	</div>\n";
	echo "</div>";
 	}
echo "<!-- //////////////////////////////////////////////////////// HEADER - END ////////////////////////////////////////////////// -->";
echo "<!-- //////////////////////////////////////////////////////// LEFT ////////////////////////////////////////////////// -->";
if (AGet($_GET,'action') != "forum"){
	echo "<div id=\"content\" class=\"clearfix\">";
	echo "	<!-- ///////////////////////// LEFT ///////////////////////// -->";
	echo "	<div class=\"content_side_col\">";
	echo "<!-- # 10 NEJ TYMU -->";
	echo "	<div class=\"content_side_col_header\">\n";
	echo "		<div class=\"content_side_col_title\">Ligová tabulka</div>\n";
	echo "	</div>\n";
	echo "	<div class=\"content_side_col_cont\">\n";
	echo "		<div class=\"content_side_col_text\">\n";
	echo "			<div class=\"content_side_col_text_results\"><span class=\"nadpis_liga_vysledky\">Pokerteam liga</span>"; LeagueSeasonTeamsResults(17,2); /* Prvni cislo je ID sezony */ echo "</div>\n";
	echo "		</div>\n";
	echo "	</div>\n";
	echo "<!-- *** 10 NEJ TYMU - KONEC *** -->";
	if (AdminCustom(4) == 1  && $panels == "full"){
		echo "<!-- # CO ZNAMENA? -->\n";
		echo "	<div class=\"content_side_col_header\">\n";
		echo "		<div class=\"content_side_col_title\">Co znamená?</div>\n";
		echo "	</div>\n";
		echo "	<div class=\"content_side_col_cont\">\n";
		echo "		<div class=\"content_side_col_text\">\n";
					DictionaryWhatMeans();
		echo "		</div>\n";
		echo "	</div>\n";
		echo "<!-- *** CO ZNAMENA - KONEC *** -->\n";
		echo "<!-- # FACEBOOK -->\n";
		echo "	<div class=\"content_side_col_header\">\n";
		echo "		<div class=\"content_side_col_title\">Následujte nás</div>\n";
		echo "	</div>\n";
		echo "	<div class=\"content_side_col_cont\">\n";
		echo "		<div><a href=\"http://www.facebook.com/search.php?q=pokerteam&init=quick#!/pages/Pokerteamcz/159054291705\" target=\"_blank\"><img src=\"images/facebook.gif\" width=\"175\" height=\"48\" alt=\"Pokerteam na Facebooku\" title=\"Pokerteam na Facebooku\"></a></div>\n";
		echo "	</div>\n";
		echo "<!-- *** FACEBOOK *** -->";
	}
	if (AGet($_GET,'action') != "clanek" && AdminCustom(1) == 1  && $panels == "full"){
		echo "<!-- *** ANKETA *** -->\n";
		echo "<div class=\"content_side_col_header\">\n";
		echo "	<div class=\"content_side_col_title\">"._POLL."</div>\n";
		echo "</div>\n";
		echo "<div class=\"content_side_col_cont\">\n";
		Poll(0,AGet($_GET,'lang'),135,10,"poll",125,41); echo "<br>\n";
		echo "	&nbsp;<a href=\"index.php?action=oldpolls&amp;lang=".AGet($_GET,'lang')."\" target=\"_self\">"._POLL_OLDER."</a>\n";
		echo "	</div>\n";
		echo "	<!-- *** ANKETA - KONEC *** -->\n";
	}
	if (AdminCustom(2) == 1){
		echo "<!-- *** SLOVNIK *** -->\n";
		echo "<div class=\"content_side_col_header\">\n";
		echo "	<div class=\"content_side_col_title\">"._DICTIONARY."</div>\n";
		echo "</div>\n";
		echo "<div class=\"content_side_col_cont\">\n";
		echo "	<div class=\"content_side_col_text\" style=\"min-height: 80px;\"><form action=\"index.php\" method=\"get\">\n";
		echo "		<table border=\"0\">\n";
		echo "			<tr>\n";
		echo "				<td>"._DICTIONARY_WORD.":<br>\n";
		echo "				<input type=\"text\" id=\"word\" name=\"word\" value=\"\" size=\"27\" autocomplete=\"off\" onkeyup=\"ajax_showOptions(this,'getDictionaryByLetters=1&amp;project=".$_SESSION['project']."',event)\">\n";
		echo "				<input type=\"hidden\" id=\"word_hidden\" name=\"id\">\n";
		echo "				<input type=\"hidden\" name=\"action\" value=\"dict\">\n";
		echo "				<input type=\"hidden\" name=\"mode\" value=\"open\"></td>\n";
		echo "			</tr>\n";
		echo "			<tr>\n";
		echo "				<td><input type=\"submit\" value=\""._DICT_SHOW_WORD."\" class=\"eden_button\">"; if (AGet($_SESSION,'loginid') != ""){ echo " &nbsp; <a href=\"index.php?action=dict&amp;lang=".AGet($_GET,'lang')."&amp;filter=".AGet($_GET,'filter')."&amp;mode=dict_add_word\">"._DICT_ADD_WORD."</a>"; } echo "<br>\n";
		echo "				<a href=\"http://www.pokerteam.cz/index.php?action=dict\" target=\"_self\">Slovník</a></td>	\n";
		echo "			</tr>\n";
		echo "		</table></form>\n";
		echo "	</div>\n";
		echo "</div>\n";
		echo "<!-- *** SLOVNIK - KONEC *** -->\n";
	}
	echo "</div>\n";
	echo "<!-- ///////////////////////// LEFT - KONEC ///////////////////////// -->";
}
echo "<!-- //////////////////////////////////////////////////////// LEFT - END ////////////////////////////////////////////////// -->";
echo "<div class=\"content_top_articles\">";
if (AGet($_GET,'action') == "article" || AGet($_GET,'action') == "msg"){
	if (AdminCustom(3) == 1){
		echo "<!-- ///////////////////////// ARTICLES ///////////////////////// -->";
		echo "<!-- *** BEST ARTICLES *** -->";
		echo "<div class=\"content_top_articles_col\">";
		echo "	<div class=\"content_top_articles_col_cont\">";
		echo "		<div class=\"content_top_articles_col_text\">";
						for($i=0;$i<5;$i++){
							echo "<div id=\"article_".$i."\" class=\"content_top_articles_article\" "; if($i == 0){echo "style=\"visibility:visible;\"";} echo ">";
							ShowBest($poker_categories['poker_all_clanky'],$i);
							$best_article_id[$i] = $article_id;
							echo "</div>";
						}
		echo "			<div id=\"content_top_articles_button\">";
						for ($i=0;$i<5;$i++){
							echo "<div class=\"content_top_articles_button\" id=\"top_article_button_".$i."\" onclick=\"document.location.href='".$eden_cfg['url']."index.php?action=clanek&amp;lang=".AGet($_GET,'lang')."&amp;filter=".AGet($_GET,'filter')."&amp;id=".$best_article_id[$i]."&amp;page_mode='\" onmouseover=\"RotateBest('".$i."','off')\" onmouseout=\"RotateBest('".$i."','on')\">"; echo $i+1; echo "</div>";
						}
		echo "			<img src=\"images/bod.gif\" width=\"1\" height=\"1\" onload=\"RotateBest('0','on')\" alt=\".\">";
		echo "			</div>";
		echo "		</div>";
		echo "	</div>";
		echo "	<!-- *** BEST ARTICLES - END *** -->";
		echo "</div>";
	}
	echo "<div class=\"content_article_col\">";
	echo "	<!-- *** ARTICLES *** -->";
	echo "	<div id=\"cat11\" style=\"margin-left:0px;width:348px;height:220px;background-color:#dedede;\">";
	echo "		<table width=\"348\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">";
					ArticlesList($poker_categories['poker_all_clanky'],3,10,0);
	echo "		</table>";
	echo "	</div>";
	echo "	<script type=\"text/javascript\">\n";
	echo "	<!--\n";
	echo "	var collapse11=new animatedcollapse(\"cat11\", 300, true);\n";
	echo "	if (animatedcollapse.getCookie(uniquepageid+\"_cat11\") == \"yes\"){\n";
	echo "		intImage['mnu_img_1'] = 2;\n";
	echo "	} else {\n";
	echo "		intImage['mnu_img_1'] = 1;\n";
	echo "	}\n";
	echo "	//-->\n";
	echo "</script>";
	$showtime = formatTime(time(),"YmdHis");
	/* Provereni zda je zadana nejaka TOP ARTICLES */
	$pieces = explode (":", $poker_categories['poker_all_clanky']);
	$num1 = count($pieces);
	/* Nacteni nastaveni poctu zobrazovanych novinek */
	$i=0;
	$categories = FALSE;
	while($num1 > $i){
		$act_category_pieces = $pieces[$i];
		if ($i>0){$divider = ",";} else {$divider = "";}
		$categories .= $divider.(integer)$act_category_pieces;
		$i++;
	}
	$res = mysql_query("SELECT COUNT(*) FROM $db_articles WHERE article_category_id IN ($categories) AND article_public=0 AND article_publish=1 AND article_top_article=1 AND article_lang='".mysql_real_escape_string(AGet($_GET,'lang'))."' AND $showtime BETWEEN article_date_on AND article_date_off LIMIT 10") or die ("<strong>"._ERROR." 1</strong> ".mysql_error());
	$num = mysql_fetch_array($res);
	if ($num[0] > 0){
		$article_headline_first = "<div class=\"content_article_col_header\"><div class=\"content_article_col_title\">"._TOP_ARTICLES."</div><div style=\"position:relative;top:-15px;right:5px;float:right\"><a href=\"javascript:swapImage('mnu_img_1');collapse11.slideit();\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_icon_minus_2.gif\" width=\"12\" height=\"12\" id=\"mnu_img_1\" align=\"middle\" alt=\".\"></a></div><img src=\"".$eden_cfg['url']."images/bod.gif\" width=\"1\" height=\"1\" alt=\".\" onload=\"swapImage('mnu_img_1')\"></div>";
		$article_headline = "<div class=\"content_article_col_header\"><div class=\"content_article_col_title\">"._ARTICLES."</div></div>";
	} else {
		$article_headline_first = FALSE;
		$article_headline = "<div class=\"content_article_col_header\"><div class=\"content_article_col_title\">"._ARTICLES."</div><div style=\"position:relative;top:-15px;right:5px;float:right\"><a href=\"javascript:swapImage('mnu_img_1');collapse11.slideit();\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_icon_minus_2.gif\" width=\"12\" height=\"12\" id=\"mnu_img_1\" align=\"middle\" alt=\".\"></a></div><img src=\"".$eden_cfg['url']."images/bod.gif\" width=\"1\" height=\"1\" alt=\".\" onload=\"swapImage('mnu_img_1')\"></div>";
	}
	echo $article_headline_first;
	ZobrazeniNovFirst($poker_categories['poker_all_clanky'],"cz");
	echo $article_headline;
	ZobrazeniNov("1",$poker_categories['poker_all_clanky']);
	echo "<!-- *** ARTICLES - KONEC *** -->\n";
	echo "<div style=\"height:20px;margin:30px 0px 0px 0px; padding:5px;border: 3px solid #907c64; text-align:center;\">Novinky a zajímosti o <a href=\"http://www.online-poker-zdarma.cz/\">poker</a> texas holdem</div>";
	echo "<br claer=\"all\">";
	echo "</div>\n";
	echo "<!-- ///////////////////////// ARTICLES - KONEC ///////////////////////// -->\n";
   	echo "<!-- ///////////////////////// NEWS ///////////////////////// -->\n";
	echo "<div class=\"content_act_col\">";
	echo "	<div id=\"cat12\" style=\"margin-left:0px;width:251px;height:220px;background-color:#dedede;\">";
	echo "		<table width=\"251\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">";
					NewsList($poker_categories['poker_all_aktuality'],1,10);
	echo "		</table>";
	echo "	</div>\n";
	echo "	<script type=\"text/javascript\">\n";
	echo "		<!--\n";
	echo "		var collapse12=new animatedcollapse(\"cat12\", 300, true);\n";
	echo "		if (animatedcollapse.getCookie(uniquepageid+\"_cat12\") == \"yes\"){\n";
	echo "			intImage['mnu_img_2'] = 2;\n";
	echo "		} else {\n";
	echo "			intImage['mnu_img_2'] = 1;\n";
	echo "		}\n";
	echo "		-->\n";
	echo "	</script>";
	/*
	$random_number = rand (0,1);
	switch ($random_number){
		case 0:
			echo "<div align=\"center\"><a href=\"http://www.pokerteam.cz/\" target=\"_self\"><img src=\"images/251x250_pokerteam_liga.gif\" title=\"PokerStars.com Pokerteam liga\" alt=\"PokerStars.com Pokerteam liga\" width=\"251\" height=\"250\" border=\"0\"></a></div>";
		break;
		default:
			echo "<div align=\"center\"><a href=\"http://www.pokerteam.cz/\" target=\"_self\"><img src=\"images/251x250_pokerteam_liga.gif\" title=\"PokerStars.com Pokerteam liga\" alt=\"PokerStars.com Pokerteam liga\" width=\"251\" height=\"250\" border=\"0\"></a></div>";
	}
	*/
	?>
<script language="javascript" type="text/javascript">
   var p = document.location.protocol;
   if (!p || p == null) p = "";
   var s = (p.toLowerCase().indexOf("http") == 0 ? p : "http:") + "//pokerstars.com/cs/ad/10171288/250x165.js";
   var r = Math.floor(Math.random()*999999)+''+Math.floor(Math.random()*999999);
   var c = document.createElement("script");
   c.type = "text/javascript";
   c.src = s+"?r="+r;
   c.id = ""+r;
   c.async = true;
   var a = document.getElementsByTagName("script");
   var t = a[a.length-1];
   t.parentNode.insertBefore(c, t);
</script>
<noscript><a href="http://pokerstars.com/cs/ad/10171288/250x165fd.gif.click?rq=noscript&vs="><img src="http://pokerstars.com/cs/ad/10171288/250x165fd.gif?rq=noscript&vs=" width="250" height="165" alt="" border="0"/></a></noscript>
<?php
	echo "<!-- *** NEWS *** -->\n";
	echo "<div class=\"content_act_col_header\">\n";
	echo "	<div class=\"content_act_col_title\">"._NEWS."</div><div style=\"position:relative;top:-15px;right:5px;float:right\"><a href=\"javascript:swapImage('mnu_img_2');collapse12.slideit();\"><img src=\"".$eden_cfg['url_skins'].$eden_project_skin."/images/sys_icon_minus_2.gif\" width=\"12\" height=\"12\" id=\"mnu_img_2\" align=\"middle\" alt=\".\"></a></div><img src=\"".$eden_cfg['url']."images/bod.gif\" width=\"1\" height=\"1\" alt=\".\" onload=\"swapImage('mnu_img_2')\">\n";
	echo "</div>\n";
	News($poker_categories['poker_all_aktuality']);
	echo "<!-- *** NEWS - KONEC *** -->\n";
	echo "</div>\n";
	echo "<!-- ///////////////////////// NEWS - KONEC ///////////////////////// -->\n";
	echo "</div>\n";
} elseif (AGet($_GET,'action') == "search"){
	echo "<div class=\"content_template_col\">\n";
	echo "	<div class=\"content_template_col_header\"><div id=\"content_forum_col_title\">"._SEARCH."</div></div>\n";
	echo "	<div id=\"content_template_col_cont\">\n";
	echo "			<div class=\"content_template_col_text\">\n";
						Search(30,0,1);
	echo "			<br>\n";
	echo "			</div>\n";
	echo "		</div>\n";
				SearchRes();
	echo "	</div>";
} elseif (AGet($_GET,'action') == "forum"){
	echo "<div id=\"content_forum_col\">\n";
	echo "	<div id=\"content_forum_col_header\">\n";
	echo "		<div id=\"content_forum_col_title\">"._FORUM."</div>\n";
	echo "	</div>\n";
	echo "	<div id=\"content_forum_col_cont\">\n";
	echo "		<div id=\"content_forum_col_text\">";
					/* Pokud je vypnut pristup pro anonymy, tak se jim zobrazi jen login, nebo registrace */
					if (AGet($_GET,'faction') == ""){ForumShowMain();}
					if (AGet($_GET,'faction') == "tema"){ForumShowMain();}
					if (AGet($_GET,'faction') == "topics"){ForumShowTopics($_GET['id1']);}
					if (AGet($_GET,'faction') == "open"){ForumShowMain();}
					if (AGet($_GET,'faction') == "close"){ForumShowMain();}
					if (AGet($_GET,'faction') == "posts"){ForumPosts();}
					if ($_SESSION['u_status'] == "user" || $_SESSION['u_status'] == "admin"){
						if (AGet($_GET,'faction') == "edit_f"){ForumAddTopic();}
						if (AGet($_GET,'faction') == "del_f"){ForumDelForum();}
						if (AGet($_GET,'faction') == "add_f"){ForumAddTopic();}
						if (AGet($_GET,'faction') == "add_t"){ForumAddTopic();}
						if (AGet($_GET,'faction') == "quote"){ForumPosts();}
						if (AGet($_GET,'faction') == "post_preview"){ForumPosts();}
						if (AGet($_GET,'faction') == "edit_post"){ForumPosts();}
						if (AGet($_GET,'faction') == "del_post"){ForumPosts();}
						if (AGet($_GET,'faction') == "pm"){ForumPM();}
						if (AGet($_GET,'faction') == "pm_preview"){ForumPM();}
						if (AGet($_GET,'faction') == "reportit"){ForumReportIt();}
						if (AGet($_GET,'faction') == "otherusers"){ForumOtherUsers($_GET['sp_width'],$_GET['sp_mode']);}
						if (AGet($_GET,'faction') == "friends"){ForumFriends($_GET['sp_width'],$_GET['sp_mode']);}
						if (AGet($_GET,'faction') == "setup") { ForumUserSetup(); }
					}
	echo "		</div>";
	echo "	</div>";
} elseif ( AGet($_GET,'action') == "league" || AGet($_GET,'action') == "league_about" || AGet($_GET,'action') == "league_news" || AGet($_GET,'action') == "league_rules" || AGet($_GET,'action') == "league_about" || AGet($_GET,'action') == "league_table" || AGet($_GET,'action') == "league_results_players" || AGet($_GET,'action') == "league_results_rounds" || AGet($_GET,'action') == "league_harmonogram" || 
        AGet($_GET,'action') == "player" || AGet($_GET,'action') == "league_results_rounds" || AGet($_GET,'action') == "league_results_players" || AGet($_GET,'action') == "league_results_teams" ||
		AGet($_GET,'action') == "league_team" || AGet($_GET,'action') == "league_team_reg" || AGet($_GET,'action') == "team_list" ||
		AGet($_GET,'action') == "svet_pokeru" || AGet($_GET,'action') == "profily_hracu" || AGet($_GET,'action') == "zajimavosti" || AGet($_GET,'action') == "online_poker" ||
		AGet($_GET,'action') == "kdehrat" || AGet($_GET,'action') == "kdehrat_online" || AGet($_GET,'action') == "kdehrat_kluby" || AGet($_GET,'action') == "kdehrat_recenze" ||
		AGet($_GET,'action') == "strategie" || AGet($_GET,'action') == "strategie_cash_games" || AGet($_GET,'action') == "strategie_turnaje" || AGet($_GET,'action') == "strategie_obecne" ||
		AGet($_GET,'action') == "zaciname" ||
		AGet($_GET,'action') == "turnaje" || AGet($_GET,'action') == "turnaje_ept" || AGet($_GET,'action') == "turnaje_wsop" || AGet($_GET,'action') == "turnaje_wpt" || AGet($_GET,'action') == "turnaje_ostatni" ||
		AGet($_GET,'action') == "browse_channel" || AGet($_GET,'action') == "provozovatel"){
		switch (AGet($_GET,'action')){
			case "clanek":
				$temp_title = _ARTICLE;
			break;
			case "komentar":
				if ($_GET['modul'] == "news"){$temp_title = _TITLE_COMMENTS_ACT;} elseif ($_GET['modul'] == "poll"){$temp_title = _TITLE_COMMENTS_POLL;}else{$temp_title = _TITLE_COMMENTS_ARTICLE;}
			break;
			case "secretgb":
				$temp_title = "Secret Guestbook";
			break;
			case "komentar":
				if ($_GET['modul'] == "articles"){
					$temp_title = _TITLE_COMMENTS_ARTICLE;
				}elseif ($_GET['modul'] == "news"){
					$temp_title = _TITLE_COMMENTS_ACT;
				}
			break;
			case "browse_channel":
				$res_article_channel = mysql_query("SELECT article_channel_title FROM $db_articles_channel WHERE article_channel_id=".(float)$_GET['id']) or die ("<strong>"._ERROR."</strong> ".mysql_error());
				$ar_article_channel = mysql_fetch_array($res_article_channel);
				$temp_title = _TITLE_BROWSE_CHANNEL.' - '.$ar_article_channel['article_channel_title'];
			break;
			case "league":
				$temp_title = "Pokerteam liga";
			break;
			case "league_about":
				$temp_title = "Pokerteam liga - O lize";
			break;
			case "league_harmonogram":
				$temp_title = "Pokerteam liga - Harmonogram";
			break;
			case "league_news":
				$temp_title = "Pokerteam liga - Novinky";
			break;
			case "league_results_players":
				$temp_title = "Pokerteam liga - Pořadí hráčů";
			break;
			case "league_results_rounds":
				$temp_title = "Pokerteam liga - Výsledky ligových kol";
			break;
			case "league_results_teams":
				$temp_title = "Pokerteam liga - Pořadí týmu";
			break;
			case "league_rules":
				$temp_title = "Pokerteam liga - Pravidla";
			break;
			case "league_table":
				$temp_title = "Pokerteam liga - Ligová tabulka";
			break;
			case "player":
		   		switch (AGet($_GET,'mode')){
					case "player_awards":
						$temp_title = "Získaná ocenení";
					break;
				   	default:
						$temp_title = "Hráčský účet";
				}
			break;
			case "league_team":
				switch (AGet($_GET,'mode')){
					case "league_list":
						if ($_GET['lid'] != ""){
							$temp_title = "Dataily ligy";
						} else {
							$temp_title = "Seznam aktivních lig";
						}
					break;
					case "team_awards":
						$temp_title = "Získaná ocenění";
					break;
					case "team_draft":
						$temp_title = "Draft hráčů";
					break;
					case "team_list":
						$temp_title = "Seznam všech týmu";
					break;
					case "team_log_player":
						$temp_title = "Historie hráče";
					break;
					case "team_log_team":
						$temp_title = "Historie týmu";
					break;
					case "team_home":
						$temp_title = "Detaily týmu";
					break;
					case "team_league_reg":
						$temp_title = "Registrace do týmových lig";
					break;
					case "player_home":
						$temp_title = "Detaily hráče";
					break;
					case "player_league_reg":
						$temp_title = "Registrace do lig jednotlivcu";
					break;
					case "player_league_leave":
						$temp_title = "Odhlášení z ligy";
					break;
					default:
						$temp_title = "Tým";
				}
			break;
			case "team_list":
				$temp_title = "Seznam týmu";
			break;
			case "league_team_reg":
				$temp_title = "Registrace týmu";
			break;
			case "kdehrat":
				$temp_title = "Kde hrát";
			break;
			case "kdehrat_kluby":
				$temp_title = "Kde hrát - Kluby";
			break;
			case "kdehrat_online":
				$temp_title = "Kde hrát - Online";
			break;
			/*
            case "kdehrat_recenze":
				$temp_title = "Kde hrát - Recenze";
			break;
            */
            case "online_poker":
				$temp_title = "Online poker";
			break;
			case "strategie":
				$temp_title = "Strategie";
			break;
			case "strategie_obecne":
				$temp_title = "Strategie - Obecné";
			break;
			case "strategie_turnaje":
				$temp_title = "Strategie - Turnaje";
			break;
			case "strategie_cash_games":
				$temp_title = "Strategie - Cash Games";
			break;
			case "svet_pokeru":
				$temp_title = "Svět pokeru";
			break;
            case "online_poker":
				$temp_title = "Online poker";
			break;
			case "profily_hracu":
				$temp_title = "Profily hráčů";
			break;
			case "svet_pokeru_reportaze":
				$temp_title = "Svět pokeru - Reportáže z turnaju";
			break;
			case "zajimavosti":
				$temp_title = "Pokerové zajímavosti";
			break;
			case "turnaje":
				$temp_title = "Turnaje";
			break;
			case "turnaje_ept":
				$temp_title = "Turnaje - European Poker Tour";
			break;
			case "turnaje_wsop":
				$temp_title = "Turnaje - World Series of Poker";
			break;
			case "turnaje_wpt":
				$temp_title = "Turnaje - World Poker Tour";
			break;
			case "turnaje_ostatni":
				$temp_title = "Turnaje - Ostatní";
			break;
			case "zaciname":
				$temp_title = "Začínáme s pokerem";
			break;
			case "provozovatel":
				$temp_title = "Provozovatel";
			break;
		}
		echo "<div class=\"content_article_home_col\">";
		echo "<div class=\"content_article_home_header\">";
		echo "	<div class=\"content_article_home_title\" style=\"text-align:center;\">".$temp_title."</div>";
		echo "</div>";
		$denied = "<div class=\"eden_league\">\nPro zobrazení této funkce musíte být přihlášeni.\n</div>\n";
		switch (AGet($_GET,'action')){
			case "player":
				switch (AGet($_GET,'mode')){
					/*******************************************************
					*	PLAYER ACCOUNT
					*******************************************************/
					case "player_acc":
					   LeaguePlayerAcc($_GET['id'],578,583);
		   			break;
					/*******************************************************
					*	PLAYER ADMIN
					*******************************************************/
					case "player_admin":
						$res_player = mysql_query("
						SELECT a.admin_nick, a.admin_reg_date, a.admin_team_own_id, lp.league_player_id, lp.league_player_team_id, lp.league_player_position_captain, lp.league_player_position_assistant, 
						lp.league_player_position_player, lt.league_team_id, lt.league_team_name, ac.admin_contact_icq, ac.admin_contact_birth_day, c.country_name 
						FROM $db_league_players AS lp 
						JOIN $db_admin AS a ON a.admin_id=lp.league_player_admin_id 
						JOIN $db_admin_contact AS ac ON ac.aid=a.admin_id 
						JOIN $db_country AS c ON c.country_id=ac.admin_contact_country 
						LEFT JOIN $db_league_teams AS lt ON lt.league_team_id=lp.league_player_team_id 
						WHERE league_player_id=".(integer)AGet($_GET,'pid')) or die ("<strong>"._ERROR." 1</strong> ".mysql_error());
						$ar_player = mysql_fetch_array($res_player);
						$backslash = FALSE;
						$team_status = FALSE;
						if ($ar_player['admin_team_own_id'] == $ar_player['league_team_id']){$team_status = "Vlastník";$backslash = " / ";}
						if ($ar_player['league_player_position_captain'] == 1){$team_status .= $backslash."Kapitán";$backslash = " / ";}
						if ($ar_player['league_player_position_assistant'] == 1){$team_status .= $backslash."Asistent";$backslash = " / ";}
						if ($ar_player['league_player_position_player'] == 1){$team_status .= $backslash."Hrác";}
						echo "<div class=\"eden_league\">";
						echo "	<br>";
						echo "	<h2>Detaily účtu</h2>";
						echo "	<br>";
						echo "	<table cellpadding=\"3\" cellspacing=\"0\" border=\"1\">\n";
						echo "		<tr>\n";
						echo "			<td>Současná personalita</td>\n";
						echo "			<td><a href=\"".$eden_cfg['url']."index.php?action=player&mode=player_acc&amp;ltid=".$ar_player['league_player_team_id']."&amp;pid=".$ar_player['league_player_id']."&amp;lang=".AGet($_GET,'lang')."&amp;filter=".AGet($_GET,'filter')."\">".$ar_player['admin_nick']."</a> (PID: ".$ar_player['league_player_id'].")</td>\n";
						echo "		</tr>\n";
						echo "		<tr>\n";
						echo "			<td>Současný team</td>\n";
						echo "			<td><a href=\"".$eden_cfg['url']."index.php?action=team&mode=home&amp;ltid=".$ar_player['league_player_team_id']."&amp;pid=".$ar_player['league_player_id']."&amp;lang=".AGet($_GET,'lang')."&amp;filter=".AGet($_GET,'filter')."\">".$ar_player['league_team_name']."</a> (TID: ".$ar_player['league_player_team_id'].")</td>\n";
						echo "		</tr>\n";
						echo "		<tr>\n";
						echo "			<td>Teamový status</td>\n";
						echo "			<td>".$team_status."</td>\n";
						echo "		</tr>\n";
						echo "	</table>\n";
						echo "	<!--<br>";
						echo "	<h2>Muj úcet</h2>";
						echo "	<br>";
						echo "	<h3>Herní nastavení</h3>-->";
						echo "</div>";
					break;
					/*******************************************************
					*	PLAYER AWARDS
					*******************************************************/
					case "player_awards":
						echo LeagueAwards(1,$_GET['id'],0);
		   			break;
					default:
						echo "";
				}
			break;
			case "browse_channel":
				ShowChannel($_GET['id'],1,0);
			break;
			case "":
				ZobrazeniNov(3,$poker_categories['poker_all_clanky']);
			break;
			case "online_poker":
				ZobrazeniNov(3,15);
			break;
	        case "profily_hracu":
				ZobrazeniNov(3,18);
			break;
			case "zajimavosti":
				ZobrazeniNov(3,34);
			break;
			case "league":
				ZobrazeniNov(3,$poker_categories['poker_liga']);
			break;
			case "league_about":
				ZobrazeniCl(1,3350);
			break;
			case "league_harmonogram":
				
			case "league_news":
				ZobrazeniNov(3,2);
			break;
			case "league_rules":
				ZobrazeniCl(1,3352);
			break;
			case "provozovatel":
				ZobrazeniCl(1,3747);
			break;
			case "league_results_players":
				if (empty($_GET['lid'])){$_GET['lid'] = LeagueGetLastActiveLid(1);}
				if (empty($_GET['sid'])){$_GET['sid'] = LeagueGetLastActiveSid($_GET['lid']);}
				LeagueSeasonPlayersResults();
			break;
			case "league_table":
			
			break;
			case "league_results_rounds":
				if (empty($_GET['lid'])){$_GET['lid'] = LeagueGetLastActiveLid(1);}
				if (empty($_GET['sid'])){$_GET['sid'] = LeagueGetLastActiveSid($_GET['lid']);}
				LeagueRoundsResults();
			break;
			case "league_results_teams":
				if (empty($_GET['lid'])){$_GET['lid'] = LeagueGetLastActiveLid(1);}
				if (empty($_GET['sid'])){$_GET['sid'] = LeagueGetLastActiveSid($_GET['lid']);}
				LeagueSeasonTeamsResults($_GET['sid'],1);
			break;
			case "league_team":
				switch (AGet($_GET,'mode')){
					case "team_awards":
						if (AGet($_SESSION,'loginid') != ""){
							echo LeagueAwards(2,0,(integer)$_GET['ltid']);
						} else {
							echo $denied;
						}
					break;
					case "team_game_add":
						if (AGet($_SESSION,'loginid') != ""){
							LeagueTeamGameAdd($_GET['ltid']);
						} else {
							echo $denied;
						}
					break;
					case "team_log_player":
						if (AGet($_SESSION,'loginid') != ""){
					   		LeagueLog("player",(integer)AGet($_GET,'pid'));
						} else {
							echo $denied;
						}
					break;
					case "team_log_team":
						if (AGet($_SESSION,'loginid') != ""){
							LeagueLog("team",(integer)$_GET['ltid']);
						} else {
							echo $denied;
						}
					break;
					case "team_player_add":
						if (AGet($_SESSION,'loginid') != ""){
							LeaguePlayerAdd();
						} else {
							echo $denied;
						}
					break;
					case "team_player_confirm":
						if (AGet($_SESSION,'loginid') != ""){
							TeamPlayerConfirm();
						} else {
							echo $denied;
						}
					break;
					case "team_player_check":
						if (AGet($_SESSION,'loginid') != ""){
							LeaguePlayerAdd();
						} else {
							echo $denied;
						}
					break;
					case "team_player_join":
						if (AGet($_SESSION,'loginid') != ""){
							TeamPlayerJoin();
						} else {
							echo $denied;
						}
					break;
					case "team_player_join_confirm":
						if (AGet($_SESSION,'loginid') != ""){
							TeamPlayerJoin();
						} else {
							echo $denied;
						}
					break;
					case "team_player_kick":
						if (AGet($_SESSION,'loginid') != ""){
					   		TeamPlayerKick(AGet($_GET,'pid'));
						} else {
							echo $denied;
						}
					break;
					case "team_player_leave":
						if (AGet($_SESSION,'loginid') != ""){
					   		TeamPlayerLeave();
						} else {
							echo $denied;
						}
					break;
					case "team_player_make_a":
						if (AGet($_SESSION,'loginid') != ""){
							TeamPlayerMake("A",AGet($_GET,'pid'));
						} else {
							echo $denied;
						}
					break;
					case "team_player_make_c":
						if (AGet($_SESSION,'loginid') != ""){
					  		TeamPlayerMake("C",AGet($_GET,'pid'));
						} else {
							echo $denied;
						}
					break;
					case "team_player_make_o":
						if (AGet($_SESSION,'loginid') != ""){
							TeamPlayerMake("O",AGet($_GET,'pid'));
						} else {
							echo $denied;
						}
					break;
					case "team_player_make_p":
						if (AGet($_SESSION,'loginid') != ""){
							TeamPlayerMake("P",AGet($_GET,'pid'));
						} else {
							echo $denied;
						}
					break;
					case "team_edit":
						if (AGet($_SESSION,'loginid') != ""){
							LeagueTeam("edit",1,2);
						} else {
							echo $denied;
						}
					break;
					case "team_draft":
						if (AGet($_SESSION,'loginid') != ""){
							LeagueTeamDraft();
						} else {
							echo $denied;
						}
					break;
					case "team_guids":
						if (AGet($_SESSION,'loginid') != ""){
					   		LeagueTeamGuids((integer)$_GET['ltid']);
					   	} else {
							echo $denied;
						}
					break;
					case "team_hibernate_check":
						if (AGet($_SESSION,'loginid') != ""){
					   		LeagueTeamHibernateCheck((integer)$_GET['ltid']);
					   	} else {
							echo $denied;
						}
					break;
					case "team_league_leave":
						if (AGet($_SESSION,'loginid') != ""){
					   		LeagueTeamLeagueLeave();
						} else {
							echo $denied;
						}
					break;
					case "team_league_reg":
						if (AGet($_SESSION,'loginid') != ""){
							LeagueTeamLeagueReg(AGet($_GET,'mode'));
						} else {
							echo $denied;
						}
					break;
					case "team_league_reg_confirm":
						if (AGet($_SESSION,'loginid') != ""){
					   		LeagueTeamLeagueReg(AGet($_GET,'mode'));
						} else {
							echo $denied;
						}
					break;
					case "team_team_confirm":
						if (AGet($_SESSION,'loginid') != ""){
					   		TeamTeamConfirm();
						} else {
							echo $denied;
						}
					break;
					case "league_list":
						LeagueLeagueList();
					break;
					case "team_home":
						LeagueTeamHome((integer)$_GET['ltid']);
					break;
					case "team_list":
						LeagueTeamList();
					break;
					case "player_league_reg":
						if (AGet($_SESSION,'loginid') != ""){
							LeagueTeamLeagueReg(AGet($_GET,'mode'));
						} else {
							echo $denied;
						}
					break;
					case "player_league_reg_confirm":
						if (AGet($_SESSION,'loginid') != ""){
					   		LeagueTeamLeagueReg(AGet($_GET,'mode'));
						} else {
							echo $denied;
						}
					break;
					case "player_league_leave":
						if (AGet($_SESSION,'loginid') != ""){
					   		LeagueTeamLeagueLeave();
						} else {
							echo $denied;
						}
					break;
					case "player_home":
						LeaguePlayerHome((integer)$_GET['lpid']);
					break;
				}
				break;
				default:
					// Nic :)
		}
		
		if (AGet($_GET,'action') == "league_team_reg" && AGet($_SESSION,'loginid') != ""){
			ZobrazeniCl(1,3351);
			/* Zobrazime formular pro registraci/editaci teamu */
			LeagueTeam("reg","".(integer)$_GET['gid']."","".(integer)$_GET['lid']."");
		} elseif (AGet($_GET,'action') == "league_team_reg" && AGet($_SESSION,'loginid') == "") {
			echo $denied;
		}
		
		if (AGet($_GET,'action') == "kdehrat"){ZobrazeniNov("3",$poker_categories['poker_kdehrat']);}
		if (AGet($_GET,'action') == "kdehrat_online"){ZobrazeniNov("3","14");}
		if (AGet($_GET,'action') == "kdehrat_kluby"){ZobrazeniNov("3","16");}
		
		if (AGet($_GET,'action') == "strategie"){ZobrazeniNov("3",$poker_categories['poker_strategie']);}
		if (AGet($_GET,'action') == "strategie_cash_games"){ZobrazeniNov("3","10");}
		if (AGet($_GET,'action') == "strategie_turnaje"){ZobrazeniNov("3","11");}
		if (AGet($_GET,'action') == "strategie_obecne"){ZobrazeniNov("3","12");}
		
		if (AGet($_GET,'action') == "svet_pokeru"){ZobrazeniNov("3",$poker_categories['poker_svet_pokeru']);}
		if (AGet($_GET,'action') == "svet_pokeru_reportaze"){ZobrazeniNov("3","20");}
		
		if (AGet($_GET,'action') == "turnaje"){ZobrazeniNov("3",$poker_categories['poker_turnaje']);}
		if (AGet($_GET,'action') == "turnaje_ept"){ZobrazeniNov("3","37");}
		if (AGet($_GET,'action') == "turnaje_wsop"){ZobrazeniNov("3","38");}
		if (AGet($_GET,'action') == "turnaje_wpt"){ZobrazeniNov("3","39");}
		if (AGet($_GET,'action') == "turnaje_wpt"){ZobrazeniNov("3","40");}
		
		if (AGet($_GET,'action') == "zaciname"){ZobrazeniNov("3",$poker_categories['poker_zaciname']);}
		echo '</div></div>';
	} else {
		echo "<div class=\"content_template_col\">\n";
	 		if(AGet($_GET,'action') != "msg"){
				switch (AGet($_GET,'action')){
					case "clanek":
						$temp_title = "Článek";
					break;
					case "komentar":
						$temp_title = "Komentář";
					break;
					case "liga_faq":
						$temp_title = "Pokerteam Liga - FAQ";
					break;
					case "liga_pravidla":
						$temp_title = "Pokerteam Liga - Pravidla";
					break;
					case "liga_zpravy":
						$temp_title = "Pokerteam Liga - Zprávy z ligy";
					break;
					case "user_details":
						$temp_title = _TITLE_USER_PROFIL;
					break;
					case "user_edit":
						switch (AGet($_GET,'mode')){
							case "team_player_confirm":
								$temp_title = _USER_EDIT." - Vstup do teamu";
							break;
							default:
								$temp_title = _USER_EDIT;
						}
					break;
					case "whoisonline":
						$temp_title = _WHO_IS_ONLINE;
					break;
				}
			echo "	<div class=\"content_template_col_header\">\n";
			echo "		<div class=\"content_template_col_title\">".$temp_title."</div>\n";
			echo "	</div>\n";
			echo "	<div class=\"content_template_col_text\">\n";
				/*******************************************************
				*
				*	AKTUALITA
				*
				*******************************************************/
				if (AGet($_GET,'action') == "aktualita"){Aktualita($_GET['id']);}
				/*******************************************************
				*
				*	ARCHIV & KAL ARCHIV
				*
				*******************************************************/
				if (AGet($_GET,'action') == "archiv" || AGet($_GET,'action') == "kal_archiv"){
					ArchivKalendar();
					if (AGet($_GET,'action') == "archiv"){Archiv();}
				}
				/*******************************************************
				*
				*	BROWSE BLOGS
				*
				*******************************************************/
				if (AGet($_GET,'action') == "browse_blogs"){
					$showtime = formatTime(time());
					$poker_blogs = "article_category_id=46 OR article_category_id=337";
					$res = mysql_query("SELECT article_id, article_author_id, article_date_on, article_date_off, article_date, article_headline FROM $db_articles WHERE (".$poker_blogs.") AND article_public=0 AND article_publish=1 AND article_parent_id=0 AND $showtime BETWEEN article_date_on AND article_date_off ORDER BY article_author_id DESC") or die ("<strong>"._ERROR." 1</strong> ".mysql_error());
					echo "<div style=\"margin:0px 0px 5px 3px;\">";
					echo "<table width=\"588\" border=\"0\" cellspacing=\"2\" cellpadding=\"5\">";
					echo "	<tr id=\"blog_title\">";
					echo "		<td width=\"60\" valign=\"top\">"._DATE."</td>";
					echo "		<td valign=\"top\">"._AUTHOR."</td>";
					echo "		<td valign=\"top\">"._TITLE."</td>";
					echo "		<td width=\"40\">"._COMMENTS."</td>";
					echo "	</tr>";
					$cislo = 0;
					while ($ar = mysql_fetch_array($res)){
						$vysledek2 = mysql_query("SELECT COUNT(*) FROM $db_comments WHERE comments_pid=".(float)$ar['article_id']." AND comments_modul='article'") or die ("<strong>"._ERROR." 2</strong> ".mysql_error()); // Nastaveni ukazatele na komentare v danem clanku
						$res_adm = mysql_query("SELECT admin_nick FROM $db_admin WHERE admin_id='".(float)$ar['article_author_id']."'") or die ("<strong>"._ERROR." 3</strong> ".mysql_error()); // Nastaveni ukazatele na komentare v danem clanku
						$ar_adm = mysql_fetch_array($res_adm);
						$num2 = mysql_fetch_array($vysledek2); // Zjisteni poctu prispevku k danemu clanku
						$datum = FormatTimestamp($ar['article_date']);
						$hlavicka = TreatText($ar['article_headline'],"50");
						echo "<tr "; if ($cislo % 2 == 0){echo "class=\"suda\"";} else {echo "class=\"licha\"";} /* stridani barev prispevku podle sudeho nebo licheho radku */ echo ">\n";
						echo "	<td width=\"60\" valign=\"top\">".FormatTimestamp($ar['article_date_on'],"d.m.Y")."</td>\n";
						echo "	<td valign=\"top\"><strong>".$ar_adm['admin_nick']."</strong></td>\n";
						echo "	<td valign=\"top\"><a href=\"index.php?action=clanek&amp;filter=".AGet($_GET,'filter')."&amp;id=".$ar['article_id']."\">".$hlavicka."</a></td>\n";
						echo "	<td width=\"40\" align=\"right\"><a href=\"index.php?action=komentar&amp;filter=".AGet($_GET,'filter')."&amp;id=".$ar['article_id']."&amp;modul=article\" target=\"_self\">".$num2[0]."</a></td>\n";
						echo "</tr>";
						$cislo++;
					}
					echo "</table>";
					echo "</div>";
				}
				/*******************************************************
				*
				*	BROWSE ARTICLES
				*
				*******************************************************/
				if (AGet($_GET,'action') == "browse_articles"){
					echo "<div style=\"margin:0px 0px 5px 3px;\">";
					BrowseArticles();
					echo "</div>";
				}
				/*******************************************************
				*
				*	BROWSE TUTORIALS
				*
				*******************************************************/
				if (AGet($_GET,'action') == "browse_articles_all"){
					echo "<div style=\"margin:0px 0px 5px 3px;\">";
					echo "<table width=\"588\" border=\"0\" cellspacing=\"2\" cellpadding=\"5\">";
					echo "	<tr id=\"blog_title\">";
					echo "		<td valign=\"top\"> </td>";
					echo "		<td width=\"60\" valign=\"top\">"._DATE."</td>";
					echo "		<td valign=\"top\">"._TITLE."</td>";
					echo "		<td width=\"40\">"._COMMENTS."</td>";
					echo "	</tr>";
						ArticlesList($poker_categories['cz_articles'],1,80,1);
					echo "</table>";
					echo "</div>";
				}
				/*******************************************************
				*
				*	BROWSE TUTORIALS
				*
				*******************************************************/
				if (AGet($_GET,'action') == "browse_tutorials"){
					echo "<div style=\"margin:0px 0px 5px 3px;\">";
					echo "<table width=\"588\" border=\"0\" cellspacing=\"2\" cellpadding=\"5\">";
					echo "	<tr id=\"blog_title\">";
					echo "		<td valign=\"top\"> </td>";
					echo "		<td width=\"60\" valign=\"top\">"._DATE."</td>";
					echo "		<td valign=\"top\">"._TITLE."</td>";
					echo "		<td width=\"40\">"._COMMENTS."</td>";
					echo "	</tr>";
						ArticlesList($poker_categories['cz_tutorials'],1,80,1);
					echo "</table>";
					echo "</div>";
				}
				/*******************************************************
				*
				*	CLANEK
				*
				*******************************************************/
				if (AGet($_GET,'action') == "clanek"){
					Clanek($_GET['id'],$_GET['par']);
				}
				/*******************************************************
				*
				*	DICTIONARY
				*
				*******************************************************/
				if (AGet($_GET,'action') == "dict"){
					Dictionary();
				}
				/*******************************************************
				*
				*	FORGOTTEN PASS
				*
				*******************************************************/
				if (AGet($_GET,'action') == "forgotten_pass"){ForgottenPass();}
				/*******************************************************
				*
				*	KOMENTAR
				*
				*******************************************************/
				if (AGet($_GET,'action') == "komentar"){Comments($_GET['id'],$_GET['modul'],120,584,400,584,584);}
				/*******************************************************
				*
				*	TEAMS LIST
				*
				*******************************************************/
				if (AGet($_GET,'action') == "teams_list"){
					echo "<table width=\"600\" border=\"0\" cellspacing=\"2\" cellpadding=\"1\"><p><strong>"._ADD_LINK_HELP."</strong></p>";
					AddLink(16,1);
					echo "	<tr>";
					echo "		<td colspan=\"2\">"; OdkazyKat(343,1); echo "</td>";
					echo "	</tr>";
					echo "</table>";
 				}
				/*******************************************************
				*
				*	PODCASTS
				*
				*******************************************************/
				if (AGet($_GET,'action') == "podcasts"){
					/* Pokud neni $_GET['id'] definovano zobrazi se vsechny povolene kanaly - jinak se zobrazi jen dany kanal */
					if ($_GET['id'] == "" || $_GET['id'] == 0){$id = 0;}else{$id = $_GET['id'];}
					ShowPodcasts($id);
				}
				/*******************************************************
				*
				*	POLLS - OLD
				*
				*******************************************************/
				if (AGet($_GET,'action') == "oldpolls"){
					echo "<div style=\"margin:0px 0px 5px 3px;\">";
					$older_poll = new OlderPoll;
					$older_poll->poll_table_width = 588;
					$older_poll->poll_column_height = 10;
					$older_poll->poll_l_width = 500;
					$older_poll->poll_r_width = 40;
					$older_poll->poll_q_for = 2;
					$older_poll->poll_hits = 50;
					$older_poll->OlderPolls();
					echo "</div>";
				}
				/*******************************************************
				*
				*	PRESS
				*
				*******************************************************/
				if (AGet($_GET,'action') == "press"){
					Zobrazeni("87");
				}
				/*******************************************************
				*
				*	REGISTRACE
				*
				*******************************************************/
				if (AGet($_GET,'action') == "reg"){UserEdit("reg");}
				/*******************************************************
				*
				*	SECRET ARTICLES
				*
				*******************************************************/
				if (AGet($_GET,'action') == "secret_articles" && $_SESSION['u_status'] == "admin"){
					$res = mysql_query("SELECT admin_priv FROM $db_admin WHERE admin_id=".(integer)AGet($_SESSION,'loginid')) or die ("<strong>"._ERROR."2</strong>".mysql_error());
					$ar = mysql_fetch_array($res);
					$res2 = mysql_query("SELECT groups_secret_guestbook_look FROM $db_groups WHERE groups_id=".(float)$ar['admin_priv']) or die ("<strong>"._ERROR."2</strong>".mysql_error());
					$ar2 = mysql_fetch_array($res2);
					if ($ar2['groups_secret_guestbook_look'] == 1){
						echo "<div style=\"margin:0px 0px 5px 3px;\">";
						echo "<table width=\"588\" border=\"0\" cellspacing=\"2\" cellpadding=\"5\">";
						echo "<tr id=\"blog_title\">";
						echo "	<td width=\"60\" valign=\"top\">"._DATE."</td>";
						echo "	<td valign=\"top\">"._AUTHOR."</td>";
						echo "	<td valign=\"top\">"._TITLE."</td>";
						echo "	<td width=\"40\">"._COMMENTS."</td>";
						echo "</tr>";
						$showtime = formatTime(time());
						$res = mysql_query("SELECT article_id, article_headline, article_date, article_date_on, article_author_id FROM $db_articles WHERE article_category_id=175 AND article_public=0 AND article_publish=1 AND $showtime BETWEEN article_date_on AND article_date_off ORDER BY article_date_on DESC") or die ("<strong>"._ERROR."2</strong>".mysql_error());
						$cislo = 0;
						while ($ar = mysql_fetch_array($res)){
							$vysledek2 = mysql_query("SELECT COUNT(*) FROM $db_comments WHERE comments_pid=".(float)$ar['article_id']." AND comments_modul='article'") or die ("<strong>"._ERROR."2</strong>".mysql_error()); // Nastaveni ukazatele na komentare v danem clanku
							$num2 = mysql_fetch_array($vysledek2); // Zjisteni poctu prispevku k danemu clanku
							$res_adm = mysql_query("SELECT admin_nick FROM $db_admin WHERE admin_id=".(float)$ar['article_author_id']) or die ("<strong>"._ERROR." 3</strong> ".mysql_error()); // Nastaveni ukazatele na komentare v danem clanku
							$ar_adm = mysql_fetch_array($res_adm);
							$datum = FormatTimestamp($ar['article_date']);
							$hlavicka = TreatText($ar['article_headline'],"50");
							echo "<tr "; if ($cislo % 2 == 0){echo "class=\"suda\"";} else {echo "class=\"licha\"";} /* stridani barev prispevku podle sudeho nebo licheho radku */ echo ">\n";
							echo "	<td width=\"60\">".FormatTimestamp($ar['article_date_on'],"d.m.Y")."</td>\n";
							echo "	<td valign=\"top\"><strong>".$ar_adm['admin_nick']."</strong></td>\n";
							echo "	<td><a href=\"index.php?action=clanek&amp;filter=".AGet($_GET,'filter')."&amp;id=".$ar['article_id']."\">".$hlavicka."</a></td>\n";
							echo "	<td width=\"40\" align=\"right\"><a href=\"index.php?action=komentar&amp;filter=".AGet($_GET,'filter')."&amp;id=".$ar['article_id']."&amp;modul=article\" target=\"_self\">".$num2[0]."</a></td>\n";
							echo "</tr>";
							$cislo++;
						}
						echo "</table>";
						echo "</div>";
					}
				}
				/*******************************************************
				*
				*	TEAM
				*
				*******************************************************/
				if (AGet($_GET,'action') == "team"){
					$vysledek = mysql_query("SELECT admin_category_id, admin_category_topicimage, admin_category_topictext FROM $db_admin_category WHERE admin_category_shows=1 ORDER BY admin_category_topictext") or die ("<strong>"._ERROR." </strong> ".mysql_error());
					while ($ar = mysql_fetch_array($vysledek)){
						echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" id=\"eden_atc_01\">\n";
						echo "<tr>\n";
						echo "	<td colspan=\"5\" id=\"eden_atc_07\"><br><img src=\"".$url_category.$ar['admin_category_topicimage']."\" alt=\".\" width=\"16\" height=\"16\" border=\"0\">&nbsp;"; $category_name = explode ("]", $ar['admin_category_topictext']); if ($category_name[1] != ""){echo $category_name[1];} else {echo $category_name[0];} echo "</td>\n";
						echo "</tr>\n";
						ZobrazeniAdminTeam($ar['admin_category_id']);
						echo "</table>";
 					}
				}
				/*******************************************************
				*
				*	USER EDIT
				*
				*******************************************************/
				if (AGet($_GET,'action') == "user_edit"){
					switch (AGet($_GET,'mode')){
						case "guids":
							UserEditGuid(AGet($_GET,'mode'));
						break;
						case "guid_add":
							UserEditGuid(AGet($_GET,'mode'));
						break;
						case "guid_edit":
							UserEditGuid(AGet($_GET,'mode'));
						break;
						default:
							UserEdit("edit_user");
					}
					
				}
				/*******************************************************
				*
				*	USER DETAILS
				*
				*******************************************************/
				if (AGet($_GET,'action') == "user_details"){
					if ($_SESSION['u_status'] == "user" || $_SESSION['u_status'] == "admin"){
						$user = new User($eden_cfg);
						echo $user->showUserDetails($_GET['user_id'], "basic");
					} else {
						echo _ONLY_FOR_USERS;
					}
				}
				/*******************************************************
				*
				*	USERS LIST
				*
				*******************************************************/
				if (AGet($_GET,'action') == "users_list" || AGet($_GET,'action') == "users"){
					echo "<!-- <form action=\"index.php?lang=".AGet($_GET,'lang')."&amp;action=users&amp;sa=form\" method=\"post\" enctype=\"text/plain\">";
					echo "<input name=\"ul_word\" type=\"text\" height=\"10\" maxlength=\"20\">";
					echo "</form> --><br><br>";
					Alphabeth('index.php?action=users&amp;lang='.AGet($_GET,'lang').'&amp;filter='.AGet($_GET,'filter').'&amp;sa=form&amp;ul_letter=',''); echo "<br><br>";
					if ($_GET['sa'] == "form"){ echo "<table>"; UsersList($_GET['ul_letter'],$ul_word); echo "</table>"; }
 				}
				/*******************************************************
				*
				*	WHO IS ONLINE
				*
				*******************************************************/
				if (AGet($_GET,'action') == "whoisonline"){
					WhoIsOnline();
				}
			echo "</div>";
			echo "</div>";
		}
		if (AGet($_GET,'action') == "kal_archiv"){
			ShowArchivKalendar();}
			echo "</div>";
		}
echo "<!-- //////////////////////////////////////////////////////// CENTER - END ////////////////////////////////////////////////// -->";
echo "<!-- //////////////////////////////////////////////////////// RIGHT ////////////////////////////////////////////////// -->";
if(AGet($_GET,'action') != "forum"){
	echo "	<!-- ///////////////////////// RIGHT ///////////////////////// -->\n";
	echo "	<div class=\"content_side_col\">\n";
	if ($_SESSION['u_status'] == "user" || $_SESSION['u_status'] == "admin"){
		echo "<!-- # PERSONAL MENU -->\n";
		echo "	<div class=\"content_side_col_header\">\n";
		echo "		<div class=\"content_side_col_title\">Uživatelský panel</div>\n";
		echo "	</div>\n";
		echo "	<div class=\"content_side_col_cont\">\n";
		echo "		<div class=\"content_side_col_text\" style=\"min-height:50px;\">\n";
					LeagueMenuPersonality(); echo "<br>\n";
		echo "		</div>\n";
		echo "	</div>\n";
		echo "	<!-- *** PERSONAL MENU - KONEC *** -->\n";
		echo "<!-- # DRAFT -->\n";
		echo "	<div class=\"content_side_col_header\">\n";
		echo "		<div class=\"content_side_col_title\">Draft hráčů</div>\n";
		echo "	</div>\n";
		echo "	<div class=\"content_side_col_cont\">\n";
		echo "		<div class=\"content_side_col_text\" style=\"min-height:50px;\">\n";
					LeagueTeamDraft("small"); echo "<br>\n";
		echo "		</div>\n";
		echo "	</div>\n";
		echo "	<!-- *** DRAFT - KONEC *** -->\n";
	}
	echo "	<!-- # PARTNERS -->\n";
	echo "	<div class=\"content_side_col_header\">\n";
	echo "		<div class=\"content_side_col_title\">Partneři</div>\n";
	echo "	</div>\n";
	echo "	<div class=\"content_side_col_cont\">\n";
	echo "		<div class=\"content_side_col_text\"><br>\n";
	echo "			<div class=\"content_side_col_text\" style=\"text-align:center; margin: 0px 0px 10px -6px; \">"; OdkazySamotneId(47); echo "<br></div> \n";
	echo "		</div>\n";
	echo "	</div>\n";
	echo "	<!-- *** PARTNERS - KONEC *** -->\n";
	if (AdminCustom(5) == 1 && $panels == "full"){
		echo "	<!-- # POKEROVI PROFICI -->\n";
		echo "	<div class=\"content_side_col_header\">\n";
		echo "		<div class=\"content_side_col_title\">Pokeroví profíci</div>\n";
		echo "	</div>\n";
		echo "	<div class=\"content_side_col_cont\">\n";
		echo "		<div class=\"content_side_col_text\" style=\"min-height:130px;\">\n";
					   echo ShowProfile();
		echo "		</div>\n";
		echo "	</div>\n";
		echo "	<!-- *** POKEROVI PROFICI - KONEC *** -->\n";
	}
	/*
	echo "	<!-- # ONLINE HERNY -->\n";
	echo "	<div class=\"content_side_col_header\">\n";
	echo "		<div class=\"content_side_col_title\">Online herny</div>\n";
	echo "	</div>\n";
	echo "	<div class=\"content_side_col_cont\">\n";
	echo "		<div class=\"content_side_col_text\"><br>\n";
	echo "			<div class=\"content_side_col_text\" style=\"text-align:center; margin: 0px 0px 10px 0px; \">"; Reklama(48); echo "<br></div> \n";
	echo "		</div>\n";
	echo "	</div>\n";
	echo "	<!-- *** ONLINE HERNY - KONEC *** -->\n";
	*/
	echo "			<div>"; Reklama(48); echo "<br></div> \n";
	?>
<script language="javascript" type="text/javascript">
   var p = document.location.protocol;
   if (!p || p == null) p = "";
   var s = (p.toLowerCase().indexOf("http") == 0 ? p : "http:") + "//pokerstars.com/cs/ad/10171288/120x600.js";
   var r = Math.floor(Math.random()*999999)+''+Math.floor(Math.random()*999999);
   var c = document.createElement("script");
   c.type = "text/javascript";
   c.src = s+"?r="+r;
   c.id = ""+r;
   c.async = true;
   var a = document.getElementsByTagName("script");
   var t = a[a.length-1];
   t.parentNode.insertBefore(c, t);
</script>
<noscript><a href="http://pokerstars.com/cs/ad/10171288/120x600fd.gif.click?rq=noscript&vs="><img src="http://pokerstars.com/cs/ad/10171288/120x600fd.gif?rq=noscript&vs=" width="120" height="600" alt="" border="0"/></a></noscript>
<?php
	echo "</div>\n";
	echo "<!-- ///////////////////////// RIGHT - KONEC ///////////////////////// -->\n";
}
echo "<!-- //////////////////////////////////////////////////////// RIGHT - END ////////////////////////////////////////////////// -->\n";
echo "<!-- //////////////////////////////////////////////////////// FOOTER ////////////////////////////////////////////////// -->\n";
echo "</div>\n";
echo "<div id=\"footer\">\n";
echo "	<div id=\"footer_text\">&copy; Pokerteam.cz, ".date("Y")."&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "	<a href=\"mailto:".TransToASCII("redakce@pokerteam.cz")."\">redakce</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "	<a href=\"mailto:".TransToASCII("inzerce@pokerteam.cz")."\">inzerce</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "	<a href=\"mailto:".TransToASCII("petr.hota@pokerteam.cz")."\">webmaster</a>&nbsp;&nbsp;|&nbsp;&nbsp;\n";
echo "	<a href=\"index.php?action=provozovatel\">provozovatel</a>\n";
if ($eden_cfg['misc_local'] == 0){
	echo "<a href=\"http://www.toplist.cz/\" target=\"_blank\">\n";
	echo "<script type=\"text/javascript\">\n";
	echo "<!--\n";
	echo "document.write ('<img src=\"http://toplist.cz/count.asp?id=943596&logo=mc&s=1&http='+escape(document.referrer)+'&wi='+escape(window.screen.width)+'&he='+escape(window.screen.height)+'&cd='+escape(window.screen.colorDepth)+'&t='+escape(document.title)+'\" width=\"1\" height=\"1\" border=0 alt=\"TOPlist\">');\n";
	echo "//-->\n";
	echo "</script>\n";
	echo "</a><br><br>\n";
}
echo "	</div>\n";
//echo _SCRIPT_TIME; $time_end = getmicrotime(); $time = $time_end - $time_start; echo $time;
echo "</div>\n";
echo "<!-- hlavni -->\n";
if ($eden_cfg['misc_local'] == 0){
	echo "<!-- Google analytics - Start -->\n";
	echo "<script type=\"text/javascript\">\n";
	echo "var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");\n";
	echo "document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));\n";
	echo "</script>\n";
	echo "<script type=\"text/javascript\">\n";
	echo "try {\n";
	echo "var pageTracker = _gat._getTracker(\"UA-7963423-1\");\n";
	echo "pageTracker._trackPageview();\n";
	echo "} catch(err) {}</script>\n";
	echo "<!-- Google analytics - End -->\n";
}
echo "</div>\n";
echo "</body>\n";
echo "</html>";