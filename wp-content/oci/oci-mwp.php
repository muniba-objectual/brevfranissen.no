<?php
if( !defined( 'MIDDLEWARE_URL' ) ) {
	$api_version = 'v1.0';
	if( isset( $_SERVER[ 'ONECOM_WP_ADDONS_API' ] ) && $_SERVER[ 'ONECOM_WP_ADDONS_API' ] != '' ) {
		$ONECOM_WP_ADDONS_API = $_SERVER[ 'ONECOM_WP_ADDONS_API' ];
	} elseif( defined( 'ONECOM_WP_ADDONS_API' ) && ONECOM_WP_ADDONS_API != '' && ONECOM_WP_ADDONS_API != false ) {
		$ONECOM_WP_ADDONS_API = ONECOM_WP_ADDONS_API;
	} else {
		$ONECOM_WP_ADDONS_API = 'http://wpapi.one.com/';
	}
	$ONECOM_WP_ADDONS_API = rtrim( $ONECOM_WP_ADDONS_API, '/' );

	define( 'MIDDLEWARE_URL', $ONECOM_WP_ADDONS_API.'/api/'.$api_version );
	//define( 'MIDDLEWARE_URL', "http://wpapi.one.com/api/v1.0" );
}

/**
 * Function to get the domain
 * 
 * */
if( ! function_exists( 'get_domain' ) ) {
	function get_domain()
	{
		if(isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'localhost'){
			return 'localhost';
		}
		else if (isset($_SERVER['ONECOM_DOMAIN_NAME']) && !empty($_SERVER['ONECOM_DOMAIN_NAME'])) {
			return $_SERVER['ONECOM_DOMAIN_NAME'];
		} else {
			return 'localhost';
		}
	}
}

/**
 * Function to get the sub domain
 * 
 * */
if( ! function_exists( 'get_subdomain' ) ) {
	function get_subdomain()
	{
		if (get_domain() === 'localhost') {
			return null;
		}
		$subdomain = substr($_SERVER['SERVER_NAME'], 0, -(strlen($_SERVER['ONECOM_DOMAIN_NAME'])));
		if ($subdomain && $subdomain !== '') {
			return rtrim($subdomain, '.');
		} else {
			return 'www';
		}
	}
}

/**
* Function to tell URL for localhost
**/

if( ! function_exists( 'localhost_oci_path' ) ) {
	function localhost_oci_path() {

        $url = explode('/', $_SERVER['REQUEST_URI']);
        $remove_values = array('wp-admin', 'install.php', 'install.php?step=start', 'install.php?step=contact','install.php?step=settings', 'install.php?step=theme');

        foreach($remove_values as $value) {
            $key = array_search($value, $url);
            unset($url[$key]);
        }

        $dir = implode('/', $url);

		$domain 	= get_domain();
		$subdomain  = get_subdomain();

		if($subdomain != null){
			$subdomain = get_subdomain().'.';
		}
		$domain = $subdomain.$domain.'/'.$dir;
		return  "http://".$domain.'/wp-content/oci/';

	}
}
if( !defined( 'OCI_URL' ) ) {
	
	if ( $_SERVER["SERVER_ADDR"] == '127.0.0.1' || $_SERVER["SERVER_ADDR"] == 'localhost' || $_SERVER["SERVER_ADDR"] == '::1' ) {
		define( 'OCI_URL', localhost_oci_path() );
	} else {
		$parts = explode('/', $_SERVER['REQUEST_URI']);
		$remove_values = array('wp-admin', 'install.php', 'install.php?step=start', 'install.php?step=contact','install.php?step=settings', 'install.php?step=theme');

		foreach($remove_values as $value) {
			$key = array_search($value, $parts);
		   	unset($parts[$key]);
		}
		
		$dir = implode('/', $parts);

		$domain 	= get_domain();
		$subdomain  = get_subdomain();

		if($subdomain != null){
			$subdomain = get_subdomain().'.';
		}
		
		$url = '//'.$subdomain.$domain.$dir;
		
		define( 'OCI_URL',$url.'/wp-content/oci/' );
	}
}



if( !defined( 'OCI_DIR' ) ) {
	define( 'OCI_DIR', realpath( WP_CONTENT_DIR.'/oci/' ) );
}
if( !defined( 'ONECOM_WP_CORE_VERSION' ) ) {
	global $wp_version;
	define( 'ONECOM_WP_CORE_VERSION' , $wp_version );
}
if( !defined( 'ONECOM_PHP_VERSION' ) ) {
	define( 'ONECOM_PHP_VERSION' , phpversion() );
}

