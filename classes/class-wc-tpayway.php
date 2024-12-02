<?php

/**
 * Class WC_TPAYWAY
 *
 * WC_TPAYWAY with API 2.0.9
 */
class WC_TPAYWAY extends WC_Payment_Gateway
{
    /**
     * WC_TPAYWAY constructor.
     */

    public $id;
    public $domain;
    public $icon;
    public $method_title;
    public $has_fields;
    public $curlExtension;
    public $ratehrkfixed;
    public $tecajnaHnbApi;
    public $api_version;
    public $title;
    public $settings;
    public $shop_id;
    public $acq_id;
    public $pg_domain;
    public $checkout_msg;
    public $description;
    public $msg;

    public function __construct()
    {

        $this->id = 'WC_TPAYWAY';
        $this->domain = 'woocommerce-tcom-payway';
        $this->icon = apply_filters('woocommerce_payway_icon', TCOM_PAYWAY_URL . 'assets/images/payway.png');
        $this->method_title = 'PayWay Hrvatski Telekom Woocommerce Payment Gateway';
        $this->has_fields = false;

        $this->curlExtension = extension_loaded('curl');

        $this->ratehrkfixed = 7.53450;
        $this->tecajnaHnbApi = "https://api.hnb.hr/tecajn-eur/v3";

        $this->api_version = '2.0';

        $this->init_form_fields();
        $this->init_settings();

        $settings = $this->settings;

        $this->title = isset($settings['title']) ? $settings['title'] : '';
        $this->shop_id = isset($settings['mer_id']) ? $settings['mer_id'] : '';
        $this->acq_id = isset($settings['acq_id']) ? $settings['acq_id'] : '';
        $this->pg_domain = $this->get_option('pg_domain');
        $this->checkout_msg = isset($settings['checkout_msg']) ? $settings['checkout_msg'] : '';
        $this->description = isset($settings['description']) ? $settings['description'] : '';

        $this->msg['message'] = '';
        $this->msg['class'] = '';

        add_action('init', array(&$this, 'check_tcompayway_response'));

        // Hook to load textdomain during 'init'
        add_action('init', array($this, 'load_textdomain'));

        if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
        } else {
            add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
        }
        add_action('woocommerce_receipt_WC_TPAYWAY', array(&$this, 'receipt_page'));

