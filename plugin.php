<?php 
/**
 * Plugin Name: UserAccount
 * Plugin URI: https://github.com/uptimizt/user-account
 * Description: personal account on the site by WordPress. shortcode [user-account]
 * Author: WPCraft
 * Author URI: https://wpcraft.ru/
 * Developer: WPCraft
 * Developer URI: https://wpcraft.ru/
 * Text Domain: useraccount
 * Domain Path: /languages
 * PHP requires at least: 5.6
 * WP requires at least: 5.0
 * Tested up to: 5.6
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Version: 0.1
 */

namespace UserAccount;

class Core {

    public static function init(){

        add_shortcode('user-account', [__CLASS__, 'render_shortcode']);

        add_action('user_account_navigation', [__CLASS__, 'render_nav']);
        add_action('user_account_content', [__CLASS__, 'account_content']);

    }

    public static function render_nav(){
        do_action( 'user_before_account_navigation' );
        ?>

        <nav class="user-account-navigation">
            <ul>
                <?php foreach ( self::get_account_menu_items() as $endpoint => $label ) : ?>
                    <li class="<?php echo self::get_account_menu_item_classes( $endpoint ); ?>">
                        <a href="<?php echo esc_url( self::get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <?php 
        do_action( 'user_after_account_navigation' );
    }


    /**
     * Get account endpoint URL.
     *
     * @param string $endpoint Endpoint.
     * @return string
     */
    function get_account_endpoint_url( $endpoint ) {
        if ( 'dashboard' === $endpoint ) {
            return self::get_account_page_permalink();
        }

        if ( 'customer-logout' === $endpoint ) {
            return wp_logout_url();
        }

        return self::get_account_page_permalink() . $endpoint;
    }


    	/**
	 * My Account content output.
	 */
	function account_content() {
		global $wp;

		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key ) {
					continue;
				}

				if ( has_action( 'user_account_' . $key . '_endpoint' ) ) {
					do_action( 'user_account_' . $key . '_endpoint', $value );
					return;
				}
			}
		}

		// No endpoint found? Default to dashboard.
		echo 'dasboard content';
    }
    
    public static function get_account_page_permalink(){
        //todo - make
        return 'https://ya.ru';
    }


    public static function render_shortcode(){

        do_action( 'user_account_navigation' ); ?>

        <div class="user-account-content-wrapper">
            <?php
                /**
                 * My Account content.
                 *
                 * @since 2.6.0
                 */
                do_action( 'user_account_content' );
            ?>
        </div>
        <?php 
    }


    /**
     * Get account menu item classes.
     *
     * @param string $endpoint Endpoint.
     * @return string
     */
    function get_account_menu_item_classes( $endpoint ) {
        global $wp;

        $classes = array(
            'woocommerce-MyAccount-navigation-link',
            'woocommerce-MyAccount-navigation-link--' . $endpoint,
        );

        // Set current item class.
        $current = isset( $wp->query_vars[ $endpoint ] );
        if ( 'dashboard' === $endpoint && ( isset( $wp->query_vars['page'] ) || empty( $wp->query_vars ) ) ) {
            $current = true; // Dashboard is not an endpoint, so needs a custom check.
        } elseif ( 'orders' === $endpoint && isset( $wp->query_vars['view-order'] ) ) {
            $current = true; // When looking at individual order, highlight Orders list item (to signify where in the menu the user currently is).
        } elseif ( 'payment-methods' === $endpoint && isset( $wp->query_vars['add-payment-method'] ) ) {
            $current = true;
        }

        if ( $current ) {
            $classes[] = 'is-active';
        }

        $classes = apply_filters( 'woocommerce_account_menu_item_classes', $classes, $endpoint );

        return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
    }



    /**
     * Get My Account menu items.
     *
     * @return array
     */
    public static function get_account_menu_items() {
        $endpoints = array(
            'edit-account'    => get_option( 'user_myaccount_edit_account_endpoint', 'edit-account' ),
            'customer-logout' => get_option( 'user_logout_endpoint', 'customer-logout' ),
        );

        $items = array(
            'dashboard'       => __( 'Dashboard', 'useraccount' ),
            'edit-account'    => __( 'Account details', 'useraccount' ),
            'customer-logout' => __( 'Logout', 'useraccount' ),
        );

        // Remove missing endpoints.
        foreach ( $endpoints as $endpoint_id => $endpoint ) {
            if ( empty( $endpoint ) ) {
                unset( $items[ $endpoint_id ] );
            }
        }

        return apply_filters( 'user_account_menu_items', $items, $endpoints );
    }
}

Core::init();