/**
 * Function to show premium badges on theme thumbnails.
 **/
if(!function_exists('oc_theme_badge')){
	function oc_theme_badge( $tag ) {
		if(! (is_array($tag) && in_array('premium', $tag))){
			return null;
		}
		echo '<span class="badge_bg" style="position: absolute;transform: rotate(45deg);z-index: 90;width: 105px;height: 73px;padding-top: 0px;top: -26px;right: -42px;background-color: #95265e;"></span>
	<span class="badge_icon" style="position: absolute;transform: rotate(45deg);z-index: 90;pointer-events: none;top: 8px;right: 13px;">
	<svg style="height: 15px;width: 9px;display: inline-block;"><use xlink:href="#topmenu_upgrade_large_d56dd1cace1438b6cbed4763fd6e5119">
	<svg viewBox="0 0 9 15" id="topmenu_upgrade_large_d56dd1cace1438b6cbed4763fd6e5119"><path d="M1.486 0h6L5.492 5.004l3.482-.009-6.839 9.38 1.627-6.903L0 7.469z" fill="#FFF" fill-rule="evenodd"></path></svg></use></svg></span>
	<span class="badge_text" style="position: absolute;transform: rotate(45deg);z-index: 90;color: #fff;text-transform: uppercase;font-style: normal;font-weight: 600;font-family: \'Open Sans\', sans-serif;display: block;text-align: center;top: 18px;font-size: 11px;right: 2px;-webkit-font-smoothing: antialiased;">Premium</span>';
	}
	add_filter( 'onecom_premium_theme_badge', 'oc_theme_badge', 10 );
}

/**
 * Function to show inline premium badge
 */
add_filter( 'onecom_premium_inline_badge', 'oc_inline_badge', 10 );
if(!function_exists('oc_inline_badge')){
	
	function oc_inline_badge() {
		echo '<span class="inline_badge standard" style="display: none;height: 28px; vertical-align: middle; margin-left: 20px; align-items: center;"><i class="inline_icon" style="background:url(\'data:image/svg+xml;base64,PHN2ZyBzdHlsZT0iZmlsbDojOTUyNjVFOyIgd2lkdGg9IjkiIGhlaWdodD0iMTQiIHZpZXdCb3g9IjAgMCA5IDE0IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik0xLjQ5IDBoNi4wMTdsLTIgNC44NzNMOSA0Ljg2NSAyLjE0MiAxNGwxLjYzLTYuNzIzTDAgNy4yNzR6IiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48L3N2Zz4=\');height: 13.5px;display: inline-block;vertical-align: middle;background-repeat: no-repeat;width: 9px;"></i><span class="inline_badge_text" style="-webkit-font-smoothing: antialiased;margin-left: 10px; opacity: 0.9;color: #333;font-family: Open Sans;font-size: 13px;line-height: 18px;">'.__("This is a Premium Theme", "oci").'</span> <a class="inline_badge_link" style="margin-left: 5px;color: #95265e;font-family: Open Sans;-webkit-font-smoothing: antialiased;font-size: 13px;font-weight: 600;line-height: 18px;cursor: pointer;text-decoration:none;" href="'.onecom_generic_locale_link('premium_page', $loaded_language).'">'.__("Learn more", "oci").'</a></span>';

		echo '<span class="inline_badge premium" style="display: none;height: 28px;vertical-align: middle;margin-left: 20px;align-items: center;color: #76a338;font-size: 14px;-webkit-font-smoothing: antialiased;"><svg style="width: 16px;height: 16px;pointer-events: none;margin-right:6px;"><use xlink:href="#premium_checkmark_91c2f8cf40d052f90c7b36218d17f875"><svg viewBox="0 0 13 13" id="premium_checkmark_91c2f8cf40d052f90c7b36218d17f875"><path d="M5.815 7.383L8.95 4.271l1.06 1.06-3.255 3.232-.953.953L3.354 7.01l1.06-1.06 1.4 1.433zM6.5 12.5a6 6 0 1 1 0-12 6 6 0 0 1 0 12zm0-1a5 5 0 1 0 0-10 5 5 0 0 0 0 10z" fill="#76a338"></path></svg></use></svg> Premium</span>';
	}
}