        $this->update_hnb_currency();
    }

    /**
     * Load plugin textdomain.
     */
    public function load_textdomain()
    {
        load_plugin_textdomain(
            'woocommerce-tcom-payway', // Textdomain
            false,            // Deprecated argument, set to false
            dirname(plugin_basename(__FILE__)) . '/languages/' // Path to language files
        );
    }


    function init_form_fields()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $this->form_fields = array(
            'enabled' => array(
                'title'       => __('Enable/Disable', $this->domain),
                'type'        => 'checkbox',
                'label'       => __('Enable PayWay Hrvatski Telekom Module.', 'woocommerce-tcom-payway'),
                'default'     => 'no',
            ),
            'title' => array(
                'title'       => __('Title:', $this->domain),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce-tcom-payway'),
                'default'     => __('PayWay Hrvatski Telekom', 'woocommerce-tcom-payway'),
            ),
            'description' => array(
                'title'       => __('Description:', $this->domain),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce-tcom-payway'),
                'default'     => __('Payway Hrvatski Telekom is a secure payment gateway in Croatia and you can pay using this payment in other currencies.', 'woocommerce-tcom-payway'),
            ),
            'pg_domain' => array(
                'title'       => __('Authorize URL', $this->domain),
                'type'        => 'select',
                'class'       => 'wc-enhanced-select',
                'description' => __('PayWay Hrvatski Telekom data submitting to this URL. Use Prod Mode to live set.', $this->domain),
                'default'     => 'prod',
                'desc_tip'    => true,
                'options'     => array(
                    'test' => __('Test Mode', 'woocommerce'),
                    'prod' => __('Prod Mode', 'woocommerce'),
                ),
            ),
            'mer_id' => array(
                'title'       => __('Shop ID:', $this->domain),
                'type'        => 'text',
                'description' => __('ShopID represents a unique identification of the web shop. ShopID is received from PayWay after signing the request for using PayWay service.', 'woocommerce-tcom-payway'),
                'default'     => '',
            ),
            'acq_id' => array(
                'title'       => __('Secret Key:', $this->domain),
                'type'        => 'password',
                'description' => '',
                'default'     => '',
            ),
            'checkout_msg' => array(
                'title'       => __('Message redirect:', $this->domain),
                'type'        => 'textarea',
                'description' => __('Message to client when redirecting to PayWay page', 'woocommerce-tcom-payway'),
                'default'     => 'Nakon potvrde biti ćete preusmjereni na plaćanje.',
            ),
        );
    }


    public function admin_options()
    {
        $hnbRatesUri = "<a href=\"" . esc_url($this->tecajnaHnbApi) . "\">HNB rates</a>";

        echo '<h3>' . __('PayWay Hrvatski Telekom payment gateway', 'woocommerce-tcom-payway') . '</h3>';
        echo '<p>' . __('<a target="_blank" href="https://www.hrvatskitelekom.hr/poslovni/ict/payway/">PayWay Hrvatski Telekom</a> is a payment gateway from Hrvatski Telekom that provides payment gateway services as dedicated services to clients in Croatia.', 'woocommerce-tcom-payway') . '</p>';
        echo '<table class="form-table">';
        $this->generate_settings_html();
        echo '</table>';
        echo '<p>';
        echo '<p>' . __('HNB rates fetched: ', 'woocommerce-tcom-payway') . $this->get_last_modified_hnb_file() . '</p>';
        echo '<p>' . esc_html__('Preferred currency will be EUR. Make sure that the default currency on your webshop is set to EUR.', 'woocommerce-tcom-payway') . '</p>';
        echo '<p>' . __('Other currencies will be automatically calculated and sent to PayWay using HNB rates: USD (WordPress) to HRK (PayWay) using ', 'woocommerce-tcom-payway') . $hnbRatesUri . '</p>';
        echo '</p>';
    }

    function payment_fields()
    {
        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }
    }

    function receipt_page($order)
    {
        echo $this->generate_ipg_form($order);
        echo '<br>' . esc_html($this->checkout_msg) . '</b>';
    }

    private function get_hnb_currency()
    {
        if (!$this->curlExtension) {
            return "CURL extension is missing. Conversion is disabled.";
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->tecajnaHnbApi);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            // Handle error if necessary
        }
        curl_close($ch);

        return $content;
    }

    /**
     *
     * Store HNB JSON locally
     *
     * Save HNB currency JSON not older than 7 days
     *
     * @return void
     */
    private function update_hnb_currency()
    {
        $file = __DIR__ . '/tecajnv2.json';

        if (!file_exists($file)) {
            file_put_contents($file, $this->get_hnb_currency());
        } else {
            clearstatcache();
            if (filesize($file)) {
                // If file is older than a day
                if (time() - filemtime($file) > (24 * 3600)) {
                    file_put_contents($file, $this->get_hnb_currency());
                }
            }
        }
    }

    private function get_last_modified_hnb_file()
    {
        $file = __DIR__ . '/tecajnv2.json';

        if (!$this->curlExtension) {
            return "HNB conversion is disabled due to missing CURL extension.";
        }

        if (file_exists($file)) {
            return date("d.m.Y H:i:s", filemtime($file));
        }

        return "HNB rates are not fetched from server.";
    }

    /**
     * Retrieve by currency conversion rate
     *
     * @return float|string
     */
    private function fetch_hnb_currency($currency)
    {
        $file = __DIR__ . '/tecajnv2.json';
        $filecontents = file_get_contents($file);

        $jsonFile = json_decode($filecontents, true);

        foreach ($jsonFile as $val) {
            if ($val['valuta'] === $currency) {
                return floatval(str_replace(",", ".", $val['srednji_tecaj']));
            }
        }

        return 1;
    }

    public function generate_ipg_form($order_id)
    {
        global $wpdb;

        $order = wc_get_order($order_id);
        // $productinfo = "Order $order_id";

        $currency_symbol = get_woocommerce_currency();
        $order_total    = $order->get_total();

        $table_name = $wpdb->prefix . 'tpayway_ipg';
        $check_order = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE transaction_id = %s", $order_id));
        if ($check_order > 0) {
            $wpdb->update(
                $table_name,
                array(
                    'response_code'      => '',
                    'response_code_desc' => '',
                    'reason_code'        => '',
                    'amount'             => $order_total,
                    'or_date'            => date('Y-m-d'),
                    'status'             => 0,
                ),
                array('transaction_id' => $order_id),
                array('%s', '%s'),
                array('%s')
            );
        } else {
            $wpdb->insert(
                $table_name,
                array(
                    'transaction_id'      => $order_id,
                    'response_code'       => '',
                    'response_code_desc'  => '',
                    'reason_code'         => '',
                    'amount'              => $order_total,
                    'or_date'             => date('Y-m-d'),
                    'status'              => '',
                ),
                array('%s', '%s', '%s', '%s', '%f', '%s', '%s')
            );
        }

        // Determine language based on customer country
        $pgw_language = $this->determine_language($order->get_billing_country());
        $pgw_language = strtoupper($pgw_language);

        // Handle currency conversion if necessary
        $wcml_settings = get_option('_wcml_settings'); // WooCommerce Multilingual - Multi Currency (WPML plugin)

        if (!$wcml_settings) {
            if ($currency_symbol === "EUR") {
                $order_total = $order->get_total();
            } else {
                if ($this->curlExtension) {
                    $order_total = $order->get_total() * $this->fetch_hnb_currency($currency_symbol);
                }
            }
        } else {
            $order_total = $order->get_total();
        }

        $order_format_value      = str_pad(($order_total * 100), 12, '0', STR_PAD_LEFT);
        $total_amount            = number_format($order_total, 2, '', '');
        $total_amount_request    = number_format($order_total, 2, ',', '');

        $secret_key    = $this->acq_id;    // Secret key
        $pgw_shop_id   = $this->shop_id;
        $pgw_order_id  = $order_id;
        $pgw_amount    = $total_amount;

        $pgw_first_name    = $order->get_billing_first_name();
        $pgw_last_name     = $order->get_billing_last_name();
        $pgw_street        = $order->get_billing_address_1() . ', ' . $order->get_billing_address_2();
        $pgw_city          = $order->get_billing_city();
        $pgw_post_code     = $order->get_billing_postcode();
        $pgw_country       = $order->get_billing_country();
        $pgw_telephone     = $order->get_billing_phone();
        $pgw_email         = $order->get_billing_email();

        $pgw_signature = hash('sha512', $pgw_shop_id . $secret_key . $pgw_order_id . $secret_key . $pgw_amount . $secret_key);

        $form_args = array(
            // Mandatory fields
            'ShopID'           => $pgw_shop_id,
            'ShoppingCartID'   => $pgw_order_id,
            'Version'          => $this->api_version,
            'TotalAmount'      => $total_amount_request,
            'ReturnURL'        => $this->get_return_url($order),
            'ReturnErrorURL'   => $order->get_cancel_order_url(),
            'CancelURL'        => $order->get_cancel_order_url(),
            'Signature'        => $pgw_signature,

            // Optional fields
            'Lang'             => $pgw_language,

            'CustomerFirstName'  => substr($pgw_first_name, 0, 50),
            'CustomerLastName'   => substr($pgw_last_name, 0, 50),
            'CustomerAddress'    => substr($pgw_street, 0, 100),
            'CustomerCity'       => substr($pgw_city, 0, 50),
            'CustomerZIP'        => substr($pgw_post_code, 0, 20),
            'CustomerCountry'    => $pgw_country,
            'CustomerEmail'      => substr($pgw_email, 0, 254),
            'CustomerPhone'      => substr($pgw_telephone, 0, 20),
            'ReturnMethod'       => 'POST',

            'acq_id'             => $this->acq_id, // Secret key
            'PurchaseAmt'        => $order_format_value,

            // ISO 4217
            'CurrencyCode'       => 978
        );

        $form_args_array = array();
        foreach ($form_args as $key => $value) {
            $form_args_array[] = "<input type='hidden' name='" . esc_attr($key) . "' value='" . esc_attr($value) . "'/>";
        }

        $pgDomain = 'https://form.payway.com.hr/authorization.aspx';
        if ($this->pg_domain === 'test') {
            $pgDomain = 'https://formtest.payway.com.hr/authorization.aspx';
        }

        return '<p></p>
            <p>Total amount will be <b>' . esc_html(number_format($order_total, 2)) . ' ' . esc_html($currency_symbol) . '</b></p>
            
            <form action="' . esc_url($pgDomain) . '" method="post" name="payway-authorize-form" id="payway-authorize-form" enctype="application/x-www-form-urlencoded">
                ' . implode('', $form_args_array) . '
                <input type="submit" class="button-alt" id="submit_ipg_payment_form" value="' . esc_attr__('Pay via PayWay', 'woocommerce-tcom-payway') . '" />
                <a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">' . esc_html__('Cancel order &amp; restore cart', 'woocommerce-tcom-payway') . '</a>
            </form>
            <!-- autoform submit -->
            <script type="text/javascript">
                jQuery("#submit_ipg_payment_form").trigger("click");
            </script>
        ';
    }

    private function determine_language($country_code)
    {
        $languages = array(
            'HR',
            'SR',
            'SL',
            'BS',
            'CG',
            'DE',
            'IT',
            'FR',
            'NL',
            'HU',
            'RU',
            'SK',
            'CZ',
            'PL',
            'PT',
            'ES',
            'BG',
            'RO',
            'EL',
        );

        return in_array($country_code, $languages) ? strtolower($country_code) : 'en';
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        return array(
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url(true),
        );
    }

    function get_response_codes($id)
    {
        $id = (int)$id;

        $res = array(
            0    => __('Action successful', 'woocommerce-tcom-payway'),
            15   => __('User cancelled the transaction by himself', 'woocommerce-tcom-payway'),
            16   => __('Transaction cancelled due to timeout', 'woocommerce-tcom-payway'),

            // Deprecated, refactoring
            1    => __('Action unsuccessful', 'woocommerce-tcom-payway'),
            2    => __('Error processing', 'woocommerce-tcom-payway'),
            3    => __('Action cancelled', 'woocommerce-tcom-payway'),
            4    => __('Action unsuccessful (3D Secure MPI)', 'woocommerce-tcom-payway'),
            1000 => __('Incorrect signature (pgw_signature)', 'woocommerce-tcom-payway'),
            1001 => __('Incorrect store ID (pgw_shop_id)', 'woocommerce-tcom-payway'),
            1002 => __('Incorrect transaction ID (pgw_transaction_id)', 'woocommerce-tcom-payway'),
            1003 => __('Incorrect amount (pgw_amount)', 'woocommerce-tcom-payway'),
            1004 => __('Incorrect authorization_type (pgw_authorization_type)', 'woocommerce-tcom-payway'),
            1005 => __('Incorrect announcement duration (pgw_announcement_duration)', 'woocommerce-tcom-payway'),
            1006 => __('Incorrect installments number (pgw_installments)', 'woocommerce-tcom-payway'),
            1007 => __('Incorrect language (pgw_language)', 'woocommerce-tcom-payway'),
            1008 => __('Incorrect authorization token (pgw_authorization_token)', 'woocommerce-tcom-payway'),
            1100 => __('Incorrect card number (pgw_card_number)', 'woocommerce-tcom-payway'),
            1101 => __('Incorrect card expiration date (pgw_card_expiration_date)', 'woocommerce-tcom-payway'),
            1102 => __('Incorrect card verification data (pgw_card_verification_data)', 'woocommerce-tcom-payway'),
            1200 => __('Incorrect order ID (pgw_order_id)', 'woocommerce-tcom-payway'),
            1201 => __('Incorrect order info (pgw_order_info)', 'woocommerce-tcom-payway'),
            1202 => __('Incorrect order items (pgw_order_items)', 'woocommerce-tcom-payway'),
            1300 => __('Incorrect return method (pgw_return_method)', 'woocommerce-tcom-payway'),
            1301 => __('Incorrect success store url (pgw_success_url)', 'woocommerce-tcom-payway'),
            1302 => __('Incorrect error store url (pgw_failure_url)', 'woocommerce-tcom-payway'),
            1304 => __('Incorrect merchant data (pgw_merchant_data)', 'woocommerce-tcom-payway'),
            1400 => __('Incorrect buyer\'s name (pgw_first_name)', 'woocommerce-tcom-payway'),
            1401 => __('Incorrect buyer\'s last name (pgw_last_name)', 'woocommerce-tcom-payway'),
            1402 => __('Incorrect address (pgw_street)', 'woocommerce-tcom-payway'),
            1403 => __('Incorrect city (pgw_city)', 'woocommerce-tcom-payway'),
            1404 => __('Incorrect ZIP code (pgw_post_code)', 'woocommerce-tcom-payway'),
            1405 => __('Incorrect country (pgw_country)', 'woocommerce-tcom-payway'),
            1406 => __('Incorrect contact phone (pgw_telephone)', 'woocommerce-tcom-payway'),
            1407 => __('Incorrect contact e-mail address (pgw_email)', 'woocommerce-tcom-payway'),
        );

        return isset($res[$id]) ? $res[$id] : __('Unknown response code', 'woocommerce-tcom-payway');
    }

    function check_tcompayway_response()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return;
            }
        }

        if (!isset($_POST['ShoppingCartID'])) {
            return;
        }

        $order_id     = sanitize_text_field($_POST['ShoppingCartID']);
        $order        = wc_get_order($order_id);
        $amount       = $this->sanitize('Amount');
        $status       = isset($_POST['Success']) ? (int)$_POST['Success'] : 0;
        $reasonCode   = isset($_POST['ApprovalCode']) ? (int)$_POST['ApprovalCode'] : 0;

        // Return URL
        if (!empty($_POST['ApprovalCode']) && isset($_POST['Success']) && isset($_POST['Signature'])) {
            if ((int)$_POST['Success'] === 1) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'tpayway_ipg';
                $wpdb->update(
                    $table_name,
                    array(
                        'response_code'      => $status,
                        'response_code_desc' => $this->get_response_codes(0),
                        'reason_code'        => $reasonCode,
                        'status'             => 1,
                    ),
                    array('transaction_id' => $order_id),
                    array('%d', '%s', '%d', '%d'),
                    array('%s')
                );

                $order_note = __('PayWay Hrvatski Telekom payment successful. Unique Id: ', 'woocommerce-tcom-payway') . $order_id;
                $order->add_order_note(esc_html($order_note));
                WC()->cart->empty_cart();

                // Mark as on-hold (we're awaiting the payment).
                $order->update_status('pending', __('Awaiting payment', 'woocommerce-tcom-payway'));

                $mailer      = WC()->mailer();
                $admin_email = get_option('admin_email', '');

                $message = $mailer->wrap_message(
                    __('Payment successful', 'woocommerce-tcom-payway'),
                    sprintf(
                        __('Payment on PayWay Hrvatski Telekom is successfully completed and order status is processed.', 'woocommerce-tcom-payway'),
                        $order->get_order_number()
                    )
                );
                $mailer->send(
                    $admin_email,
                    sprintf(
                        __('Payment for order no. %s was successful.', 'woocommerce-tcom-payway'),
                        $order->get_order_number()
                    ),
                    $message
                );

                $order->payment_complete();

                wp_redirect($this->get_return_url($order));
                exit;
            }
        }

        if (isset($_POST['Success'])) {
            if (isset($_POST['ResponseCode'])) {
                $responseCode = (int)$_POST['ResponseCode'];
                if ($responseCode === 15 || $responseCode === 16) {

                    $order->add_order_note($this->get_response_codes($responseCode) . " (Code $responseCode)");
                    $order->update_status('cancelled');
                    WC()->cart->empty_cart();

                    global $wpdb;
                    $table_name = $wpdb->prefix . 'tpayway_ipg';
                    $wpdb->update(
                        $table_name,
                        array(
                            'response_code'      => $responseCode,
                            'response_code_desc' => $this->get_response_codes($responseCode),
                            'reason_code'        => 0,
                            'status'             => 0,
                        ),
                        array('transaction_id' => $order_id),
                        array('%d', '%s', '%d', '%d'),
                        array('%s')
                    );

                    $text = '<html><meta charset="utf-8"><body><center>';
                    $text .= esc_html__('A payment was not cancelled', 'woocommerce-tcom-payway') . '<br>';
                    $text .= esc_html__('Reason: ', 'woocommerce-tcom-payway') . esc_html($this->get_response_codes($responseCode)) . '<br>';
                    $text .= esc_html__('Order Id: ', 'woocommerce-tcom-payway') . esc_html($order_id) . '<br>';
                    $text .= esc_html__('Redirecting...', 'woocommerce-tcom-payway');
                    $text .= '</center><script>setTimeout(function(){ window.location.replace("' . esc_js($order->get_cancel_order_url()) . '"); },3000);</script></body></html>';

                    echo $text;

                    exit;
                }
            }

            if ($_POST['Success'] === "0") {
                $errorCodes = isset($_POST['ErrorCodes']) ? sanitize_text_field(json_encode($_POST['ErrorCodes'])) : '';

                $order->update_status('failed');
                $order->add_order_note($this->get_response_codes($reasonCode) . " (Code $reasonCode)");
                WC()->cart->empty_cart();

                global $wpdb;
                $table_name = $wpdb->prefix . 'tpayway_ipg';
                $wpdb->update(
                    $table_name,
                    array(
                        'response_code'      => 0,
                        'response_code_desc' => $errorCodes,
                        'reason_code'        => 0,
                        'status'             => 0,
                    ),
                    array('transaction_id' => $order_id),
                    array('%d', '%s', '%d', '%d'),
                    array('%s')
                );

                $text = '<html><meta charset="utf-8"><body><center>';
                $text .= esc_html__('A payment was not successful or declined', 'woocommerce-tcom-payway') . '<br>';
                $text .= esc_html__('Reason: ', 'woocommerce-tcom-payway') . esc_html($errorCodes) . '<br>';
                $text .= esc_html__('Order Id: ', 'woocommerce-tcom-payway') . esc_html($order_id) . '<br>';
                $text .= esc_html__('Redirecting...', 'woocommerce-tcom-payway');
                $text .= '</center><script>setTimeout(function(){ window.location.replace("' . esc_js($order->get_cancel_order_url()) . '"); },3000);</script></body></html>';

                echo $text;

                exit;
            }
        }
    }

    function get_pages($title = false, $indent = true)
    {
        $wp_pages   = get_pages('sort_column=menu_order');
        $page_list  = array();
        if ($title) {
            $page_list[] = $title;
        }
        foreach ($wp_pages as $page) {
            $prefix = '';
            if ($indent) {
                $has_parent = $page->post_parent;
                while ($has_parent) {
                    $prefix .= ' - ';
                    $next_page = get_page($has_parent);
                    $has_parent = $next_page->post_parent;
                }
            }
            $page_list[$page->ID] = $prefix . $page->post_title;
        }
        return $page_list;
    }

    public function sanitize(string $data): string
    {
        return strip_tags(
            stripslashes(
                sanitize_text_field(
                    filter_input(INPUT_POST, $data)
                )
            )
        );
    }
}
