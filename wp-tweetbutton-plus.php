<?php
/*
Plugin Name: WP-TweetButton Plus
Plugin URI: http://blog.unijimpe.net/wp-tweetbutton/
Description: This plugin allow insert Tweet Button on your blog or site. The button can be customized in the settings page. Tweet Button can be positioned a the top, bottom of content. You can use text shortcut <code>&lt;!--wp_twitterbutton--&gt;</code> and PHP function <code>wp_tweetbutton();</code> for manual insert. 
Version: 1.2
Author: unijimpe 
Author URI: http://blog.unijimpe.net
*/

// Global params
$wptb_version	= "1.2";
$wptb_params	= array("wptb_count"			=>	"vertical",
						"wptb_lang"				=>	"en",
						"wptb_via"				=>	"",
						"wptb_related"			=>	"",
						"wptb_related_desc"		=>	"",
						"wptb_text"				=>	"page",
						"wptb_text_value"		=>	"",
						
						"wptb_display_entry"	=>	"yes",
						"wptb_display_page"		=>	"",
						"wptb_display_home"		=>	"",
						"wptb_position"			=>	"before"
						);

// Define General Options
add_option("wptb_count",			$wptb_params["wptb_count"], 			'Count box position');
add_option("wptb_lang", 			$wptb_params["wptb_lang"], 				'The language for the Tweet Button');
add_option("wptb_via", 				$wptb_params["wptb_via"], 				'Screen name of the user to attribute the Tweet to');
add_option("wptb_related",			$wptb_params["wptb_related"],			'Related account');
add_option("wptb_related_desc",		$wptb_params["wptb_related_desc"],		'Related account description');
add_option("wptb_text", 			$wptb_params["wptb_text"], 				'Default Tweet text');
add_option("wptb_text_value", 		$wptb_params["wptb_text_value"], 		'Custom Tweet text value');

add_option("wptb_display_entry", 	$wptb_params["wptb_display_entry"], 	'Display button on all entries');
add_option("wptb_display_page", 	$wptb_params["wptb_display_page"], 		'Display button on all pages');
add_option("wptb_display_home", 	$wptb_params["wptb_display_home"], 		'Display button on Home');
add_option("wptb_position", 		$wptb_params["wptb_position"], 			'Position od button');

