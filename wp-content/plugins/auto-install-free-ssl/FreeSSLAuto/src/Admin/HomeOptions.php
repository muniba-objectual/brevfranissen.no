<?php

/**
 * @package Auto-Install Free SSL
 * This package is a WordPress Plugin. It issues and installs free SSL certificates in cPanel shared hosting with complete automation.
 *
 * @author Free SSL Dot Tech <support@freessl.tech>
 * @copyright  Copyright (C) 2019-2020, Anindya Sundar Mandal
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @link       https://freessl.tech
 * @since      Class available since Release 1.0.0
 *
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */
namespace AutoInstallFreeSSL\FreeSSLAuto\Admin;

use  AutoInstallFreeSSL\FreeSSLAuto\Acme\Factory as AcmeFactory ;
use  AutoInstallFreeSSL\FreeSSLAuto\Logger ;
/**
 * Home page options
 *
 */
class HomeOptions
{
    public  $factory ;
    public  $app_settings ;
    private  $logger ;
    /**
     * Start up
     */
    public function __construct()
    {
        if ( !defined( 'ABSPATH' ) ) {
            die( __( "Access denied", 'auto-install-free-ssl' ) );
        }
        $this->factory = new Factory();
        $this->app_settings = aifs_get_app_settings();
        $this->logger = new Logger();
        add_action( 'admin_enqueue_scripts', array( $this, 'countdown_js_script' ) );
    }
    
    /**
     *
     *
     * Previous __construct()
     */
    public function display()
    {
        $this->log_all_ca_server_response_handler();
        if ( aifs_is_free_version() ) {
            
            if ( aifs_is_existing_user() && time() < strtotime( "January 1, 2023" ) ) {
                //Display free Premium License request button
                //$this->request_free_premium_license(); // removed the method since 3.4.1
            } else {
                
                if ( !get_option( 'aifs_free_plan_selected' ) || isset( $_GET['comparison'] ) && $_GET['comparison'] == "yes" ) {
                    //before 3.4.0, 3.4.2
                    //if(isset($_GET['comparison']) && $_GET['comparison'] == "yes"){ // version 3.4.0, 3.4.1
                    $this->plan_comparison_table_handler();
                    $this->plan_comparison_table();
                } else {
                    wp_redirect( admin_url( 'admin.php?page=aifs_generate_ssl_manually' ) );
                    exit;
                }
            
            }
        
        }
        //This will be displayed after purchase
        if ( aifssl_fs()->is_paying() && !aifssl_fs()->is_premium() ) {
            $this->after_making_payment();
        }
    }
    
