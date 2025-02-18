<?php

namespace EmbedPress\Includes\Traits;


if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
use \EmbedPress\Includes\Classes\EmbedPress_Plugin_Usage_Tracker;
use \EmbedPress\Includes\Classes\EmbedPress_Notice;

use PriyoMukul\WPNotice\Notices;

use PriyoMukul\WPNotice\Utils\CacheBank;
use PriyoMukul\WPNotice\Utils\NoticeRemover;

trait Shared
{

    private $insights = null;

    /**
     * @var CacheBank
     */
    private static $cache_bank;


    /**
     * Optional usage tracker
     *
     * @since v1.0.0
     */
    public function start_plugin_tracking()
    {
        $this->insights = $tracker = EmbedPress_Plugin_Usage_Tracker::get_instance(EMBEDPRESS_FILE, [
            'opt_in'       => true,
            'goodbye_form' => true,
            'item_id'      => '98ba0ac16a4f7b3b940d'
        ]);
        $tracker->set_notice_options(array(
            'notice' => __('Want to help make <strong>EmbedPress</strong> even more awesome? You can get a <strong>10% discount coupon</strong> for Premium extensions if you allow us to track the usage.', 'embedpress'),
            'extra_notice' => __('We collect non-sensitive diagnostic data and plugin usage information.
            Your site URL, WordPress & PHP version, plugins & themes and email address to send you the
            discount coupon. This data lets us make sure this plugin always stays compatible with the most
            popular plugins and themes. No spam, I promise.', 'embedpress'),
        ));
        $tracker->init();
    }

    public function admin_notice()
    {

        self::$cache_bank = CacheBank::get_instance();

        try {
            $this->notices();
        } catch (\Exception $e) {
            unset($e);
        }

        // Remove OLD notice from 1.0.0 (if other WPDeveloper plugin has notice)
        NoticeRemover::get_instance('1.0.0');
    }

    public function notices()
    {
        $_assets_url = plugins_url('assets/', EMBEDPRESS_PLUGIN_BASENAME);

        $notices = new Notices([
            // 'dev_mode'       => true,
            'id'             => 'embedpress',
            'storage_key'    => 'notices',
            'lifetime'       => 3,
            'stylesheet_url' => $_assets_url . 'css/admin-notices.css',
            'styles'         => $_assets_url . 'css/admin-notices.css',
            'priority'       => 6
        ]);

        /**
         * This is review message and thumbnail.
         */
        $_review_notice = [
            'thumbnail' => $_assets_url . 'images/icon-128x128.png',
            'html' => '<p>' . __('We hope you\'re enjoying EmbedPress! Could you please do us a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'embedpress') . '</p>',
            'links' => [
                'later' => array(
                    'link'       => 'https://wordpress.org/support/plugin/embedpress/reviews/',
                    'target'     => '_blank',
                    'label'      => __('Ok, you deserve it!', 'embedpress'),
                    'icon_class' => 'dashicons dashicons-external',
                ),
                'allready' => array(
                    'label' => __('I already did', 'embedpress'),
                    'icon_class' => 'dashicons dashicons-smiley',
                    'attributes' => [
                        'data-dismiss' => true
                    ],
                ),
                'maybe_later' => array(
                    'label' => __('Maybe Later', 'embedpress'),
                    'icon_class' => 'dashicons dashicons-calendar-alt',
                    'attributes' => [
                        'data-later' => true
                    ],
                ),
                'support' => array(
                    'link' => 'https://wpdeveloper.com/support',
                    'label' => __('I need help', 'embedpress'),
                    'icon_class' => 'dashicons dashicons-sos',
                ),
                'never_show_again' => array(
                    'label' => __('Never show again', 'embedpress'),
                    'icon_class' => 'dashicons dashicons-dismiss',
                    'attributes' => [
                        'data-dismiss' => true
                    ],
                ),
            ],
        ];

        $notices->add(
            'review',
            $_review_notice,
            [
                'start'       => $notices->strtotime('+15 day'),
                'recurrence'  => 30,
                'dismissible' => true,
                'refresh'     => EMBEDPRESS_VERSION,
            ]
        );

        $notices->add(
            'optin',
            [$this->insights, 'notice'],
            [
                'start'       => $notices->strtotime('+10 day'),
                'recurrence'  => 30,
                'dismissible' => true,
                'refresh'     => EMBEDPRESS_VERSION,
                'do_action'   => 'wpdeveloper_notice_clicked_for_embedpress',
                'display_if'  => !is_array($notices->is_installed('embedpress-pro/embedpress-pro.php'))
            ]
        );

        // $b_message            = '<p style="margin-top: 0; margin-bottom: 10px;">Black Friday Sale: Save up to 40% now & <strong>embed from 150+</strong> sources with advanced features ⚡</p><a class="button button-primary" href="https://wpdeveloper.com/upgrade/embedpress-bfcm" target="_blank">Upgrade to PRO</a> <button data-dismiss="true" class="dismiss-btn button button-link">I don’t want to save money</button>';
        // $_black_friday_notice = [
        //     'thumbnail' => $_assets_url . 'images/full-logo.svg',
        //     'html'      => $b_message,
        // ];

        // $notices->add(
        //     'black_friday_notice',
        //     $_black_friday_notice,
        //     [
        //         'start'       => $notices->time(),
        //         'recurrence'  => false,
        //         'dismissible' => true,
        //         'refresh'     => EMBEDPRESS_VERSION,
        //         "expire"      => strtotime('11:59:59pm 2nd December, 2023'),
        //         'display_if'  => !is_plugin_active('embedpress-pro/embedpress-pro.php')
        //     ]
        // );

        // $b_message            = '<p style="margin-top: 0; margin-bottom: 10px;"><strong>Akah  Join Us in Celebrating 100K+ Users!</strong> Enjoy up to 30% OFF for EmbedPress PRO & embed from 150+ sources</p><a class="button button-primary" href="https://wpdeveloper.com/upgrade/embedpress-bfcm" target="_blank">Upgrade to PRO</a> <button data-dismiss="true" class="dismiss-btn button button-link">I don’t want to save money</button>';
        // $_black_friday_notice = [
        //     'thumbnail' => $_assets_url . 'images/full-logo.svg',
        //     'html'      => $b_message,
        // ];

        // $notices->add(
        //     '100k_notice',
        //     $_black_friday_notice,
        //     [
        //         'start'       => $notices->time(),
        //         'recurrence'  => false,
        //         'dismissible' => true,
        //         'refresh'     => EMBEDPRESS_VERSION,
        //         "expire"      => strtotime('11:59:59pm 12th September, 2024'),
        //         'display_if'  => !is_plugin_active('embedpress-pro/embedpress-pro.php')
        //     ]
        // );

        $b_message            = '<div class="helloween_2024_notice"><p style="margin-top: 0; margin-bottom: 0px;">🎃 Unlock advanced embedding functionalities with EmbedPress PRO & enjoy <strong>Up to $150 Off</strong> this Halloween.</p><a class="button button-primary" href="https://embedpress.com/halloween-2024/" target="_blank">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m15.743 10.938.161-1.578c.086-.842.142-1.399.098-1.749h.015c.727 0 1.316-.622 1.316-1.389s-.589-1.389-1.315-1.389c-.727 0-1.316.622-1.316 1.39 0 .346.12.663.32.907-.287.186-.66.58-1.223 1.171-.434.456-.65.684-.893.72a.7.7 0 0 1-.394-.059c-.223-.104-.372-.385-.67-.95l-1.57-2.97a22 22 0 0 0-.476-.873c.569-.306.958-.93.958-1.65C10.754 1.496 9.97.667 9 .667s-1.754.829-1.754 1.852c0 .72.389 1.344.958 1.65-.139.234-.293.525-.476.873l-1.57 2.97c-.298.565-.447.846-.67.95a.7.7 0 0 1-.394.058c-.242-.035-.46-.263-.893-.719-.563-.592-.937-.985-1.223-1.171.2-.244.32-.56.32-.908 0-.767-.589-1.389-1.316-1.389-.726 0-1.315.622-1.315 1.39 0 .766.589 1.388 1.315 1.388h.016c-.045.35.012.906.098 1.749l.16 1.578c.09.876.164 1.71.255 2.46H15.49c.09-.75.165-1.584.254-2.46m-7.698 6.395h1.908c2.488 0 3.732 0 4.562-.784.362-.342.591-.959.757-1.762H2.727c.166.803.395 1.42.757 1.762.83.784 2.074.784 4.562.784" fill="#fff"/></svg> Upgrade to PRO</a></div>';
        $_helloween_2024_notice = [
            'thumbnail' => $_assets_url . 'images/full-logo.svg',
            'html'      => $b_message,
        ];


        $notices->add(
            'helloween_2024_notice',
            $_helloween_2024_notice,
            [
                'start'       => $notices->time(),
                'recurrence'  => false,
                'dismissible' => true,
                'refresh'     => EMBEDPRESS_VERSION,
                "expire"      => strtotime('11:59:59pm 3rd November, 2024'),
                'display_if' => !is_plugin_active('embedpress-pro/embedpress-pro.php') && ($_SERVER['REQUEST_URI'] === '/wp-admin/' || $_SERVER['REQUEST_URI'] === '/wp-admin/index.php'),
            ]
        );

        $king_icon = '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m15.743 10.938.161-1.578c.086-.842.142-1.399.098-1.749h.015c.727 0 1.316-.622 1.316-1.389s-.589-1.389-1.315-1.389c-.727 0-1.316.622-1.316 1.39 0 .346.12.663.32.907-.287.186-.66.58-1.223 1.171-.434.456-.65.684-.893.72a.7.7 0 0 1-.394-.059c-.223-.104-.372-.385-.67-.95l-1.57-2.97a22 22 0 0 0-.476-.873c.569-.306.958-.93.958-1.65C10.754 1.496 9.97.667 9 .667s-1.754.829-1.754 1.852c0 .72.389 1.344.958 1.65-.139.234-.293.525-.476.873l-1.57 2.97c-.298.565-.447.846-.67.95a.7.7 0 0 1-.394.058c-.242-.035-.46-.263-.893-.719-.563-.592-.937-.985-1.223-1.171.2-.244.32-.56.32-.908 0-.767-.589-1.389-1.316-1.389-.726 0-1.315.622-1.315 1.39 0 .766.589 1.388 1.315 1.388h.016c-.045.35.012.906.098 1.749l.16 1.578c.09.876.164 1.71.255 2.46H15.49c.09-.75.165-1.584.254-2.46m-7.698 6.395h1.908c2.488 0 3.732 0 4.562-.784.362-.342.591-.959.757-1.762H2.727c.166.803.395 1.42.757 1.762.83.784 2.074.784 4.562.784" fill="#fff"/></svg>';

        $b_friday_message = '<div class="black_friday_2024_notice"><p class="notice-message">🔒 Unlock advanced embedding functionalities with EmbedPress PRO & enjoy <strong>up to %40 Off</strong> this Black Friday.</p>
        <div class="notice-links">
            <a class="button button-primary" href="https://embedpress.com/bfcm24-pricing" target="_blank">
        ' . $king_icon . ' Upgrade to PRO</a> 
            <a class="full-price-link" href="https://embedpress.com/bfcm24-pricing" target="_blank">No, I prefer to pay full price</a>
        </div>
        </div>';
        $_black_friday_2024_notice = [
            'thumbnail' => $_assets_url . 'images/full-logo.svg',
            'html'      => $b_friday_message,
        ];

        $notices->add(
            'black_friday_2024_notice',
            $_black_friday_2024_notice,
            [
                'start'       => $notices->time(),
                'recurrence'  => false,
                'dismissible' => true,
                'refresh'     => EMBEDPRESS_VERSION,
                "expire"      => strtotime('11:59:59pm 5th December, 2024'),
                'display_if' => !is_plugin_active('embedpress-pro/embedpress-pro.php') && ($_SERVER['REQUEST_URI'] === '/wp-admin/' || $_SERVER['REQUEST_URI'] === '/wp-admin/index.php'),
            ]
        );

        $holiday_message = '<div class="holiday_2024_notice"><p class="notice-message">🎁 <strong>SAVE 25% now</strong> & unlock advanced embedding functionalities from 150+ multi-media sources in 2025.</p>
        <div class="notice-links">
            <a class="button button-primary" href="https://embedpress.com/holiday24-admin-notice" target="_blank">
        ' . $king_icon . ' GET PRO Lifetime Access</a> 
            <a class="embedpress-notice-dismiss-button dismiss-btn" data-dismiss="true" href="#" target="_blank">No, I’ll Pay Full Price Later</a>
            
        </div>
        </div>';
        $_holiday_2024_notice = [
            'thumbnail' => $_assets_url . 'images/full-logo.svg',
            'html'      => $holiday_message,
        ];

        $notices->add(
            'holiday_2024_notice',
            $_holiday_2024_notice,
            [
                'start'       => $notices->time(),
                'recurrence'  => false,
                'dismissible' => true,
                'refresh'     => EMBEDPRESS_VERSION,
                "expire"      => strtotime('11:59:59pm 10th January, 2025'),
                'display_if' => !is_plugin_active('embedpress-pro/embedpress-pro.php') && ($_SERVER['REQUEST_URI'] === '/wp-admin/' || $_SERVER['REQUEST_URI'] === '/wp-admin/index.php'),
            ]
        );

        $notices->init();

        self::$cache_bank->create_account($notices);
        self::$cache_bank->calculate_deposits($notices);
    }

    public function is_pro_active()
    {
        return is_plugin_active('embedpress-pro/embedpress-pro.php');
    }

    /**
     * Show Admin notice when one of embedpress old plugin active
     *
     * @since  2.4.0
     */
    public function embedpress_admin_notice()
    {
        $compatibility_message = '<p style="margin-top: 0; margin-bottom: 0px;"><strong style="color:#FF7369;">Action Needed:</strong> Please update <strong>EmbedPress Pro</strong> to the latest version (<strong>v3.6.5</strong>) for enhanced features and compatibility.</p>';


        if (is_plugin_active('embedpress-pro/embedpress-pro.php') && version_compare(EMBEDPRESS_PRO_PLUGIN_VERSION, '3.6.5', '<')) {
            echo '<div class="notice notice-warning">' . $compatibility_message . '</div>';
        }
    }
}
