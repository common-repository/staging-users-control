<?php
   /*
   Plugin Name: Staging Users Control
   Plugin URI: http://webmy.me/
   Description: A plugin to prevent certain users from logging into the back-end in the LIVE environment. You can let your users edit staging but not live.
   Version: 1.0
   Author: Yassine Haddioui
   Author URI: http://webmy.me/
   License: GPL2
   */
?>
<?


/* Add and saving a checkbox in the Users page */

function suc_user_field( $user ) {
	if ( !current_user_can( 'manage_options', $user->ID ) )
        return FALSE;
    $allowed_live = get_the_author_meta( 'allowed_live', $user->ID);
?>

<h3>
  <?php _e('Staging Users Control'); ?>
</h3>
<table class="form-table">
  <tbody>
    <tr class="show-allowed-live">
      <th scope="row">Can login to LIVE</th>
      <td><fieldset>
          <legend class="screen-reader-text"><span>Can login to LIVE</span></legend>
          <label for="allowed_live">
            <input name="allowed_live" type="checkbox" id="allowed_live" value="1" <?= $allowed_live ? ' checked="checked"' : ''?>>
            This user can login to the LIVE website.</label>
          <br>
        </fieldset></td>
    </tr>
  </tbody>
</table>
<?php 
}
function suc_save_custom_user_profile_fields( $user_id ) {
    if ( !current_user_can( 'manage_options', $user_id ) )
        return FALSE;
    update_user_meta( $user_id, 'allowed_live', $_POST['allowed_live'] );
}
add_action( 'show_user_profile', 'suc_user_field' );
add_action( 'edit_user_profile', 'suc_user_field' );
add_action( 'personal_options_update', 'suc_save_custom_user_profile_fields' );
add_action( 'edit_user_profile_update', 'suc_save_custom_user_profile_fields' );

function suc_rand_anum($length, $caps = TRUE) {
	if (!$caps)
 		$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
	else
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $random = '';
  for ($i = 0; $i < $length; $i++) {
    $random .= $characters[rand(0, strlen($characters) - 1)];
  }
  return $random;
}
/* Plugin Settings Page */

class SUCSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Staging Users Control', 
            'Staging Users Control', 
            'manage_options', 
            'staging-users-control-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option('suc_option');
		$blogurl = get_bloginfo('url');
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Staging Users Control</h2>           
            <form method="post" action="options.php">
            <p>This site URL is <strong><?=$blogurl?></strong> using <code>get_bloginfo('url')</code>.</p>
            <p>Don't forget to edit the users you want to allow on the LIVE site and check the option "Can login to LIVE" <strong>before</strong> setting up the LIVE url. By default, nobody is allowed on the live site.</p>
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'suc_group' );   
                do_settings_sections( 'staging-users-control-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'suc_group', // Option group
            'suc_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_1', // ID
            'Basic Settings', // Title
            array( $this, 'print_section_1_info' ), // Callback
            'staging-users-control-admin' // Page
        );  


        add_settings_field(
            'livesiteurl', 
            'Live Environment URL', 
            array( $this, 'livesiteurl_callback' ), 
            'staging-users-control-admin', 
            'setting_section_1'
        );

		add_settings_field(
            'showloginmessage', 
            'Show login warning', 
            array( $this, 'showloginmessage_callback' ), 
            'staging-users-control-admin', 
            'setting_section_1'
        );
		 add_settings_section(
            'setting_section_2', // ID
            'Advanced / Debug', // Title
            array( $this, 'print_section_2_info' ), // Callback
            'staging-users-control-admin' // Page
        ); 
		add_settings_field(
            'bkpcookie', 
            'Backup Cookie Value', 
            array( $this, 'bkpcookie_callback' ), 
            'staging-users-control-admin', 
            'setting_section_2'
        );  
		add_settings_field(
            'recognizeurl', 
            'Compare URL', 
            array( $this, 'recognizeurl_callback' ), 
            'staging-users-control-admin', 
            'setting_section_2'
        );
    }

    /**
     * Sanitize each setting field as needed - We use base64_encode to avoid Search & Replaces during pushes.
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();

        if( isset( $input['livesiteurl'] ) )
            $new_input['livesiteurl'] = base64_encode($input['livesiteurl']);
		if(isset($input['showloginmessage']) && $input['showloginmessage'])
            $new_input['showloginmessage'] = 1;
		else
			$new_input['showloginmessage'] = 0;
		if(isset($input['recognizeurl']) && $input['recognizeurl'])
            $new_input['recognizeurl'] = 1;
		else
			$new_input['recognizeurl'] = 0;
		 if($input['bkpcookie'])
            $new_input['bkpcookie'] = $input['bkpcookie'];
		else
			$new_input['bkpcookie'] = suc_rand_anum(32);
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_1_info()
    {
        ?>
        
        <?
    }
	    public function print_section_2_info()
    {
		 $this->options = get_option('suc_option');
		 
        ?>
        <p>If you find yourself locked out from the LIVE website, just setup a cookie with the name <strong>SUC-LOGMELIVE</strong> (caps) and the specified value <strong><?=$this->options['bkpcookie']?></strong>. It will allow you to login.</p>
        <?
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function livesiteurl_callback()
    {
        printf(
            '<input type="text" id="livesiteurl" name="suc_option[livesiteurl]" value="%s" class="regular-text" /><p>Leave empty to disable.</p>',
            isset( $this->options['livesiteurl'] ) ?  base64_decode($this->options['livesiteurl']) : ''
        );
    }
	public function bkpcookie_callback()
    {
        printf(
            '<input type="text" id="bkpcookie" name="suc_option[bkpcookie]" value="%s" class="regular-text" /><p>This will allow you to login in case you are locked out. You still need credentials, will not bypass the login process.</p>',
            isset( $this->options['bkpcookie'] ) ?  $this->options['bkpcookie'] : suc_rand_anum(32)
        );
    }
	public function showloginmessage_callback()
    {
        printf(
            '<input type="checkbox" id="showloginmessage" name="suc_option[showloginmessage]" value="1"  %s /><p>Show a message on the login page (Live site only).</p>',
            isset( $this->options['showloginmessage'] ) &&  $this->options['showloginmessage'] ? 'checked="checked"' : ''
        );
    }
	public function recognizeurl_callback()
    {
        printf(
            '<input type="checkbox" id="recognizeurl" name="suc_option[recognizeurl]" value="1"  %s /><p>Instead of comparing your URL with <code>get_bloginfo("url")</code>, we will compare it to the actual URL. HTTP/HTTPS must be taken in account.</p>',
            isset( $this->options['recognizeurl'] ) &&  $this->options['recognizeurl'] ? 'checked="checked"' : ''
        );
    }
}
if( is_admin() )
    $my_settings_page = new SUCSettingsPage();

/* Hooking the login function */