    /**
     *
     *
     * @return string
     */
    public function single_domain_ssl_data()
    {
        $app_settings = aifs_get_app_settings();
        $text_display = "";
        $certificate = $this->factory->single_domain_get_ssl_file_path();
        
        if ( $certificate ) {
            $cert_array = openssl_x509_parse( openssl_x509_read( file_get_contents( $certificate ) ) );
            /*$date = new DateTime('@' . $cert_array['validTo_time_t']);
              $expiry_date = $date->format('Y-m-d H:i:s') . ' ' . date_default_timezone_get();*/
            $exp = ( function_exists( 'wp_date' ) ? wp_date( 'F j, Y - h:i:s A', $cert_array['validTo_time_t'] ) : date( 'F j, Y - h:i:s A', $cert_array['validTo_time_t'] ) . " " . __( "UTC", 'auto-install-free-ssl' ) );
            $expiry_date = str_replace( '-', __( "at", 'auto-install-free-ssl' ), $exp );
            $issuerShort = $cert_array['issuer']['O'];
            //$subjectAltName = str_replace('DNS:', '', $cert_array['extensions']['subjectAltName']);
            if ( $_GET['page'] == "auto_install_free_ssl" ) {
                $text_display .= '<h3 style="color: #0b9e0b;">https://' . aifs_get_domain() . '</h3><br />';
            }
            $text_display .= __( "SSL Expiry date", 'auto-install-free-ssl' ) . ': ' . $expiry_date . '<br />';
            $text_display .= __( "Issuer", 'auto-install-free-ssl' ) . ': ' . $issuerShort . '<br />';
            if ( aifs_is_free_version() ) {
                
                if ( get_option( 'aifs_automated_domain_verification_trial_used' ) && get_option( 'aifs_number_of_ssl_generated' ) == 1 ) {
                    $text_display .= '<br /><div class="aifs-premium">🚀 <strong>' . sprintf( __( 'You\'ve experienced the ease of our Automatic Verification of Domain Ownership - a taste of the %1$sautomation magic%2$s that our Premium version offers.', 'auto-install-free-ssl' ), '<a href="#pro">', '</a>' ) . '</strong></div><br />';
                } else {
                    $days = __( "30", 'auto-install-free-ssl' );
                    /* translators: %s: A plural number, e.g., 30 */
                    $text_display .= '<br /><strong><s>' . sprintf( __( "This plugin will renew & install the SSL automatically %s days before the expiry.", 'auto-install-free-ssl' ), $days ) . '</s></strong>';
                    /* translators: %s: placeholders for HTML code create a hyperlink with the word 'Premium'. */
                    $text_display .= '<br /><div class="aifs-premium"><span class="dashicons dashicons-arrow-up-alt"></span> ' . sprintf( __( "%sPremium%s feature", 'auto-install-free-ssl' ), '<a href="' . $this->factory->upgrade_url() . '">', '</a>' ) . ' <span class="dashicons dashicons-arrow-up-alt"></span></div>';
                }
            
            }
            //echo 'Home directory: '.$this->factory->set_ssl_parent_directory().'<br />';
            $text_display .= '<br /><hr />';
            
            if ( aifs_is_free_version() || !aifs_can_manage_multi_domain() ) {
                $certificate = wp_nonce_url( get_site_url() . $_SERVER['REQUEST_URI'], 'aifs_download_ssl', 'aifsdownloadssl' ) . "&domain=" . aifs_get_domain( true );
                $text_display .= '<span class="dashicons dashicons-download" style="color: darkviolet;"></span>&nbsp;<span style="font-size: large; font-weight: bold; color: darkviolet;">' . __( "Downloads", 'auto-install-free-ssl' ) . '</span><br />';
                $text_display .= '<div class="aifs-download"><a href="' . $certificate . '&file=certificate.pem" title="' . __( "Click here to download SSL Certificate", 'auto-install-free-ssl' ) . '">' . __( "SSL", 'auto-install-free-ssl' ) . '</a> &nbsp;&nbsp;&nbsp;| &nbsp;&nbsp;&nbsp;';
                $text_display .= '<a href="' . $certificate . '&file=private.pem" title="' . __( "Click here to download Private Key", 'auto-install-free-ssl' ) . '">' . __( "Private Key", 'auto-install-free-ssl' ) . '</a> &nbsp;&nbsp;&nbsp;| &nbsp;&nbsp;&nbsp;';
                $text_display .= '<a href="' . $certificate . '&file=cabundle.pem" title="' . __( "Click here to download CA Bundle", 'auto-install-free-ssl' ) . '">' . __( "CA Bundle", 'auto-install-free-ssl' ) . '</a></div>';
                $text_display .= '<hr />';
            }
            
            
            if ( aifs_is_free_version() && !get_option( 'aifs_force_ssl' ) && get_option( 'aifs_is_generated_ssl_installed' ) ) {
                /* translators: %s: placeholders for HTML code to create a hyperlink with the text 'Activate Force HTTPS'. */
                $text_display .= sprintf( __( "After installing the SSL certificate, don't forget to %sActivate Force HTTPS%s", 'auto-install-free-ssl' ), '<a href="#force-https">', '</a>' );
                $text_display .= "<hr />";
            }
        
        }
        
        return $text_display;
    }
    