/**
* Function to query update
**/
if( ! function_exists( 'onecom_query_check' ) ) {
	function onecom_query_check( $url ) {
		$url = add_query_arg(
			array(
				'wp' => ONECOM_WP_CORE_VERSION,
				'php' => ONECOM_PHP_VERSION
			), $url
		);
		return $url;
	}
}

/**
 * function to set locale in js file
 */
if( ! function_exists( 'setLocale_jsFile' ) ) {
	function setLocale_jsFile() {
		return array(
			'BACK' => __('Back','oci'),
			'NEXTSTEP' => __('Next step','oci'),
			'SKIP' => __('Skip','oci'),
			'INSTALL' => __('Install theme' ,'oci'),
			'STARTAJAX' => __('Installing WordPress','oci'),
            'EMAILERROR' => __('This is an invalid email. Please change it.','oci'),
            'USERNAMERROR' => __('This is an invalid username. Please change it.','oci'),
		);
	}
}

/**
* Load OCI ajax handler
**/
$resource_extension = ( SCRIPT_DEBUG || SCRIPT_DEBUG == 'true') ? '' : '.min'; // Adding .min extension if SCRIPT_DEBUG is enabled
$resource_min_dir = ( SCRIPT_DEBUG || SCRIPT_DEBUG == 'true') ? '' : 'min-'; // Adding min- as a minified directory of resources if SCRIPT_DEBUG is enabled

$version = date("his");
/** Register OCI assets */
wp_register_script('one-mwp-jquery', OCI_URL . "assets/js/jquery-3.3.1.min.js", '', $version );
wp_register_script('one-mwp-wizard-script', OCI_URL . "assets/js/jquery.smartWizard.min.js", '', $version );
	
wp_register_script('one-tooltip-jquery', OCI_URL . "assets/js/bootstrap-tooltip.min.js", '', $version );
wp_register_style('one-tooltip-style', OCI_URL . "assets/css/bootstrap-tooltip.min.css", null, $version );

if((WP_DEBUG || WP_DEBUG == 'true' ) && (SCRIPT_DEBUG || SCRIPT_DEBUG == 'true' )){
	
	wp_register_style('one-mwp-style', OCI_URL . "assets/css/mwp-style.css", null, $version );
	wp_register_script('one-mwp-script', OCI_URL . "assets/js/mwp.js", array('one-mwp-jquery','one-mwp-wizard-script','media-upload','thickbox','wp-util','user-profile'), $version );

	$header_scripts = array(
		'one-mwp-style',
		'one-tooltip-style',
		'one-mwp-wizard-style',
		'thickbox'
	);

	$footer_scripts = array(
		'one-mwp-jquery',
		'one-mwp-wizard-script',
		'one-tooltip-jquery',
		'one-mwp-script'

	);
}else{

	wp_register_style('one-mwp-style', OCI_URL . 'assets/'.$resource_min_dir.'css/mwp-style'.$resource_extension.'.css', null, $version );
	wp_register_script('one-mwp-script', OCI_URL . 'assets/'.$resource_min_dir.'js/mwp'.$resource_extension.'.js', array('one-mwp-jquery','one-mwp-wizard-script','media-upload','thickbox','wp-util','user-profile'), $version );
	$header_scripts = array(
		'one-mwp-style',
        'one-tooltip-style',
		'one-mwp-wizard-style',
		'thickbox'
	);
	
	$footer_scripts = array(
		'one-mwp-jquery',
		'one-mwp-wizard-script',
        'one-tooltip-jquery',
		'one-mwp-script'
	);

}
wp_localize_script( 'one-mwp-script', 'oci', array( 'ajaxurl' => OCI_URL.'ajax.php','adminurl' => admin_url(),'LANG' => setLocale_jsFile() ) );

