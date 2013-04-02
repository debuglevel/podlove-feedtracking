<?php
namespace Podlove\Modules\Feedtracking;

require_once('PiwikTracker.php');
require_once('GoogleAnalytics/autoload.php');

class Feedtracking extends \Podlove\Modules\Base {

	protected $module_name = 'Feedtracking';
	protected $module_description = 'Enable support for tracking feeds via Piwik (and maybe others in the future).';

	public function load() {
		add_action( 'rss2_head', array( $this, 'track' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
	}

	public function settings()
	{

		add_settings_section(
			/* $id 		 */ 'podlove_feedtracking_settings',
			/* $title 	 */ __( 'Feed Tracking', 'podlove_feedtracking' ),	
			/* $callback */ function () { },
			/* $page	 */ \Podlove\Settings\Settings::$pagehook	
		);

		add_settings_field(
			/* $id       */ 'podlove_feedtracking_setting_piwik_enabled',
			/* $title    */ sprintf(
				'<label for="feedtracking_piwik_siteid">%s</label>',
				__( 'Enable Piwik?', 'podlove_feedtracking' )
			),
			/* $callback */ function () {
				?>
				<input name="podlove[feedtracking_piwik_enabled]" id="feedtracking_piwik_enabled" type="checkbox" <?php checked( \Podlove\get_setting( 'feedtracking_piwik_enabled' ), 'on' ) ?>>
				<?php
			},
			/* $page     */ \Podlove\Settings\Settings::$pagehook,  
			/* $section  */ 'podlove_feedtracking_settings'
		);

		add_settings_field(
			/* $id       */ 'podlove_feedtracking_setting_piwik_siteid',
			/* $title    */ sprintf(
				'<label for="feedtracking_piwik_siteid">%s</label>',
				__( 'Piwik SiteID', 'podlove_feedtracking' )
			),
			/* $callback */ function () {
				?>
				<input name="podlove[feedtracking_piwik_siteid]" id="feedtracking_piwik_siteid" type="text" value="<?php echo \Podlove\get_setting( 'feedtracking_piwik_siteid' ) ?>">
				<?php
			},
			/* $page     */ \Podlove\Settings\Settings::$pagehook,  
			/* $section  */ 'podlove_feedtracking_settings'
		);

		add_settings_field(
			/* $id       */ 'podlove_feedtracking_setting_piwik_url',
			/* $title    */ sprintf(
				'<label for="feedtracking_piwik_url">%s</label>',
				__( 'Piwik URL', 'podlove_feedtracking' )
			),
			/* $callback */ function () {
				?>
				<input name="podlove[feedtracking_piwik_url]" id="feedtracking_piwik_url" type="text" value="<?php echo \Podlove\get_setting( 'feedtracking_piwik_url' ) ?>">
				<p>
					<span class="description"><?php echo __( 'e.g.: http://localhost/piwik/', 'podlove' ); ?></span>
				</p>
				<?php
			},
			/* $page     */ \Podlove\Settings\Settings::$pagehook,  
			/* $section  */ 'podlove_feedtracking_settings'
		);


		add_settings_field(
			/* $id       */ 'podlove_feedtracking_setting_ga_enabled',
			/* $title    */ sprintf(
				'<label for="feedtracking_piwik_siteid">%s</label>',
				__( 'Enable (untested) Google Analytics?', 'podlove_feedtracking' )
			),
			/* $callback */ function () {
				?>
				<input name="podlove[feedtracking_ga_enabled]" id="feedtracking_ga_enabled" type="checkbox" <?php checked( \Podlove\get_setting( 'feedtracking_ga_enabled' ), 'on' ) ?>>
				<?php
			},
			/* $page     */ \Podlove\Settings\Settings::$pagehook,  
			/* $section  */ 'podlove_feedtracking_settings'
		);

		add_settings_field(
			/* $id       */ 'podlove_feedtracking_setting_ga_accountid',
			/* $title    */ sprintf(
				'<label for="feedtracking_ga_accountid">%s</label>',
				__( 'GA AccountID', 'podlove_feedtracking' )
			),
			/* $callback */ function () {
				?>
				<input name="podlove[feedtracking_ga_accountid]" id="feedtracking_ga_accountid" type="text" value="<?php echo \Podlove\get_setting( 'feedtracking_ga_accountid' ) ?>">
				<p>
					<span class="description"><?php echo __( 'e.g.: UA-12345678-9', 'podlove' ); ?></span>
				</p>
				<?php
			},
			/* $page     */ \Podlove\Settings\Settings::$pagehook,  
			/* $section  */ 'podlove_feedtracking_settings'
		);

		add_settings_field(
			/* $id       */ 'podlove_feedtracking_setting_ga_domainname',
			/* $title    */ sprintf(
				'<label for="feedtracking_ga_domainname">%s</label>',
				__( 'GA Domain name', 'podlove_feedtracking' )
			),
			/* $callback */ function () {
				?>
				<input name="podlove[feedtracking_piwik_domainname]" id="feedtracking_ga_domainname" type="text" value="<?php echo \Podlove\get_setting( 'feedtracking_piwik_domainname' ) ?>">
				<p>
					<span class="description"><?php echo __( 'e.g.: www.example.org', 'podlove' ); ?></span>
				</p>
				<?php
			},
			/* $page     */ \Podlove\Settings\Settings::$pagehook,  
			/* $section  */ 'podlove_feedtracking_settings'
		);

	}

	public function track() {
		if ( \Podlove\get_setting( 'feedtracking_piwik_enabled' ) == true ) {
			$this->track_piwik();
		}
		if ( \Podlove\get_setting( 'feedtracking_ga_enabled' ) == true ) {
			$this->track_ga();
		}
	}

	public function track_piwik() {
		$t = new \PiwikTracker( $idSite = \Podlove\get_setting( 'feedtracking_piwik_siteid' ), \Podlove\get_setting( 'feedtracking_piwik_url' ));

		$t->setUserAgent( $_SERVER['HTTP_USER_AGENT'] );

		$httpurl = $this->full_url();
		$t->setUrl( $url = $httpurl );
		$t->setUrlReferrer( $url = $_SERVER['HTTP_REFERER'] );

		$t->doTrackPageView(apply_filters( 'podlove_feed_title', '' ));
	}

	public function track_ga() {
		// Initilize GA Tracker
		//$tracker = new GoogleAnalytics\Tracker('UA-12345678-9', 'example.com');
		$tracker = new \UnitedPrototype\GoogleAnalytics\Tracker(\Podlove\get_setting( 'feedtracking_ga_accountid' ), \Podlove\get_setting( 'feedtracking_ga_domainname' ));

		// Assemble Visitor information
		// (could also get unserialized from database)
		$visitor = new \UnitedPrototype\GoogleAnalytics\Visitor();
		$visitor->setIpAddress($_SERVER['REMOTE_ADDR']);
		$visitor->setUserAgent($_SERVER['HTTP_USER_AGENT']);
		$visitor->setScreenResolution('1024x768');

		// Assemble Session information
		// (could also get unserialized from PHP session)
		$session = new \UnitedPrototype\GoogleAnalytics\Session();

		// Assemble Page information
		$page = new \UnitedPrototype\GoogleAnalytics\Page($_SERVER['REQUEST_URI']);
		$page->setTitle(apply_filters( 'podlove_feed_title', '' ));

		// Track page view
		$tracker->trackPageview($page, $session, $visitor);
	}

	public function full_url()
	{
	    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	    $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
	    $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
	    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	    return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
	}

}