    /**
     * Display plan comparison table
     * @since 3.0.6
     */
    public function plan_comparison_table()
    {
        $app_settings = aifs_get_app_settings();
        $hosting = __( "hosting", 'auto-install-free-ssl' );
        /* translators: 'cPanel' is web hosting control panel software developed by cPanel, LLC. */
        $cpanel = __( "cPanel", 'auto-install-free-ssl' );
        /* translators: "Let's Encrypt" is a nonprofit SSL certificate authority. */
        $ca = __( "Let's Encrypt™", 'auto-install-free-ssl' );
        $free = wp_nonce_url( get_site_url() . $_SERVER['REQUEST_URI'], 'aifs_free_plan', 'aifsfree' );
        ?>
        <div class="wrap">

        <?php 
        echo  aifs_header() ;
        ?>

          <div>
            <p style="font-size: large; text-align: center; color: black;"><?php 
        echo  __( "We'll save you \$90+ per year for every website", 'auto-install-free-ssl' ) ;
        ?></p>
          </div>

        <?php 
        
        if ( !isset( $_GET['comparison'] ) && isset( $_GET['welcome'] ) && $_GET['welcome'] == "yes" ) {
            ?>
          <div class="card block-body" style="max-width: 100%; text-align: center;">
            <p><?php 
            /* translators: %s: Name of this plugin, i.e., 'Auto-Install Free SSL' */
            echo  sprintf( __( 'Thank you for choosing \'%s\' - the most powerful plugin to generate Free SSL Certificates in your WordPress dashboard.', 'auto-install-free-ssl' ), AIFS_NAME ) ;
            ?></p>

            <p><?php 
            /* translators: %1$s: Name of this plugin, i.e., 'Auto-Install Free SSL'; %2$s: Name of the SSL certificate authority, e.g., Let's Encrypt */
            echo  sprintf( __( '\'%1$s\' makes creating Free SSL Certificates easy using the %2$s API. Please get started by creating an SSL Certificate or reading our documentation.', 'auto-install-free-ssl' ), AIFS_NAME, $ca ) ;
            ?></p>
            <p style="margin-top: 2%;"><a href="<?php 
            echo  $free ;
            ?>" class="button button-primary"><?php 
            echo  __( "Generate Your First SSL Certificate", 'auto-install-free-ssl' ) ;
            ?></a> <a href="https://freessl.tech/wordpress-letsencrypt-free-ssl-certificate-documentation/?utm_source=users_website&utm_medium=dashboard&utm_campaign=aifs_free&utm_content=welcome_section" target="_blank" class="button" style="margin-left: 5%;"><?php 
            echo  __( "Read the Documentation", 'auto-install-free-ssl' ) ;
            ?></a></p>
          </div>
        <?php 
        }
        
        ?>

          <p style="font-size: xx-large; text-align: center; color: black;"><?php 
        echo  __( "Features Comparison", 'auto-install-free-ssl' ) ;
        ?></p>

          <p style="font-size: medium; text-align: center;"><?php 
        echo  __( "Please scroll down to learn about the features and select any plugin version", 'auto-install-free-ssl' ) ;
        ?></p>

        <?php 
        /*if($this->factory->is_cpanel() && ((get_option('aifs_premium_plan_selected') >= 1 && time() < strtotime("January 1, 2024")) || (time() > strtotime("November 1, 2022") && time() < strtotime("January 1, 2024")))){
              $coupon_code = "20AutoInstall";
              echo '<div style="background: white; color: black; margin: 2% 0; padding: 1% 1.5%; border-style: solid; border-width: 1px; border-color: green;"><p style="font-size: medium; margin: 0;">';
              echo '<span class="dashicons dashicons-megaphone" style="font-size: xx-large; color: #5F97FB;"></span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . sprintf(__('Could a coupon help? Use the %1$s discount%2$s code %3$s during checkout. The coupon is expiring soon!', 'auto-install-free-ssl'), "<span style='color: red;'>20%", "</span>", "<strong>'$coupon_code'</strong>");
              echo '</p></div>';
          }*/
        /*if(!get_option('aifs_default_page_first_access_time')){
                        add_option('aifs_default_page_first_access_time', time());
                    }
        
                    $first_access_time = get_option('aifs_default_page_first_access_time');*/
        //if(($first_access_time && (time() > $first_access_time + 5 * 60)) || (isset($_GET['comparison']) && $_GET['comparison'] == "yes")){
        if ( !get_option( 'aifs_comparison_table_promo_start_time' ) ) {
            add_option( 'aifs_comparison_table_promo_start_time', time() );
        }
        $start_time = get_option( 'aifs_comparison_table_promo_start_time' );
        
        if ( $this->factory->is_cpanel() && time() < $start_time + AIFS_COUNTDOWN_DURATION && (get_option( 'aifs_premium_plan_selected' ) >= 1 && time() < strtotime( "January 1, 2024" ) || time() > strtotime( "November 1, 2022" ) && time() < strtotime( "January 1, 2024" )) ) {
            echo  '<div id="aifs-promo" class="aifs-promo"><p style="font-size: medium; margin: 0;">' ;
            echo  '<span class="dashicons dashicons-arrow-down-alt" style="font-size: xx-large; color: #5F97FB;"></span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ;
            
            if ( time() > strtotime( "August 19, 2023" ) && time() < strtotime( "September 22, 2023" ) ) {
                $coupon_code = "SUMMER_40";
                $percentage = __( "40%", 'auto-install-free-ssl' );
                echo  "<strong>End-of-Summer Sale:</strong> " ;
            } else {
                $coupon_code = "20AutoInstall";
                $percentage = __( "20%", 'auto-install-free-ssl' );
            }
            
            /* translators: %1$s: HTML code to make the color red of the text '%2$s discount!'. Please keep its order the same.    %2$s: Discount percentage (includes % sign) */
            echo  sprintf( __( 'Select any Pro plan asap to grab a %1$s %2$s discount!', 'auto-install-free-ssl' ), "<span style='color: red;'>", $percentage ) . "</span>" ;
            echo  '<img src="' . AIFS_URL . 'assets/img/fire.webp" style="margin-left: 2%; width 20px; height: 20px;"><span class="expires-in">' . __( "expires in", 'auto-install-free-ssl' ) . ' <span id="countdown" style=""></span></span>' ;
            echo  '</p></div>' ;
            echo  '<script type="text/javascript">' . $this->countdown_js_script() . '</script>' ;
            //add_action( 'admin_enqueue_scripts', array($this, 'countdown_js_script') );
        } else {
            $coupon_code = false;
        }
        
        /*}
          else{
              $coupon_code = false;
          }*/
        ?>

          <div class="aifs-pricing">

            <?php 
        $intro_text = __( "Hello", 'auto-install-free-ssl' ) . (( strlen( aifs_admin_first_name() ) > 0 ? " " . aifs_admin_first_name() : "" )) . ", ";
        $intro_text_small = "";
        $ssl_installation = __( "Automatic", 'auto-install-free-ssl' );
        $ssl_installation_more_info = "";
        
        if ( !isset( $app_settings['is_cpanel'] ) || !$app_settings['is_cpanel'] ) {
            $intro_text .= '<span style="color: #77c401;">' . __( "we'll issue you a full refund if we can't automate your SSL certificate.", 'auto-install-free-ssl' ) . ' *</span>';
            //$ssl_installation_more_info = __( "Please contact us after purchase. We'll set up automatic SSL installation either with a bash script or CDN.", 'auto-install-free-ssl');
            $time_required = __( "We'll do it", 'auto-install-free-ssl' );
            $time_required_unlimited_license = __( "We'll do it (10 sites)", 'auto-install-free-ssl' );
            /* translators: %s: HTML code to create a new line. */
            $time_required_more_info_unlimited_license = sprintf( __( "PRO unlimited license is limited to 10 websites. %sIf you can implement the automation with the bash script yourself, it is truly unlimited.", 'auto-install-free-ssl' ), "\n" );
            $wildcard_ssl_single_domain = __( "Yes", 'auto-install-free-ssl' );
            $multisite_support_single_domain = __( "Yes", 'auto-install-free-ssl' );
        } else {
            /* translators: 'cPanel' is web hosting control panel software developed by cPanel, LLC. */
            $intro_text .= __( "you have cPanel! 100% automation is possible with premium!!", 'auto-install-free-ssl' ) . " ";
            $intro_text .= '<span style="color: #77c401;">' . __( "Otherwise, we'll issue you a full refund.", 'auto-install-free-ssl' ) . '</span>';
            $time_required = __( "1 Min (once)", 'auto-install-free-ssl' );
            $time_required_unlimited_license = __( "1 Min (once)", 'auto-install-free-ssl' );
            $nine = __( "9", 'auto-install-free-ssl' );
            $ten = __( "10", 'auto-install-free-ssl' );
            /* translators: %1$s: Name of the web hosting control panel ('cPanel' or 'hosting'; based on a condition); %2$s: A plural number, e.g., 10 */
            $time_required_more_info_unlimited_license = sprintf( __( 'If you need the plugin to work on all websites in the same %1$s, you need %2$s minutes (once).', 'auto-install-free-ssl' ), ( isset( $app_settings['is_cpanel'] ) && $app_settings['is_cpanel'] ? $cpanel : $hosting ), ( isset( $app_settings['is_cpanel'] ) && $app_settings['is_cpanel'] ? $nine : $ten ) );
            $wildcard_ssl_single_domain = __( "No", 'auto-install-free-ssl' );
            $multisite_support_single_domain = __( "No", 'auto-install-free-ssl' );
        }
        
        //if(!aifs_is_existing_user()){
        ?>

                <h1 style="margin: 0 1% -1% 2%; font-size: 1.4em; line-height: 1.5em;"><?php 
        echo  $intro_text ;
        ?></h1>
                <p style="margin-left: 2%;"><?php 
        echo  $intro_text_small ;
        ?></p>
            <?php 
        //}
        ?>
            <table class="table">
              <!-- Heading -->
              <thead>
                <tr>
                  <th>&nbsp;</th>
                  <th>
                    <?php 
        echo  __( "Free", 'auto-install-free-ssl' ) ;
        ?>
                    <span class="ptable-price"><?php 
        echo  __( "\$0.0", 'auto-install-free-ssl' ) ;
        ?></span>
                  </th>
                  <th class="highlight">
                    <?php 
        echo  __( "Pro", 'auto-install-free-ssl' ) ;
        ?>
                    <span class="ptable-price"><?php 
        echo  __( "\$26.99 / year", 'auto-install-free-ssl' ) ;
        ?></span>
                    <span class="ptable-price">(<?php 
        echo  __( "Lifetime: \$44.99", 'auto-install-free-ssl' ) ;
        ?>)</span>
                  </th>
                  <th>
                    <?php 
        echo  __( "Pro Unlimited", 'auto-install-free-ssl' ) ;
        ?>
                    <span class="ptable-price"><?php 
        echo  __( "\$178.99 / year", 'auto-install-free-ssl' ) ;
        ?></span>
                    <span class="ptable-price">(<?php 
        echo  __( "Lifetime: \$258.99", 'auto-install-free-ssl' ) ;
        ?>)</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><span class="ptable-title"><?php 
        echo  __( "Domain Verification", 'auto-install-free-ssl' ) ;
        ?></span></td>
                  <td>
                    <?php 
        echo  __( "Manual", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "Automatic", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "Automatic", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                </tr>
                <tr>
                  <td><span class="ptable-title"><?php 
        echo  __( "Generate SSL", 'auto-install-free-ssl' ) ;
        ?></span></td>
                  <td>
                    <?php 
        echo  __( "Manual", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "Automatic", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "Automatic", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                </tr>
                <tr>
                  <td><span class="ptable-title"><?php 
        echo  __( "SSL Installation", 'auto-install-free-ssl' ) ;
        ?></span></td>
                  <td>
                    <?php 
        echo  __( "Manual", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  $ssl_installation ;
        ?> <!-- <a title="<?php 
        echo  $ssl_installation_more_info ;
        ?>">[?]</a> -->
                  </td>
                  <td>
                    <?php 
        echo  $ssl_installation ;
        ?> <!-- <a title="<?php 
        echo  $ssl_installation_more_info ;
        ?>">[?]</a> -->
                  </td>
                </tr>
                <tr>
                  <td><span class="ptable-title"><?php 
        echo  __( "SSL Renewal", 'auto-install-free-ssl' ) ;
        ?></span></td>
                  <td>
                    <?php 
        echo  __( "Manual", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "Automatic", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "Automatic", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                </tr>

                <?php 
        
        if ( isset( $app_settings['is_cpanel'] ) && $app_settings['is_cpanel'] ) {
            ?>
                    <tr>
                      <td><span class="ptable-title"><?php 
            echo  __( "Cron Job", 'auto-install-free-ssl' ) ;
            ?></span></td>
                      <td>
                        <?php 
            echo  __( "No", 'auto-install-free-ssl' ) ;
            ?>
                      </td>
                      <td>
                        <?php 
            echo  __( "Automatic", 'auto-install-free-ssl' ) ;
            ?> <a title="<?php 
            echo  __( "You don’t need to set up the Cron Job manually. It works by default.", 'auto-install-free-ssl' ) ;
            ?>">[?]</a>
                      </td>
                      <td>
                        <?php 
            echo  __( "Automatic", 'auto-install-free-ssl' ) ;
            ?> <a title="<?php 
            echo  __( "You don’t need to set up the Cron Job manually. It works by default.", 'auto-install-free-ssl' ) ;
            ?>">[?]</a>
                      </td>
                    </tr>
                <?php 
        } else {
            ?>
                    <tr>
                      <td><span class="ptable-title"><?php 
            echo  __( "Automation with Bash script or Cloudflare CDN", 'auto-install-free-ssl' ) ;
            ?></span></td>
                      <td>
                        <?php 
            echo  __( "No", 'auto-install-free-ssl' ) ;
            ?>
                      </td>
                      <td>
                        <?php 
            echo  __( "Yes", 'auto-install-free-ssl' ) ;
            ?> <a title="<?php 
            echo  __( "Please get in touch with us after purchase. After reviewing your web hosting environment, we'll choose the best option (Bash script or Cloudflare CDN) and implement the automation. This service is applicable if you purchase without a discount.", 'auto-install-free-ssl' ) ;
            ?>">[?]</a>
                      </td>
                      <td>
                        <?php 
            echo  __( "Yes", 'auto-install-free-ssl' ) ;
            ?> <a title="<?php 
            echo  __( "Please get in touch with us after purchase. After reviewing your web hosting environment, we'll choose the best option (Bash script or Cloudflare CDN) and implement the automation. This service is applicable if you purchase without a discount.", 'auto-install-free-ssl' ) ;
            ?>">[?]</a>
                      </td>
                    </tr>
                <?php 
        }
        
        ?>

                <tr>
                  <td><span class="ptable-title"><?php 
        echo  __( "Time Required to Set Up", 'auto-install-free-ssl' ) ;
        ?></span></td>
                  <td>
                    <?php 
        echo  __( "20+ Min (per 60 days)", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  $time_required ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  $time_required_unlimited_license ;
        ?> <a title="<?php 
        echo  $time_required_more_info_unlimited_license ;
        ?>">[?]</a>
                  </td>
                </tr>
                <tr>
                  <td><span class="ptable-title"><?php 
        echo  __( "Wildcard SSL", 'auto-install-free-ssl' ) ;
        ?></span></td>
                  <td>
                    <?php 
        echo  __( "No", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  $wildcard_ssl_single_domain ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "Yes", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                </tr>
                <tr>
                  <td><span class="ptable-title"><?php 
        echo  __( "Multisite Support", 'auto-install-free-ssl' ) ;
        ?></span></td>
                  <td>
                    <?php 
        echo  __( "No", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  $multisite_support_single_domain ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "Yes", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                </tr>
                <tr>
                  <td><span class="ptable-title"><?php 
        echo  __( "SSL Expiration Chance", 'auto-install-free-ssl' ) ;
        ?></span></td>
                  <td>
                    <?php 
        echo  __( "High", 'auto-install-free-ssl' ) ;
        ?> <a title="<?php 
        echo  __( "Manually renewing SSL every 60 days is tiresome and challenging to remember", 'auto-install-free-ssl' ) ;
        ?>">[?]</a>
                  </td>
                  <td>
                    <?php 
        echo  __( "No", 'auto-install-free-ssl' ) ;
        ?> <!-- <a title="<?php 
        //echo __( "Because the renewal is automated", 'auto-install-free-ssl' )
        ?>">[?]</a> -->
                  </td>
                  <td>
                    <?php 
        echo  __( "No", 'auto-install-free-ssl' ) ;
        ?> <!-- <a title="<?php 
        //echo __( "Because the renewal is automated", 'auto-install-free-ssl' )
        ?>">[?]</a> -->
                  </td>
                </tr>

                <?php 
        
        if ( isset( $app_settings['is_cpanel'] ) && $app_settings['is_cpanel'] ) {
            ?>
                    <tr>
                      <td><span class="ptable-title"><?php 
            /* translators: %s: A technical word ('cPanel' or 'hosting'; based on a condition) */
            echo  sprintf( __( "One installation works on all websites of a %s", 'auto-install-free-ssl' ), ( isset( $app_settings['is_cpanel'] ) && $app_settings['is_cpanel'] ? $cpanel : $hosting ) ) ;
            ?></span></td>
                      <td>
                        <?php 
            echo  __( "No", 'auto-install-free-ssl' ) ;
            ?>
                      </td>
                      <td>
                        <?php 
            echo  __( "No", 'auto-install-free-ssl' ) ;
            ?>
                      </td>
                      <td>
                        <?php 
            echo  __( "Yes", 'auto-install-free-ssl' ) ;
            ?>
                      </td>
                    </tr>
                <?php 
        }
        
        ?>

                <tr>
                  <td><span class="ptable-title"><?php 
        echo  __( "Support", 'auto-install-free-ssl' ) ;
        ?></span></td>
                  <td>
                    <?php 
        echo  __( "Forum", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "E-mail / Chat", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "E-mail / Chat", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                </tr>

                <tr>
                  <td><span class="ptable-title"><?php 
        echo  __( "No Advertisements", 'auto-install-free-ssl' ) ;
        ?></span></td>
                  <td>
                    <?php 
        echo  __( "No", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "Yes", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                  <td>
                    <?php 
        echo  __( "Yes", 'auto-install-free-ssl' ) ;
        ?>
                  </td>
                </tr>
                <!-- Buttons -->
                <?php 
        $coupon = ( $coupon_code ? "&coupon=" . $coupon_code : "" );
        //$free = wp_nonce_url( get_site_url().$_SERVER['REQUEST_URI'], 'aifs_free_plan', 'aifsfree' ); //already defined
        $pro = wp_nonce_url( get_site_url() . $_SERVER['REQUEST_URI'], 'aifs_pro_plan', 'aifspro' ) . $coupon;
        $pro_unlimited = wp_nonce_url( get_site_url() . $_SERVER['REQUEST_URI'], 'aifs_pro_unlimited_plan', 'aifsprounlimited' ) . $coupon;
        ?>
                <tr>
                  <td>&nbsp;</td>
                  <td class="bg-red"><a class="btn" href="<?php 
        echo  $free ;
        ?>"><?php 
        echo  __( "Select", 'auto-install-free-ssl' ) ;
        ?></a></td>
                  <td class="bg-green"><a class="btn" href="<?php 
        echo  $pro ;
        ?>"><?php 
        echo  __( "Select", 'auto-install-free-ssl' ) ;
        ?></a></td>
                  <td class="bg-lblue"><a class="btn" href="<?php 
        echo  $pro_unlimited ;
        ?>"><?php 
        echo  __( "Select", 'auto-install-free-ssl' ) ;
        ?></a></td>
                </tr>
              </tbody>
            </table>
          </div>

          <?php 
        if ( !isset( $app_settings['is_cpanel'] ) || !$app_settings['is_cpanel'] ) {
            /* translators: %s: HTML code to create a hyperlink with the text 'Terms & conditions'. */
            echo  "<br /><br /><p>* <i>" . sprintf( __( "%sTerms & conditions%s apply.", 'auto-install-free-ssl' ), '<a href="https://freessl.tech/terms-of-service" target="_blank">', '</a>' ) . "</i></p>" ;
        }
        ?>

          <?php 
        
        if ( $this->factory->is_cpanel() ) {
            ?>
              <hr />
              <table style="width: 100%; margin-top: 0%; margin-bottom: 2%;" id="">
                <tr>
                    <td class="card block-body" style="width: 50%; padding-left: 1.5%;">
                        <h3 style="text-align: center;"><?php 
            echo  __( "Setting up Automation for Free SSL Certificate is very easy!", 'auto-install-free-ssl' ) ;
            ?></h3>
                        <p style="color: green; text-align: center;"><?php 
            echo  __( "Video Tutorial for the Premium Version [1:42 Minute]", 'auto-install-free-ssl' ) ;
            ?></p>
                        <p style="text-align: center;"><?php 
            //echo __( "Starting from version 3.0.5, you can use the cPanel password or API Token. We'll provide a video: 'How to Create API Tokens in cPanel'.", 'auto-install-free-ssl' )
            ?></p>

                        <div style="padding:53.33% 0 0 0;position:relative;"><iframe src="https://player.vimeo.com/video/745390051?h=94ba682137&title=0&byline=0&portrait=0" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div><script src="https://player.vimeo.com/api/player.js"></script>
                    </td>
                </tr>
              </table>
          <?php 
        }
        
        ?>

          <!--  <br />
          <div class="card block-body" style="width: 100%; padding-left: 1.5%;">
             <p><?php 
        //echo sprintf(__( '%1$s discount code for you: %2$s', 'auto-install-free-ssl' ), "25%", "<strong>AUTOMATION</strong>")
        ?></p>
             <p><?php 
        //echo __( "Facing difficulties? Our Premium Version automatically generates free SSL certificates and installs & renews them.", 'auto-install-free-ssl' )
        ?></p>
             <p><i><?php 
        //echo __( "BONUS: we'll do the one-time setup for you.", 'auto-install-free-ssl' )
        ?></i> <a class="aifs-review-now aifs-review-button" style="margin-left: 5%;" href="<?php 
        //echo $this->factory->upgrade_url() . '&coupon=AUTOMATION'
        ?>"><?php 
        //echo __( "Grab the deal now!", 'auto-install-free-ssl' )
        ?></a></p>
          </div> -->
      </div>
        <?php 
    }
    
    /**
     * Plan comparison table handler
     *  @since 3.0.6
     */
    public function plan_comparison_table_handler()
    {
        
        if ( isset( $_GET['coupon'] ) ) {
            $coupon_code = $_GET['coupon'];
        } else {
            $coupon_code = false;
        }
        
        
        if ( isset( $_GET['aifsfree'] ) ) {
            //FREE plan selected
            if ( !wp_verify_nonce( $_GET['aifsfree'], 'aifs_free_plan' ) ) {
                wp_die( __( "Access denied", 'auto-install-free-ssl' ) );
            }
            update_option( 'aifs_free_plan_selected', 1 );
            wp_redirect( admin_url( 'admin.php?page=aifs_generate_ssl_manually' ) );
        } else {
            
            if ( isset( $_GET['aifspro'] ) ) {
                //PRO plan selected
                if ( !wp_verify_nonce( $_GET['aifspro'], 'aifs_pro_plan' ) ) {
                    wp_die( __( "Access denied", 'auto-install-free-ssl' ) );
                }
                update_option( 'aifs_premium_plan_selected', get_option( 'aifs_premium_plan_selected' ) + 1 );
                //wp_redirect(admin_url('admin.php?page=auto_install_free_ssl-pricing&checkout=true&plan_id=17218&plan_name=pro&billing_cycle=annual&pricing_id=19386&currency=usd'));
                wp_redirect( $this->factory->upgrade_url( $coupon_code, "checkout=true&plan_id=17218&plan_name=pro&billing_cycle=annual&pricing_id=19386" . (( $coupon_code ? "&hide_coupon=true&currency=usd" : "&currency=usd" )) ) );
            } else {
                
                if ( isset( $_GET['aifsprounlimited'] ) ) {
                    //PRO UNLIMITED plan selected
                    if ( !wp_verify_nonce( $_GET['aifsprounlimited'], 'aifs_pro_unlimited_plan' ) ) {
                        wp_die( __( "Access denied", 'auto-install-free-ssl' ) );
                    }
                    update_option( 'aifs_premium_plan_selected', get_option( 'aifs_premium_plan_selected' ) + 1 );
                    //wp_redirect(admin_url('admin.php?page=auto_install_free_ssl-pricing&checkout=true&plan_id=17218&plan_name=pro&billing_cycle=annual&pricing_id=19771&currency=usd'));
                    wp_redirect( $this->factory->upgrade_url( $coupon_code, "checkout=true&plan_id=17218&plan_name=pro&billing_cycle=annual&pricing_id=19771" . (( $coupon_code ? "&hide_coupon=true&currency=usd" : "&currency=usd" )) ) );
                }
            
            }
        
        }
    
    }
    
    /**
     * Log or stop logging all responses received from the Let's Encrypt™ server.
     * @since 3.6.2
     */
    public function log_all_ca_server_response_handler()
    {
        
        if ( isset( $_GET['log_all_ca_server_response'] ) ) {
            
            if ( aifs_is_free_version() && get_option( 'aifs_free_plan_selected' ) ) {
                $redirect_url = admin_url( 'admin.php?page=aifs_generate_ssl_manually' );
            } else {
                $redirect_url = admin_url( 'admin.php?page=auto_install_free_ssl' );
            }
            
            $common_text = __( "Settings successfully updated!", 'auto-install-free-ssl' ) . " ";
            if ( $_GET['log_all_ca_server_response'] == "yes" ) {
                
                if ( update_option( 'aifs_log_all_ca_server_response', 1 ) ) {
                    $success_text = $common_text . __( "We'll log all responses from the Let's Encrypt™ server.", 'auto-install-free-ssl' );
                    $this->logger->write_log( 'info', $success_text, [
                        'event' => 'ping',
                    ] );
                    aifs_add_flash_notice( $success_text );
                    wp_redirect( $redirect_url );
                    exit;
                }
            
            }
            if ( $_GET['log_all_ca_server_response'] == "no" ) {
                
                if ( delete_option( 'aifs_log_all_ca_server_response' ) ) {
                    $success_text = $common_text . __( "We have stopped logging all responses from the Let's Encrypt™ server.", 'auto-install-free-ssl' );
                    $this->logger->write_log( 'info', $success_text, [
                        'event' => 'ping',
                    ] );
                    aifs_add_flash_notice( $success_text );
                    wp_redirect( $redirect_url );
                    exit;
                }
            
            }
        }
    
    }
    
    /**
     * Display message after successful payment
     *
     */
    private function after_making_payment()
    {
        ?>
        <div class="wrap">

        <?php 
        echo  aifs_header() ;
        ?>

        <table style="width: 100%; height: 400px; margin-bottom: 2%;">
            <tr>
                <td class="card block-body" style="padding-top: 1%; padding-left: 2%;">
				    <?php 
        $heading = __( "Thank you for the purchase", 'auto-install-free-ssl' );
        $premium_download_link = aifssl_fs()->_get_latest_download_local_url();
        $contact_link = aifssl_fs()->contact_url();
        echo  '<h1 style="text-align: center;">' . $heading . '</h1>' ;
        echo  '<br /><h3 style="color: #076507; text-align: center;">' . __( "Now, please deactivate this FREE version. Then download, install & activate the premium version and enjoy!", 'auto-install-free-ssl' ) . '</h3>' ;
        echo  '<div style="text-align: left; margin-top: 3%; margin-left: 18%;">' ;
        echo  '<p>' . __( "If you missed downloading the Pro version of this plugin, you could download it using any of the following options", 'auto-install-free-ssl' ) . ':</p>' ;
        echo  '<ol>' ;
        echo  '<li><a href="' . $premium_download_link . '">' . __( "Download from this link.", 'auto-install-free-ssl' ) . '</a></li>' ;
        /* translators: %s: HTML code to create a hyperlink with the text 'Account page'. */
        echo  '<li>' . sprintf( __( "Download from the %sAccount page%s.", 'auto-install-free-ssl' ), '<a href="' . admin_url( 'admin.php?page=auto_install_free_ssl-account' ) . '">', '</a>' ) . '</li>' ;
        echo  '<li>' . __( "Download from the email we sent you after the purchase.", 'auto-install-free-ssl' ) . '</li>' ;
        echo  '</ol>' ;
        echo  '</div>' ;
        /* translators: %s: HTML code to make the text 'License Key' bold. */
        echo  '<p style="text-align: center;">' . sprintf( __( "The %sLicense Key%s has been provided in the same email.", 'auto-install-free-ssl' ), '<strong>', '</strong>' ) . '</p>' ;
        /* translators: %s: HTML code to create a hyperlink with the text 'Contact Us'. */
        echo  '<p style="text-align: center;">' . sprintf( __( "Any issues? %sContact Us%s right away. We'll be happy to help you.", 'auto-install-free-ssl' ), '<a href="' . $contact_link . '">', '</a>' ) . '</p>' ;
        ?>
                </td>
            </tr>
        </table>

        <?php 
        echo  aifs_powered_by() ;
        ?>
        </div>
    <?php 
    }
    
    public function countdown_js_script()
    {
        //wp_register_script('aifs_countdown', '');
        //wp_enqueue_script('aifs_countdown', false, [], false, true);
        return '// Set the date to countdown to (in this example, it is 1 hour from now)
var countDownDate = ' . (get_option( 'aifs_comparison_table_promo_start_time' ) + AIFS_COUNTDOWN_DURATION) . ' * 1000;

// Update the countdown every second
var countdownTimer = setInterval(function() {

  // Get the current time
  var now = new Date().getTime();

  // Calculate the distance between now and the countdown date
  var distance = countDownDate - now;

  // Calculate days, hours, minutes, and seconds
    var days = Math.floor(distance / (1000 * 60 * 60 * 24));    
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    // Display the countdown in the HTML element with ID "countdown"
    if (days > 1) {
      document.getElementById("countdown").innerHTML = days + " days & " + ("0" + hours).slice(-2) + ":" + ("0" + minutes).slice(-2) + ":" + ("0" + seconds).slice(-2);
    }
    else if (days > 0) {
      document.getElementById("countdown").innerHTML = days + " day & " + ("0" + hours).slice(-2) + ":" + ("0" + minutes).slice(-2) + ":" + ("0" + seconds).slice(-2);
    }
    else {
      document.getElementById("countdown").innerHTML = ("0" + hours).slice(-2) + ":" + ("0" + minutes).slice(-2) + ":" + ("0" + seconds).slice(-2);
    }

  // If the countdown is finished, clear the timer and display "EXPIRED" in the HTML element
  if (distance < 0) {
    clearInterval(countdownTimer);
    document.getElementById("aifs-promo").style.display = "none";
  }
}, 1000);';
        //wp_add_inline_script('aifs_countdown', $js_code);
    }

}