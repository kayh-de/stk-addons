<?php
/**
 * copyright 2012 Stephen Just <stephenjust@users.sf.net>
 *
 * This file is part of stkaddons
 *
 * stkaddons is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * stkaddons is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with stkaddons.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('libs/Smarty.class.php');

/**
 * Create a template object 
 */
class Template {
    private static $smarty = NULL;
    private static $tpl_file = NULL;
    private static $tpl_root = TPL_PATH; // FIXME: Define this based on user setting
    
    public static $meta_desc = NULL;
    
    private static function createSmarty() {
	if (Template::$smarty != NULL)
	    return;

	Template::$smarty = new Smarty;
	Template::$smarty->compile_dir = TMP.'tpl_c/';
    }

    public static function setFile($tpl_file) {
	Template::createSmarty();

	if (file_exists(Template::$tpl_root.$tpl_file))
	    Template::$tpl_file = $tpl_file;
	else
	    throw new TemplateException('Template file "'.htmlspecialchars($tpl_file).'" was not found.');
    }
    
    public static function display() {
	Template::setupHead();
	Template::setupMenu();

	Template::$smarty->display(Template::$tpl_root.Template::$tpl_file);
    }
    
    private static function setupHead() {
	// Fill meta tags
	$meta_tags = array(
	    array('field' => 'content-type',    'content' => 'text/html; charset=UTF-8'),
	    array('field' => 'content-language','content' => LANG),
	    array('field' => 'description',	'content' => Template::$meta_desc)
	);
	Template::$smarty->assign('meta_tags',$meta_tags);

	// Fill script tags
	$script_inline = array(
	    array('content' => "var siteRoot='http://localhost/stk-web/';")
	);
	Template::$smarty->assign('script_inline',$script_inline);
	$script_includes = array(
	    array('src' => SITE_ROOT.'js/jquery.js'),
	    array('src' => SITE_ROOT.'js/jquery.newsticker.js'),
	    array('src' => SITE_ROOT.'js/script.js')
	);
	Template::$smarty->assign('script_includes',$script_includes);
    }
    
    private static function setupMenu() {
	// Main menu buttons
	$name = isset($_SESSION['real_name']) ? $_SESSION['real_name'] : NULL;
	$menu = array(
	    'welcome' => sprintf(htmlspecialchars(_('Welcome, %s')),$name),
	    'home' => File::link('index.php',htmlspecialchars(_("Home"))),
	    'login' => File::link('login.php',htmlspecialchars(_('Login'))),
	    'logout' => File::link('login.php?action=logout',htmlspecialchars(_('Log out'))),
	    'users' => File::link('users.php',htmlspecialchars(_('Users'))),
	    'upload' => File::link('upload.php',htmlspecialchars(_('Upload'))),
	    'manage' => File::link('manage.php',htmlspecialchars(_('Manage'))),
	    'karts' => File::link('addons.php?type=karts',htmlspecialchars(_('Karts'))),
	    'tracks' => File::link('addons.php?type=tracks',htmlspecialchars(_('Tracks'))),
	    'arenas' => File::link('addons.php?type=arenas',htmlspecialchars(_('Arenas'))),
	    'about' => File::link('about.php',htmlspecialchars(_('About'))),
	    'stk_home' => File::link('http://supertuxkart.sourceforge.net',htmlspecialchars(_('STK Homepage')))
	);
	Template::$smarty->assign('show_welcome',User::$logged_in);
	Template::$smarty->assign('show_login',!User::$logged_in);
	Template::$smarty->assign('show_users',User::$logged_in);
	Template::$smarty->assign('show_upload',User::$logged_in);
	Template::$smarty->assign('show_manage',(isset($_SESSION['role']['manageaddons'])) ? $_SESSION['role']['manageaddons'] : false);
	if (basename(get_self()) == 'addons.php') {
	    Template::$smarty->assign('show_karts',!($_GET['type'] == 'karts'));
	    Template::$smarty->assign('show_tracks',!($_GET['type'] == 'tracks'));
	    Template::$smarty->assign('show_arenas',!($_GET['type'] == 'arenas'));
	} else {
	    Template::$smarty->assign('show_karts',false);
	    Template::$smarty->assign('show_tracks',false);
	    Template::$smarty->assign('show_arenas',false);
	}
	Template::$smarty->assign('menu',$menu);

	// Language menu
	Template::$smarty->assign('lang_menu_lbl',htmlspecialchars(_('Languages')));
	$langs = array(
	    array('en_US',0,0,'EN'),
	    array('ca_ES',-96,-99,'CA'),
	    array('de_DE',0,-33,'DE'),
	    array('es_ES',-96,-66,'ES'),
	    array('fr_FR',0,-66,'FR'),
	    array('ga_IE',0,-99,'GA'),
	    array('gl_ES',-48,0,'GL'),
	    array('id_ID',-48,-33,'ID'),
	    array('it_IT',-96,-33,'IT'),
	    array('nl_NL',-48,-66,'NL'),
	    array('ru_RU',-48,-99,'RU'),
	    array('zh_TW',-96,0,'ZH (T)')
	);
	for ($i = 0; $i < count($langs); $i++) {
	    $url = $_SERVER['REQUEST_URI'];
	    // Generate the url to change the language
	    if (strstr($url,'?') === false)
		$url .= '?lang='.$langs[$i][0];
	    else {
		// Make sure any existing instances of lang are removed
		$url = preg_replace('/(&amp;)*lang=[a-z_]+/i',NULL,$url);
		$url .= '&amp;lang='.$langs[$i][0];
		$url = str_replace('?&amp;','?',$url);
	    }
	    $langs[$i][0] = $url;
	}
	Template::$smarty->assign('lang_menu_items',$langs);
    }
    
    public static function assignments($assigns) {
	if (!is_array($assigns))
	    throw new TemplateException('Invalid template assignments.');
	
	foreach ($assigns as $key => $value) {
	    Template::$smarty->assign($key,$value);
	}
    }
}
?>