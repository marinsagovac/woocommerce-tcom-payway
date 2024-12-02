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
        // Payment Gateway Settings
        $this->id = 'WC_TPAYWAY';
        $this->domain = 'woocommerce-tcom-payway';
        $this->icon = apply_filters('woocommerce_payway_icon', TCOM_PAYWAY_URL . 'assets/images/payway.png');
        $this->method_title = 'PayWay Hrvatski Telekom WooCommerce Payment Gateway';
        $this->has_fields = false;

        // Extension check
        $this->curlExtension = extension_loaded('curl');

        // Default conversion rate and API for HNB
        $this->ratehrkfixed = 7.53450;
        $this->tecajnaHnbApi = "https://api.hnb.hr/tecajn-eur/v3";

        // API version
        $this->api_version = '2.0';

        // Initialize settings
        $this->init_form_fields();
        $this->init_settings();

        // Get settings
        $settings = $this->settings;
        $this->title = $this->get_option('title', '');
        $this->shop_id = $this->get_option('mer_id', '');
        $this->acq_id = $this->get_option('acq_id', '');
        $this->pg_domain = $this->get_option('pg_domain', 'test');
        $this->checkout_msg = $this->get_option('checkout_msg', '');
        $this->description = $this->get_option('description', '');

        // Message initialization
        $this->msg['message'] = '';
        $this->msg['class'] = '';

        // Hook into WooCommerce
        add_action('init', array($this, 'check_tcompayway_response'));
        add_action('init', array($this, 'load_textdomain'));

        // Update settings on WooCommerce version >= 2.0
        if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        } else {
            add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
        }

        // Receipt page hook
        add_action('woocommerce_receipt_WC_TPAYWAY', array($this, 'receipt_page'));

        // Update HNB currency (ensure the file is up-to-date)
        $this->update_hnb_currency();
    }


    /**
     * Load plugin textdomain.
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('woocommerce-tcom-payway', false, plugin_dir_path(__FILE__) . 'languages');
    }



    public function init_form_fields()
    {
        // Define form fields with proper escaping
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __('Enable/Disable', 'woocommerce-tcom-payway'),
                'type'        => 'checkbox',
                'label'       => __('Enable PayWay Hrvatski Telekom Module.', 'woocommerce-tcom-payway'),
                'default'     => 'no',
            ),
            'title' => array(
                'title'       => __('Title:', 'woocommerce-tcom-payway'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce-tcom-payway'),
                'default'     => __('PayWay Hrvatski Telekom', 'woocommerce-tcom-payway'),
            ),
            'description' => array(
                'title'       => __('Description:', 'woocommerce-tcom-payway'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce-tcom-payway'),
                'default'     => __('Payway Hrvatski Telekom is a secure payment gateway in Croatia and you can pay using this payment in other currencies.', 'woocommerce-tcom-payway'),
            ),
            'pg_domain' => array(
                'title'       => __('Authorize URL', 'woocommerce-tcom-payway'),
                'type'        => 'select',
                'class'       => 'wc-enhanced-select',
                'description' => __('PayWay Hrvatski Telekom data submitting to this URL. Use Prod Mode to live set.', 'woocommerce-tcom-payway'),
                'default'     => 'prod',
                'desc_tip'    => true,
                'options'     => array(
                    'test' => __('Test Mode', 'woocommerce-tcom-payway'),
                    'prod' => __('Prod Mode', 'woocommerce-tcom-payway'),
                ),
            ),
            'mer_id' => array(
                'title'       => __('Shop ID:', 'woocommerce-tcom-payway'),
                'type'        => 'text',
                'description' => __('ShopID represents a unique identification of the web shop. ShopID is received from PayWay after signing the request for using PayWay service.', 'woocommerce-tcom-payway'),
                'default'     => '',
            ),
            'acq_id' => array(
                'title'       => __('Secret Key:', 'woocommerce-tcom-payway'),
                'type'        => 'password',
                'description' => '',
                'default'     => '',
            ),
            'checkout_msg' => array(
                'title'       => __('Message redirect:', 'woocommerce-tcom-payway'),
                'type'        => 'textarea',
                'description' => __('Message to client when redirecting to PayWay page', 'woocommerce-tcom-payway'),
                'default'     => 'Nakon potvrde biti ćete preusmjereni na plaćanje.',
            ),
        );
    }

    public function admin_options()
    {
        $hnbRatesUri = "<a href=\"" . esc_url($this->tecajnaHnbApi) . "\" target=\"_blank\">" . esc_html__('HNB rates', 'woocommerce-tcom-payway') . "</a>";

        echo '<h3>' . esc_html__('PayWay Hrvatski Telekom payment gateway', 'woocommerce-tcom-payway') . '</h3>';
        echo '<p>' . esc_html__('PayWay Hrvatski Telekom is a payment gateway from Hrvatski Telekom that provides payment gateway services as dedicated services to clients in Croatia.', 'woocommerce-tcom-payway') . '</p>';
        echo '<table class="form-table">';
        $this->generate_settings_html();
        echo '</table>';
        echo '<p>';
        echo '<p>' . esc_html__('HNB rates fetched: ', 'woocommerce-tcom-payway') . esc_html($this->get_last_modified_hnb_file()) . '</p>';
        echo '<p>' . esc_html__('Preferred currency will be EUR. Make sure that the default currency on your webshop is set to EUR.', 'woocommerce-tcom-payway') . '</p>';
        echo '<p>' . esc_html__('Other currencies will be automatically calculated and sent to PayWay using HNB rates: USD (WordPress) to HRK (PayWay) using ', 'woocommerce-tcom-payway') . esc_url($hnbRatesUri) . '</p>';
        echo '</p>';
    }



    function payment_fields() {
        // Check if description exists and safely output it
        if ($this->description) {
            echo wpautop(wptexturize(esc_html($this->description))); // Escape description for safety
        }
    }
    

    function receipt_page($order) {
        // Generate the IPG form and safely output the checkout message
        echo $this->generate_ipg_form($order);
        echo '<br>' . esc_html($this->checkout_msg) . '</p>'; // Correct the closing tag from </b> to </p>
    }
    

    private function get_hnb_currency()
    {
        if (!$this->curlExtension) {
            return esc_html__("CURL extension is missing. Conversion is disabled.", 'woocommerce-tcom-payway');
        }

        $response = wp_remote_get($this->tecajnaHnbApi);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            return esc_html__("Error fetching data: ", 'woocommerce-tcom-payway') . esc_html($error_message);
        }

        $content = wp_remote_retrieve_body($response);

        // Return the raw content or process it as needed
        return $content;
    }



    /**
     * Store HNB JSON locally
     *
     * Save HNB currency JSON not older than 7 days
     *
     * @return void
     */
    private function update_hnb_currency() {
        $file = __DIR__ . '/tecajnv2.json';
    
        if (!file_exists($file)) {
            // Save file content only if file doesn't exist
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                WP_Filesystem();
            }
            $wp_filesystem->put_contents($file, $this->get_hnb_currency());
        } else {
            clearstatcache();
    
            // If file exists and is not empty
            if (filesize($file) > 0) {
                // If file is older than a day
                if (time() - filemtime($file) > (24 * 3600)) {
                    $wp_filesystem->put_contents($file, $this->get_hnb_currency());
                }
            } else {
                // Handle case where file exists but is empty
                $wp_filesystem->put_contents($file, $this->get_hnb_currency());
            }
        }
    }
    

    private function get_last_modified_hnb_file() {
        $file = __DIR__ . '/tecajnv2.json';
    
        // Check if CURL extension is available
        if (!$this->curlExtension) {
            return esc_html__("HNB conversion is disabled due to missing CURL extension.", 'woocommerce-tcom-payway');
        }
    
        // Check if file exists
        if (file_exists($file)) {
            return gmdate("d.m.Y H:i:s", filemtime($file)); // Changed to gmdate
        }
    
        return esc_html__("HNB rates are not fetched from server.", 'woocommerce-tcom-payway');
    }
    

    /**
     * Retrieve currency conversion rate by currency
     *
     * @param string $currency
     * @return float|string
     */
    private function fetch_hnb_currency($currency) {
        $file = __DIR__ . '/tecajnv2.json';
    
        // Check if the file exists before attempting to read
        if (!file_exists($file)) {
            return esc_html__("Currency data not found.", 'woocommerce-tcom-payway');
        }
    
        $response = wp_remote_get($file); // Use wp_remote_get() instead of file_get_contents
        if (is_wp_error($response)) {
            return esc_html__("Error fetching file.", 'woocommerce-tcom-payway');
        }
    
        $filecontents = wp_remote_retrieve_body($response);
        $jsonFile = json_decode($filecontents, true);
    
        // Check for valid JSON decoding
        if (json_last_error() !== JSON_ERROR_NONE) {
            return esc_html__("Error decoding JSON data.", 'woocommerce-tcom-payway');
        }
    
        // Loop through each currency and return the rate
        foreach ($jsonFile as $val) {
            if ($val['valuta'] === $currency) {
                return floatval(str_replace(",", ".", $val['srednji_tecaj']));
            }
        }
    
        // Return a default rate if the currency is not found
        return 1;
    }
    

    public function generate_ipg_form($order_id) {
        global $wpdb;
    
        // Get order object
        $order = wc_get_order($order_id);
        $currency_symbol = get_woocommerce_currency();
        $order_total = $order->get_total();
    
        // Define table name and check if transaction exists in the database
        $table_name = $wpdb->prefix . 'tpayway_ipg';
    
        // Using prepare for safe query
        $check_order = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE transaction_id = %s", $order_id));
    
        // Cache the result for future use if necessary
        if ($check_order === null) {
            $check_order = 0; // Fallback in case of null result
        }
    
        if ($check_order > 0) {
            // Update existing order data using prepared statements
            $wpdb->update(
                $table_name,
                array(
                    'response_code'      => '',
                    'response_code_desc' => '',
                    'reason_code'        => '',
                    'amount'             => $order_total,
                    'or_date'            => current_time('mysql'),
                    'status'             => 0,
                ),
                array('transaction_id' => $order_id),
                array('%s', '%s', '%s', '%f', '%s', '%d'),
                array('%s')
            );
        } else {
            // Insert new order data
            $wpdb->insert(
                $table_name,
                array(
                    'transaction_id'      => $order_id,
                    'response_code'       => '',
                    'response_code_desc'  => '',
                    'reason_code'         => '',
                    'amount'              => $order_total,
                    'or_date'             => current_time('mysql'),
                    'status'              => '',
                ),
                array('%s', '%s', '%s', '%s', '%f', '%s', '%s')
            );
        }
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

        // Return language based on country code
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
        // Mapping response codes to their respective descriptions
        $res = array(
            0    => __('Action successful', 'woocommerce-tcom-payway'),
            15   => __('User cancelled the transaction by himself', 'woocommerce-tcom-payway'),
            16   => __('Transaction cancelled due to timeout', 'woocommerce-tcom-payway'),

            // Deprecated response codes
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

        // Return the description for the given response code
        return isset($res[$id]) ? $res[$id] : __('Unknown response code', 'woocommerce-tcom-payway');
    }

    function check_tcompayway_response()
{
    // Check if request method is POST and nonce is valid
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payway_nonce']) && wp_verify_nonce($_POST['payway_nonce'], 'payway_nonce_action')) {

        // Sanitize the ShoppingCartID and Amount
        if (isset($_POST['ShoppingCartID'])) {
            $order_id = sanitize_text_field($_POST['ShoppingCartID']);  // Ensure order ID is sanitized
        }

        $order = wc_get_order($order_id);  // Get order object

        $amount = isset($_POST['Amount']) ? sanitize_text_field($_POST['Amount']) : 0;  // Sanitize amount
        $status = isset($_POST['Success']) ? (int)$_POST['Success'] : 0;
        $reasonCode = isset($_POST['ApprovalCode']) ? (int)$_POST['ApprovalCode'] : 0;

        // Check for a successful payment
        if (!empty($_POST['ApprovalCode']) && isset($_POST['Success']) && isset($_POST['Signature'])) {
            if ($status === 1) {
                global $wpdb;

                // Update transaction status in the database
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

                // Add order note and update order status
                $order->add_order_note(__('PayWay Hrvatski Telekom payment successful. Unique Id: ', 'woocommerce-tcom-payway') . $order_id);
                WC()->cart->empty_cart();
                $order->update_status('pending', __('Awaiting payment', 'woocommerce-tcom-payway'));

                // Send email to admin about successful payment
                $mailer = WC()->mailer();
                $admin_email = get_option('admin_email', '');
                $message = $mailer->wrap_message(
                    __('Payment successful', 'woocommerce-tcom-payway'),
                    sprintf(__('Payment on PayWay Hrvatski Telekom is successfully completed and order status is processed.', 'woocommerce-tcom-payway'), $order->get_order_number())
                );
                $mailer->send($admin_email, sprintf(__('Payment for order no. %s was successful.', 'woocommerce-tcom-payway'), $order->get_order_number()), $message);

                // Mark payment as complete and redirect to the return URL
                $order->payment_complete();
                wp_redirect($this->get_return_url($order));
                exit;
            }
        }

        // Handle canceled or failed transactions
        if (isset($_POST['Success'])) {
            $responseCode = isset($_POST['ResponseCode']) ? (int)$_POST['ResponseCode'] : 0;

            // Handle canceled transactions (code 15 or 16)
            if (in_array($responseCode, [15, 16])) {
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

                // Display cancellation message and redirect
                $text = '<html><meta charset="utf-8"><body><center>';
                $text .= esc_html__('A payment was not cancelled', 'woocommerce-tcom-payway') . '<br>';
                $text .= esc_html__('Reason: ', 'woocommerce-tcom-payway') . esc_html($this->get_response_codes($responseCode)) . '<br>';
                $text .= esc_html__('Order Id: ', 'woocommerce-tcom-payway') . esc_html($order_id) . '<br>';
                $text .= esc_html__('Redirecting...', 'woocommerce-tcom-payway');
                $text .= '</center><script>setTimeout(function(){ window.location.replace("' . esc_js($order->get_cancel_order_url()) . '"); },3000);</script></body></html>';

                echo $text;
                exit;
            }

            // Handle failed transactions (response code 0)
            if ($_POST['Success'] === "0") {
                $errorCodes = isset($_POST['ErrorCodes']) ? sanitize_text_field(json_encode($_POST['ErrorCodes'])) : '';  // Sanitize error codes

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

                // Display failure message and redirect
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
        // Fetch pages sorted by menu order
        $wp_pages = get_pages(array('sort_column' => 'menu_order'));
        $page_list = array();

        // Optionally add a title at the beginning
        if ($title) {
            $page_list[] = $title;
        }

        foreach ($wp_pages as $page) {
            $prefix = '';

            // Indentation logic: Check for parent and apply " - " for each ancestor
            if ($indent && $page->post_parent) {
                $ancestors = get_post_ancestors($page->ID);
                $prefix = str_repeat(' - ', count($ancestors));
            }

            // Add the page title with the appropriate prefix
            $page_list[$page->ID] = $prefix . $page->post_title;
        }

        return $page_list;
    }


    public function sanitize(string $data): string
    {
        // Check if the data exists in $_POST
        $input = isset($_POST[$data]) ? $_POST[$data] : '';

        // Sanitize the input by stripping tags, removing slashes, and sanitizing text
        return strip_tags(
            stripslashes(
                sanitize_text_field($input)
            )
        );
    }
}