function suc_can_i_login($login = NULL) {
	if ($login)
		$user = get_user_by('login', $login);
	else
		$user = wp_get_current_user();
	$allowed_live = get_the_author_meta( 'allowed_live', $user->ID);
	$suc_options = get_option('suc_option');
	if ($suc_options)
	{
		$live_site_url = strtolower(trim(base64_decode($suc_options['livesiteurl'])));
		$blog_url = strtolower(trim(get_bloginfo('url')));
		if (suc_check_if_live($suc_options) && !$allowed_live && !suc_check_bkp_cookie($suc_options))
		{
			add_action( 'admin_notices', 'suc_admin_notice' );
			wp_logout();
			//die('You cannot login on the live website.');
		}
	}
	
}
add_action('wp_login', 'suc_can_i_login');
add_action('wp_loaded','suc_can_i_login');
function suc_admin_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'You cannot use the LIVE website. Please use staging. You have been logged out.', 'suc-text-domain' ); ?></p>
    </div>
    <?php
}
function suc_custom_login_message() {
	$suc_options = get_option('suc_option');
	$message = '';
	if ($suc_options && $suc_options['showloginmessage'])
	{
		$live_site_url = strtolower(trim(base64_decode($suc_options['livesiteurl'])));
		$blog_url = strtolower(trim(get_bloginfo('url')));
		if (suc_check_if_live()){
			$message .= '<p style="text-align:center;">Live site. <strong>Restricted Login Enabled.</strong></p>';
			return $message;
		}
		
	}
	return $message;
}
add_filter('login_message', 'suc_custom_login_message');

function suc_check_if_live($suc_options = NULL){
	if (!$suc_options)
		$suc_options = get_option('suc_option');
	if ($suc_options['livesiteurl'])
		$live_site_url = strtolower(trim(base64_decode($suc_options['livesiteurl'])));	
	else
		$live_site_url = '';
	$blog_url = strtolower(trim(get_bloginfo('url')));
	$current_url = suc_full_url($_SERVER);
	if (isset($suc_options['recognizeurl']) && $suc_options['recognizeurl'] && $current_url && $live_site_url)
	{
		if (strpos($current_url, $live_site_url) === 0)
			return true;
		else
			return false;
	}
	elseif ($blog_url == $live_site_url)
		return true;
	return false;
}
function suc_check_bkp_cookie($suc_options = NULL){
	if (!$suc_options)
		$suc_options = get_option('suc_option');
	if (isset($_COOKIE['SUC-LOGMELIVE']) && $_COOKIE['SUC-LOGMELIVE'] == $suc_options['bkpcookie'])
		return true;
	else
		return false;
}
function suc_url_origin($s, $use_forwarded_host=false)
{
    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
    $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}
function suc_full_url($s, $use_forwarded_host=false)
{
    return suc_url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