function getConfigTB() {
	// get config options into array var
	global $wptb_params;
    static $config;
    if (empty($config)) {
		foreach( $wptb_params as $option => $default) {
			$config[$option] = get_option($option);
		}
    }
    return $config;
}
function getButtonTB() {
	global $post;
	
	$config = getConfigTB();
	$option = "?url=".urlencode(get_permalink());
	// count param
	if ($config['wptb_count'] != "horizontal") { 
		$option.= "&amp;count=".$config['wptb_count'];
	}
	// language param
	if ($config['wptb_lang'] != "en") { 
		$option .= "&amp;lang=".$config['wptb_lang'];
	}
	// via param
	if ($config['wptb_via'] != "") {
		$option .= '&amp;via='.urlencode($config['wptb_via']);
	}
	// related param
	if ($config['wptb_related'] != "") {
		$option .= '&amp;related='.urlencode($config['wptb_related']);
		if ($config['wptb_related_desc'] != "") {
			$option .= ':'.urlencode($config['wptb_related_desc']);
		}
	}
	// text param
	if ($config['wptb_text'] == "page") {
		$option .= '&amp;text='.$post->post_title.' - '.get_bloginfo('name');
	}
	if ($config['wptb_text'] == "entry") {
		$option .= '&amp;text='.$post->post_title;
	}
	if ($config['wptb_text'] == "blog") {
		$option .= '&amp;text='.get_bloginfo('name');
	}
	if ($config['wptb_text'] == "custom") {
		$option .= '&amp;text='.$config['wptb_text_value'];
	}
	
	return "<a href=\"http://twitter.com/share".$option."\" class=\"twitter-share-button\">Tweet</a>";	
}
function addButtonTB($content) {
	$button = getButtonTB();
	$config = getConfigTB();
	
	if (substr_count($content, '<!--wp_tweetbutton-->') > 0) {
		$content = str_replace('<!--wp_tweetbutton-->', $button, $content);
	}
	
	if ($config['wptb_display_page'] == "" && is_page()) {
		return $content;
	}
	if ($config['wptb_display_entry'] == "" && is_single()) {
		return $content;
	}
	if ($config['wptb_display_home'] == "" && is_home()) {
		return $content;
	}
	
	if ($config['wptb_position'] == "after") {
		$content = $content."<p>".$button."</p>";
	}
	if ($config['wptb_position'] == "before") {
		$content = "<p>".$button."</p>".$content;
	}
	return $content;
}
function wp_tweetbutton() {
	$button = getButtonTB();
	echo $button;
}
function showConfigPageTB() {
	// update general options
	global $wptb_version, $wptb_params;
	
	if (isset($_POST['wptb_update'])) {
		check_admin_referer();
		foreach( $wptb_params as $option => $default ) {
			$wptb_param = trim($_POST[$option]);
			if ($wptb_param == "") {
				$wptb_param = $default;
			}
			update_option($option, $wptb_param);
		}
		echo "<div class='updated'><p><strong>WP-TweetButton Plus options updated</strong></p></div>";
	}
	$wptb_config = getConfigTB();
?>
		<form method="post" action="options-general.php?page=wp-tweetbutton-plus.php">
		<div class="wrap">
			<h2>WP-TweetButton Plus Options</h2>
            <h3>Basic Options</h3>
            <table class="form-table">
                <tr>
                    <th scope="row" valign="top">
                        <label for="wptb_count">Button Style</label>
                    </th>
                    <td id="tdcount">
                        <div style="float:left; width: 110px; height: 100px; background:url(<?php echo WP_PLUGIN_URL; ?>/wp-tweetbutton-plus/vertical.png) no-repeat 0px 30px;">
                            <input type="radio" name="wptb_count" id="wptb_count_v" value="vertical"<?php if ($wptb_config["wptb_count"] == "vertical") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_count_v">Vertical count</label>
                        </div>
                        <div style="float:left; width: 128px; height: 100px; background:url(<?php echo WP_PLUGIN_URL; ?>/wp-tweetbutton-plus/horizontal.png) no-repeat 0px 30px;">
                            <input type="radio" name="wptb_count" id="wptb_count_h" value="horizontal"<?php if ($wptb_config["wptb_count"] == "horizontal") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_count_h">Horizontal count</label>
                        </div>
                        <div style="float:left; width: 100px; height: 100px; background:url(<?php echo WP_PLUGIN_URL; ?>/wp-tweetbutton-plus/none.png) no-repeat 0px 30px;">
                            <input type="radio" name="wptb_count" id="wptb_count_n" value="none"<?php if ($wptb_config["wptb_count"] == "none") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_count_n">No count</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" valign="top">
                        <label for="wptb_lang">Language</label>
                    </th>
                    <td>
                        <select name="wptb_lang" id="wptb_lang" style="width:283px;">
                            <option value="en"<?php if ($wptb_config["wptb_lang"] == "en") { echo " selected=\"selected\""; } ?>>English</option>
                            <option value="fr"<?php if ($wptb_config["wptb_lang"] == "fr") { echo " selected=\"selected\""; } ?>>French</option>
                            <option value="de"<?php if ($wptb_config["wptb_lang"] == "de") { echo " selected=\"selected\""; } ?>>German</option>
                            <option value="es"<?php if ($wptb_config["wptb_lang"] == "es") { echo " selected=\"selected\""; } ?>>Spanish</option>
                            <option value="ja"<?php if ($wptb_config["wptb_lang"] == "ja") { echo " selected=\"selected\""; } ?>>Japanese</option>
                        </select>
                        <br />
                        <span class="description">The language for the Tweet Button</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row" valign="top">
                        <label for="wptb_via">Your Twitter Account</label>
                    </th>
                    <td>
                        @ <input type="text" name="wptb_via" id="wptb_via" value="<?php echo $wptb_config["wptb_via"]; ?>" style="width:270px;" />
                        <br />
                        <span class="description">Screen name of the user to attribute the Tweet</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row" valign="top">
                        <label for="wptb_related">Related Twitter Account</label>
                    </th>
                    <td>
                        @ <input type="text" name="wptb_related" id="wptb_related" value="<?php echo $wptb_config["wptb_related"]; ?>" style="width:100px;" /> 
                        Description <input type="text" name="wptb_related_desc" value="<?php echo $wptb_config["wptb_related_desc"]; ?>" style="width:100px;" /><br />
                        <span class="description">Twitter accounts for users to follow after they share</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row" valign="top">
                        <label for="wptb_text">Tweet text</label>
                    </th>
                    <td>
                    	<div>
                            <input type="radio" name="wptb_text" id="wptb_text_page" value="page"<?php if ($wptb_config["wptb_text"] == "page") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_text_page">Title of Page</label> <em style="color:#999;">(Example: "Post Title - Blog Title")</em>
                        </div> 
                        <div>
                            <input type="radio" name="wptb_text" id="wptb_text_entry" value="entry"<?php if ($wptb_config["wptb_text"] == "entry") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_text_entry">Title of Entry</label> <em style="color:#999;">(Example: "Post Title")</em>
                        </div>
                        <div>
                            <input type="radio" name="wptb_text" id="wptb_text_blog" value="blog"<?php if ($wptb_config["wptb_text"] == "blog") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_text_blog">Title of Blog</label> <em style="color:#999;">(Example: "Blog Title")</em>
                        </div>    
                        <div>
                            <input type="radio" name="wptb_text" id="wptb_text_custom" value="custom"<?php if ($wptb_config["wptb_text"] == "custom") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_text_custom">Custom text</label>
                            <input type="text" name="wptb_text_value" value="<?php echo $wptb_config["wptb_text_value"]; ?>" style="width:195px;" />    
                        </div>
                    </td>
                </tr>
            </table>
            <h3>More Options</h3>
            <table class="form-table">
                <tr>
                    <th scope="row" valign="top">
                        <label for="wptb_display">Display</label>
                    </th>
                    <td id="tdcount">
                    	<div>
                            <input type="checkbox" name="wptb_display_entry" id="wptb_display_entry" value="yes"<?php if ($wptb_config["wptb_display_entry"] == "yes") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_display_entry">Display in All Entries</label>
                        </div>
                        <div>
                            <input type="checkbox" name="wptb_display_page" id="wptb_display_page" value="yes"<?php if ($wptb_config["wptb_display_page"] == "yes") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_display_page">Display in All Pages</label>
                        </div>
                        <div>
                            <input type="checkbox" name="wptb_display_home" id="wptb_display_home" value="yes"<?php if ($wptb_config["wptb_display_home"] == "yes") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_display_home">Display in Home Page</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" valign="top">
                        <label for="wptb_position">Position</label>
                    </th>
                    <td>
                        <div>
                            <input type="radio" name="wptb_position" id="wptb_position_before" value="before"<?php if ($wptb_config["wptb_position"] == "before") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_position_before">Before Content</label>
                        </div>
                        <div>
                            <input type="radio" name="wptb_position" id="wptb_position_after" value="after"<?php if ($wptb_config["wptb_position"] == "after") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_position_after">After Content</label>
                        </div>
                        <div>
                            <input type="radio" name="wptb_position" id="wptb_position_none" value="none"<?php if ($wptb_config["wptb_position"] == "none") { echo " checked=\"checked\""; } ?> />
                            <label for="wptb_position_none">None</label>
                        </div>
                    </td>
                </tr>
            </table>
            <p class="submit">
                  <input name="wptb_update" value="Save Changes" type="submit" class="button-primary" />
            </p>
            <table>
                <tr>
                    <th width="30%" valign="top" style="padding-top: 10px; text-align:left;" colspan="2">
                        Custom Functions to use WP-TweetButton Plus
                    </th>
                </tr>
                <tr>
                    <td colspan="2">
                      <p>For include into post content use: <code>&lt;!--wp_twitterbutton--&gt;</code></p>
                      <p>For include in your template use: <code>&lt;?php wp_twitterbutton(); ?&gt;</code></p>
                    </td>
              	</tr>
                <tr>
                    <th width="30%" valign="top" style="padding-top: 10px; text-align:left;" colspan="2">
                        More Information and Support
                    </th>
                </tr>
                <tr>
                    <td colspan="2">
                      <p>Check our links for updates and comment there if you have any problems / questions / suggestions. </p>
                      <ul>
                        <li><a href="http://blog.unijimpe.net/wp-tweetbutton/">Plugin Home Page</a></li>
                        <li><a href="http://blog.unijimpe.net/">Author Home Page</a></li>
                        <li><a href="http://twitter.com/goodies/tweetbutton">Tweet Button Goodies Page</a></li>
                    	<li><a href="http://dev.twitter.com/pages/tweet_button">Tweet Button | Developer documentation for the Sharing API</a></li>
                      </ul>
                      <p>&nbsp;</p>
                 	</td>
              </tr>
            </table>			
		</div>
		</form>
<?php
}
function addMenuTB() {
	// add menu options
	add_options_page('WP-TweetButton Plus Options', 'WP-TweetButton Plus', 8, basename(__FILE__), 'showConfigPageTB');
}
function addHeaderTB() {
	// add header elements
	global $wptb_version;
	echo "\n<!-- WP-TweetButton Plus ".$wptb_version." by unijimpe -->";
	echo "\n<script type=\"text/javascript\" src=\"http://platform.twitter.com/widgets.js\"></script>\n";
}

add_filter('the_content', 'addButtonTB');
add_action('wp_head', 'addHeaderTB');
add_action('admin_menu', 'addMenuTB');
?>