/**
* Function to fetch themes
**/
if( ! function_exists( 'oci_fetch_themes' ) ) {
	function oci_fetch_themes() {
		
		$themes = array();

		$url = onecom_query_check( MIDDLEWARE_URL.'/themes' );

		$url = add_query_arg(
			array(
				'item_count' => 1000
			), $url
		);

		$ip = onecom_get_client_ip_env();
		$domain = ( isset( $_SERVER[ 'ONECOM_DOMAIN_NAME' ] ) && ! empty( $_SERVER[ 'ONECOM_DOMAIN_NAME' ] ) ) ? $_SERVER[ 'ONECOM_DOMAIN_NAME' ] : 'localhost';

		if( empty( $themes ) || $themes == false ) {
			global $wp_version;
			$args = array(
			    'timeout'     => 5,
			    'httpversion' => '1.0',
			    'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
			    'body'        => null,
			    'compress'    => false,
			    'decompress'  => true,
			    'sslverify'   => true,
			    'stream'      => false,
			    'headers'       => array(
		            'X-ONECOM-CLIENT-IP' => $ip,
		            'X-ONECOM-CLIENT-DOMAIN' => $domain
		        )
			); 
			
			$response = wp_remote_get( $url, $args );
			$body = wp_remote_retrieve_body( $response );
			$body = json_decode( $body );

			$themes = array();

			if( !empty($body->success) && $body->success ) {
				$themes = $body->data->collection;
				if (is_array($themes) && !empty($themes)){
					foreach ($themes as $key=>$theme){
						if (isset($theme->slug) && $theme->slug === 'onecom-ilotheme'){
							unset($themes[$key]);
						}
					}
				}
			}

		}		

		return $themes;
	}
}

/**
 * Function to get the client ip address
 **/
if( ! function_exists( 'onecom_get_client_ip_env' ) ) {
	function onecom_get_client_ip_env() {
	    if (getenv('HTTP_CLIENT_IP')){
			$ipaddress = getenv('HTTP_CLIENT_IP');
		}else if(getenv('REMOTE_ADDR')){
			$ipaddress = getenv('REMOTE_ADDR');
		}else{
			$ipaddress = '0.0.0.0';
		}
	 
	    return $ipaddress;
	}
}


/**
 * Function to buil URLs as per locale
 */
global $onecom_global_links;
$onecom_global_links = array();
$onecom_global_links[ 'en' ] = array(
	'premium_page' => 'https://www.one.com/en/wordpress-hosting'
);
$onecom_global_links[ 'cs_CZ' ] = array(
	'premium_page' => 'https://www.one.com/cs/wordpress'
);
$onecom_global_links[ 'da_DK' ] = array(
	'premium_page' => 'https://www.one.com/da/wordpress'
);
$onecom_global_links[ 'de_DE' ] = array(
	'premium_page' => 'https://www.one.com/de/wordpress'
);
$onecom_global_links[ 'es_ES' ] = array(
	'premium_page' => 'https://www.one.com/es/wordpress'
);
$onecom_global_links[ 'fr_FR' ] = array(
	'premium_page' => 'https://www.one.com/fr/wordpress'
);
$onecom_global_links[ 'it_IT' ] = array(
	'premium_page' => 'https://www.one.com/it/wordpress'
);
$onecom_global_links[ 'nb_NO' ] = array(
	'premium_page' => 'https://www.one.com/no/wordpress'
);
$onecom_global_links[ 'nl_NL' ] = array(
	'premium_page' => 'https://www.one.com/nl/wordpress-hosting'
);
$onecom_global_links[ 'pl_PL' ] = array(
	'premium_page' => 'https://www.one.com/pl/wordpress'
);
$onecom_global_links[ 'pt_PT' ] = array(
	'premium_page' => 'https://www.one.com/pt/wordpress'
);
$onecom_global_links[ 'fi' ] = array(
	'premium_page' => 'https://www.one.com/fi/wordpress'
);
$onecom_global_links[ 'sv_SE' ] = array(
	'premium_page' => 'https://www.one.com/sv/wordpress-hosting'
);

if( ! function_exists( 'onecom_generic_locale_link' ) ) {
	function onecom_generic_locale_link( $request, $locale, $lang_only=0 ) {
		global $onecom_global_links;
		if( ! empty( $onecom_global_links )  && array_key_exists( $locale, $onecom_global_links ) ) {

			if($lang_only != 0){ return strstr($locale, '_', true); }

			if( ! empty( $onecom_global_links[ $locale ][ $request ] ) ) {
				return $onecom_global_links[ $locale ][ $request ];
			}
		}

		if($lang_only != 0){ return 'en'; }

		return $onecom_global_links[ 'en' ][ $request ];
	}
}

/*
 * Filter out themes marked as hidden
 * */
if( ! function_exists( 'onecom_filter_hidden_themes' ) ) {
    function onecom_filter_hidden_themes($themesArr = [])
    {
        // return if empty themes array
        if (empty($themesArr)) {
            return $themesArr;
        }

        // iterate through themes array and filter out which are hidden
        foreach ($themesArr as $key => $theme) {
            if ($theme->hidden === "true" || $theme->hidden === true) {
                unset($themesArr[$key]);
            }
        }
        return $themesArr;
    }
}