<?php

/**
 * Class WC_TPAYWAY
 *
 * WC_TPAYWAY with API 2.0
 */
class WC_TPAYWAY extends WC_Payment_Gateway
{
    /**
     * WC_TPAYWAY constructor.
     */
    public function __construct()
    {
        $this->id = 'WC_TPAYWAY';
        $this->domain = 'tcom-payway-wc';
        $this->icon = apply_filters('woocommerce_payway_icon', TCOM_PAYWAY_URL . 'assets/images/payway.png');
        $this->method_title = 'T-Com PayWay Woocommerce Payment Gateway';
        $this->has_fields = false;

        $this->version = '2.0';

        $this->init_form_fields();
        $this->init_settings();

        $settings = $this->settings;

        $this->title = isset($settings['title']) ? $settings['title'] : '';
        $this->shop_id = isset($settings['mer_id']) ? $settings['mer_id'] : '';
        $this->acq_id = isset($settings['acq_id']) ? $settings['acq_id'] : '';
        $this->pg_domain = $this->get_option( 'pg_domain' );
        $this->response_url_success = isset($settings['response_url_success']) ? $settings['response_url_success'] : $this->get_return_url($order);
        $this->response_url_fail = isset($settings['response_url_fail']) ? $settings['response_url_fail'] : $order->get_cancel_order_url();
        $this->checkout_msg = isset($settings['checkout_msg']) ? $settings['checkout_msg'] : '';
        $this->woo_active = isset($settings['woo_active']) ? $settings['woo_active'] : '';
        $this->description = isset($settings['description']) ? $settings['description'] : '';

        $this->msg['message'] = '';
        $this->msg['class'] = '';

        add_action('init', array(&$this, 'check_tcompayway_response'));

        if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
        } else {
            add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
        }
        add_action('woocommerce_receipt_WC_TPAYWAY', array(&$this, 'receipt_page'));
    }

    function init_form_fields()
    {

        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        if (is_plugin_active('woo-multi-currency/woo-multi-currency.php')) {
            $woo_active = array(
                'title' => __('Use Woo Multi Currency conversion', $this->domain),
                'type' => 'checkbox',
                'label' => __('Enable currency auto conversion for <a href="https://wordpress.org/plugins/woo-multi-currency/">Woo Multi Currency</a> plugin. Make sure that is checked "Allow Multi Currency" in plugin page.', 'tcom-payway-wc'),
                'default' => 'no',
            );
        } else {
            $woo_active = array(
                'title' => __('Use Woo Multi Currency conversion', $this->domain),
                'type' => 'checkbox',
                'disabled' => true,
                'label' => __('Enable currency auto conversion for <a href="https://wordpress.org/plugins/woo-multi-currency/">Woo Multi Currency</a> plugin. Make sure that is checked "Allow Multi Currency" in plugin page.', 'tcom-payway-wc'),
                'default' => 'no',
            );
        };

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', $this->domain),
                'type' => 'checkbox',
                'label' => __('Enable T-Com PayWay Module.', 'tcom-payway-wc'),
                'default' => 'no',
            ),
            'title' => array(
                'title' => __('Title:', $this->domain),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'tcom-payway-wc'),
                'default' => __('T-Com PayWay', 'tcom-payway-wc'),
            ),
            'description' => array(
                'title' => __('Description:', $this->domain),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'tcom-payway-wc'),
                'default' => __('T-Com Payway is secure payment gateway in Croatia and you can pay using this payment in other currency.', 'tcom-payway-wc'),
            ),
            'pg_domain' => array(
                'title' => __('Authorize URL', $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => __('T-Com PayWay data submiting to this URL', $this->domain),
                'default' => 'prod',
                'desc_tip' => true,
		'options' => array(
			'test'      => __( 'Test Mode', 'woocommerce' ),
			'prod' => __( 'Prod Mode', 'woocommerce' ),
		),
            ),
            'mer_id' => array(
                'title' => __('Shop ID:', $this->domain),
                'type' => 'text',
                'description' => __('ShopID represents a unique identification of web shop. ShopID is received from PayWay after signing the request for
using PayWay service.', 'tcom-payway-wc'),
                'default' => '',
            ),
            'acq_id' => array(
                'title' => __('Secret Key:', $this->domain),
                'type' => 'text',
                'description' => '',
                'default' => '',
            ),
            'response_url_success' => array(
                'title' => __('Response URL success', $this->domain),
                'type' => 'text',
                'description' => '',
                'default' => '',
            ),
            'response_url_fail' => array(
                'title' => __('Response URL fail:', $this->domain),
                'type' => 'text',
                'description' => '',
                'default' => '',
            ),
            'checkout_msg' => array(
                'title' => __('Message redirect:', $this->domain),
                'type' => 'textarea',
                'description' => __('Message to client when redirecting to PayWay page', 'tcom-payway-wc'),
                'default' => '',
            ),
            'woo_active' => $woo_active,
        );
    }

    public function admin_options()
    {
        echo '<h3>' . __('T-Com PayWay payment gateway', 'tcom-payway-wc') . '</h3>';
        echo '<p>' . __('<a target="_blank" href="http://pgw.t-com.hr/">T-Com PayWay</a> is payment gateway from telecom T-Com who provides payment gateway services as dedicated services to clients in Croatia.', 'tcom-payway-wc') . '</p>';
        echo '<table class="form-table">';
        $this->generate_settings_html();
        echo '</table>';
    }

    function payment_fields()
    {
        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }
    }

    function receipt_page($order)
    {
        global $woocommerce;

        $order_details = new WC_Order($order);

        echo $this->generate_ipg_form($order);
        echo '<br>' . $this->checkout_msg . '</b>';
    }

    public function generate_ipg_form($order_id)
    {

        global $wpdb;
        global $woocommerce;

        $order = new WC_Order($order_id);
        $productinfo = "Order $order_id";

        $curr_symbole = get_woocommerce_currency();
        $order_total = $order->get_total();

        $table_name = $wpdb->prefix . 'tpayway_ipg';
        $check_order = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE transaction_id = '" . $order_id . "'");
        if ($check_order > 0) {
            $wpdb->update(
                $table_name,
                array(
                    'response_code' => 0,
                    'response_code_desc' => '',
                    'reason_code' => '',
                    'amount' => $order_total,
                    'or_date' => date('Y-m-d'),
                    'status' => 0,
                ),
                array('transaction_id' => $order_id)
            );
        } else {
            $wpdb->insert(
                $table_name,
                array(
                    'transaction_id' => $order_id,
                    'response_code' => 0,
                    'response_code_desc' => '',
                    'reason_code' => '',
                    'amount' => $order_total,
                    'or_date' => date('Y-m-d'),
                    'status' => 0,
                ),
                array('%s', '%d')
            );
        }

        switch ($woocommerce->customer->country) {
            case 'HR':
            case 'SR':
            case 'SL':
            case 'BS':
                $pgw_language = 'hr';
                break;
            case 'CG':
                $pgw_language = 'cg';
                break;
            case 'DE':
                $pgw_language = 'de';
                break;
            case 'IT':
                $pgw_language = 'it';
                break;
            case 'FR':
                $pgw_language = 'fr';
                break;
            case 'NL':
                $pgw_language = 'nl';
                break;
            case 'HU':
                $pgw_language = 'hu';
                break;
            case 'RU':
                $pgw_language = 'ru';
                break;
            case 'SK':
                $pgw_language = 'sk';
                break;
            case 'CZ':
                $pgw_language = 'cz';
                break;
            case 'PL':
                $pgw_language = 'pl';
                break;
            case 'PT':
                $pgw_language = 'pt';
                break;
            case 'ES':
                $pgw_language = 'es';
                break;
            default:
                $pgw_language = 'en';
        }

        $pgw_language = strtoupper($pgw_language);

        // $order->order_total = $order->order_total;
        if ('HR' === $woocommerce->customer->country) {
            if ('HRK' === $order->get_order_currency()) {
                $order_total = $order_total;
            }
        } else {
            $order_total = $order_total;
        }

        $curr_symbole = get_woocommerce_currency();
        $hrk_rate = apply_filters('jal_pw_currency_rate', false); // filter can be used to input Currency rate for other currencies to HRK
        $convert = 'HRK' !== $curr_symbole;

        if (false === $convert && $hrk_rate) {
            $wcml_settings = get_option('_wcml_settings'); // WooCommerce Multilingual - Multi Currency (WPML plugin)

            if ($wcml_settings) {
                $curr_rates = $wcml_settings['currency_options'];

                $hrk_rate = $curr_rates[$curr_symbole]['rate'];
            } elseif ('yes' === $this->woo_active) {
                if (isset($_COOKIE['wmc_current_currency'])) { // WooCommerce Multi Currency plugin
                    $selected_c = get_option('wmc_selected_currencies');

                    if ('HRK' !== $_COOKIE['wmc_current_currency']) {
                        $hrk_rate = $selected_c[$_COOKIE['wmc_current_currency']]['rate'];
                    }
                }
            }
        }

        if ($convert && is_numeric($hrk_rate)) {
            $order_total = $woocommerce->cart->total * (1 / $hrk_rate);
        }

        $order_format_value = str_pad(($order_total * 100), 12, '0', STR_PAD_LEFT);
        $total_amount = number_format($order_total, 2, '', '');
	$total_amount_request = number_format($order_total, 2, ',', '');

        $secret_key = $this->acq_id;    // Secret key

        $pgw_shop_id = $this->shop_id;
        $pgw_order_id = $order_id;
        $pgw_amount = $total_amount;

        $order = new WC_Order($order_id);
        $pgw_first_name = $order->get_billing_first_name();
        $pgw_last_name = $order->get_billing_last_name();
        $pgw_street = $order->get_billing_address_1() . ', ' . $order->get_billing_address_2();
        $pgw_city = $order->get_billing_city();
        $pgw_post_code = $order->get_billing_postcode();
        $pgw_country = $order->get_billing_country();
        $pgw_telephone = $order->get_billing_phone();
        $pgw_email = $order->get_billing_email();

        $pgw_signature = hash('sha512', $pgw_shop_id . $secret_key . $pgw_order_id . $secret_key . $pgw_amount . $secret_key);

        $form_args = array(
            // Mandatory fields
            'ShopID' => $pgw_shop_id,
            'ShoppingCartID' => $pgw_order_id,
            'Version' => $this->version,
            'TotalAmount' => $total_amount_request,
            'Signature' => $pgw_signature,
            'ReturnURL' => $this->response_url_success,
            'CancelURL' => $this->response_url_fail,
            'ReturnErrorURL' => $this->response_url_fail,

            // Optional fields
            'Lang' => $pgw_language,

            'CustomerFirstName' => substr($pgw_first_name, 0, 50),
            'CustomerLastName' => substr($pgw_last_name, 0, 50),
            'CustomerAddress' => substr($pgw_street, 0, 100),
            'CustomerCity' => substr($pgw_city, 0, 50),
            'CustomerZIP' => substr($pgw_post_code, 0, 20),
            'CustomerCountry' => ($pgw_country),
            'CustomerEmail' => substr($pgw_email, 0, 254),
            'CustomerPhone' => substr($pgw_telephone, 0, 20),
            // 'PaymentPlan' => 0000, // No payment plan
            'ReturnMethod' => 'POST',

            'acq_id' => $this->acq_id, // secret key
            'PurchaseAmt' => $order_format_value,
        );

        $form_args_array = array();
        $form_args_joins = null;
        foreach ($form_args as $key => $value) {
            $form_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
            $form_args_joins = $key . '=' . $value . '&';
        }
	    
	    $pgDomain = 'https://form.payway.com.hr/authorization.aspx';
	    if ($this->pg_domain == 'test') {
	    	$pgDomain = 'https://formtest.payway.com.hr/authorization.aspx';
	    }

        return '<p></p>
    <p>Total amount will be <b>' . number_format(($order_total)) . ' ' . $curr_symbole . '</b></p>
    <form action="' . $pgDomain . '" method="post" name="payway-authorize-form" id="payway-authorize-form" type="application/x-www-form-urlencoded">
        ' . implode('', $form_args_array) . '
        <input type="submit" class="button-alt" id="submit_ipg_payment_form" value="' . __('Pay via PayWay', 'tcom-payway-wc') . '" />
            <a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __('Cancel order &amp; restore cart', 'tcom-payway-wc') . '</a>
        </form>
        <!-- autoform submit -->
        <script type="text/javascript">
            jQuery("#submit_ipg_payment_form").trigger("click");
        </script>
        ';
    }

    function process_payment($order_id)
    {
        $order = new WC_Order($order_id);
        return array(
            'result' => 'success',
            'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay')))),
        );
    }

    function get_response_codes($id)
    {
        $id = (int)$id;

        $res = array(
            0 => __('Action successful', 'tcom-payway-wc'),
            15 => __('User cancelled the transaction by himself', 'tcom-payway-wc'),
            16 => __('Transaction cancelled due timeout', 'tcom-payway-wc'),

            // Deprecated, refactoring
            1 => __('Action unsuccessful', 'tcom-payway-wc'),
            2 => __('Error processing', 'tcom-payway-wc'),
            3 => __('Action cancelled', 'tcom-payway-wc'),
            4 => __('Action unsuccessful (3D Secure MPI)', 'tcom-payway-wc'),
            1000 => __('Incorrect signature (pgw_signature)', 'tcom-payway-wc'),
            1001 => __('Incorrect store ID (pgw_shop_id)', 'tcom-payway-wc'),
            1002 => __('Incorrect transaction ID (pgw_transaction_id)', 'tcom-payway-wc'),
            1003 => __('Incorrect amount (pgw_amount)', 'tcom-payway-wc'),
            1004 => __('Incorrect authorization_type (pgw_authorization_type)', 'tcom-payway-wc'),
            1005 => __('Incorrect announcement duration (pgw_announcement_duration)', 'tcom-payway-wc'),
            1006 => __('Incorrect installments number (pgw_installments)', 'tcom-payway-wc'),
            1007 => __('Incorrect language (pgw_language)', 'tcom-payway-wc'),
            1008 => __('Incorrect authorization token (pgw_authorization_token)', 'tcom-payway-wc'),
            1100 => __('Incorrect card number (pgw_card_number)', 'tcom-payway-wc'),
            1101 => __('Incorrect card expiration date (pgw_card_expiration_date)', 'tcom-payway-wc'),
            1102 => __('Incorrect card verification data (pgw_card_verification_data)', 'tcom-payway-wc'),
            1200 => __('Incorrect order ID (pgw_order_id)', 'tcom-payway-wc'),
            1201 => __('Incorrect order info (pgw_order_info)', 'tcom-payway-wc'),
            1202 => __('Incorrect order items (pgw_order_items)', 'tcom-payway-wc'),
            1300 => __('Incorrect return method (pgw_return_method)', 'tcom-payway-wc'),
            1301 => __('Incorrect success store url (pgw_success_url)', 'tcom-payway-wc'),
            1302 => __('Incorrect error store url (pgw_failure_url)', 'tcom-payway-wc'),
            1304 => __('Incorrect merchant data (pgw_merchant_data)', 'tcom-payway-wc'),
            1400 => __('Incorrect buyer\'s name (pgw_first_name)', 'tcom-payway-wc'),
            1401 => __('Incorrect buyer\'s last name (pgw_last_name)', 'tcom-payway-wc'),
            1402 => __('Incorrect address (pgw_street)', 'tcom-payway-wc'),
            1403 => __('Incorrect city (pgw_city)', 'tcom-payway-wc'),
            1404 => __('Incorrect ZIP code (pgw_post_code)', 'tcom-payway-wc'),
            1405 => __('Incorrect country (pgw_country)', 'tcom-payway-wc'),
            1406 => __('Incorrect contact phone (pgw_telephone)', 'tcom-payway-wc'),
            1407 => __('Incorrect contact e-mail address (pgw_email)', 'tcom-payway-wc'),
        );

        return $res[$id];
    }

    function check_tcompayway_response()
    {
        global $woocommerce;

        // Return if is error during installation
        if (!$_POST['ShoppingCartID']) {
                return;
        }
        
        if (!$_POST['Amount']) {
        	return;
	}
	// End installation
        
        $order_id = $_POST['ShoppingCartID'];

        $order = new WC_Order($order_id);
        $amount = $this->sanitize($_POST['Amount']);
        $status = isset($_POST['Success']) ? (int)$_POST['Success'] : 0;
        $reasonCode = isset($_POST['ApprovalCode']) ? (int)$_POST['ApprovalCode'] : 0;

        // Return URL
        if (!empty($_POST['ApprovalCode']) && isset($_POST['Success']) && isset($_POST['Signature'])) {
            if ((int)$_POST['Success'] == 1) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'tpayway_ipg';
                $wpdb->update(
                    $table_name,
                    array(
                        'response_code' => $status,
                        'response_code_desc' => $this->get_response_codes(0),
                        'reason_code' => $reasonCode,
                        'status' => 1,
                    ),
                    array('transaction_id' => $order_id)
                );

                $order_note = __('T-Com PAYWAY payment successful. Unique Id: ', 'tcom-payway-wc') . $order_id;
                $order->add_order_note(esc_html($order_note));
                $woocommerce->cart->empty_cart();

                // Mark as on-hold (we're awaiting the payment).
                $order->update_status('pending', __('Awaiting payment', 'tcom-payway-wc'));

                $mailer = $woocommerce->mailer();

                $admin_email = get_option('admin_email', '');

                $message = $mailer->wrap_message(
                    __('Payment successful', 'tcom-payway-wc'),
                    sprintf(
                        __('Payment on T-Com PayWay is successfully completed and order status is processed.', 'tcom-payway-wc'),
                        $order->get_order_number()
                    )
                );
                $mailer->send(
                    $admin_email,
                    sprintf(
                        __('Payment for order no. %s was sucessful.', 'tcom-payway-wc'),
                        $order->get_order_number()
                    ),
                    $message
                );

                $order->payment_complete();

                wp_redirect($this->response_url_success, 302);
		exit;
            }
        }

        if (isset($_POST['Success'])) {
            if ($_POST['Success'] == "0") {
                $errorCodes = json_encode($_POST['ErrorCodes']);

                $order->update_status('failed');
                $order->add_order_note($this->get_response_codes($reasonCode) . " (Code $reasonCode)");
                $woocommerce->cart->empty_cart();

                global $wpdb;
                $table_name = $wpdb->prefix . 'tpayway_ipg';
                $wpdb->update(
                    $table_name,
                    array(
                        'response_code' => 0,
                        'response_code_desc' => $errorCodes,
                        'reason_code' => 0,
                        'status' => 0,
                    ),
                    array('transaction_id' => $order_id)
                );

                $text = '<html><meta charset="utf-8"><body><center>';
                $text .= __('A payment was not successfull or declined', 'tcom-payway-wc') . '<br>';
                $text .= __('Reason: ', 'tcom-payway-wc');
                $text .= $errorCodes . '<br>';
                $text .= __('Order Id: ', 'tcom-payway-wc');
                $text .= $order_id . '<br>';
                $text .= __('Redirecting...', 'tcom-payway-wc');
                $text .= '</center><script>setTimeout(function(){ window.location.replace("' . $this->response_url_fail . '"); },3000);</script></body></html>';

                echo $text;

                exit;
            }
        }

        // Cancelled
        if (isset($_POST['ResponseCode'])) {
            $responseCode = (int)$_POST['ResponseCode'];
            if ($responseCode == 15 || $responseCode == 16) {

                $order->add_order_note($this->get_response_codes($responseCode) . " (Code $responseCode)");
                $order->update_status('cancelled');
                $woocommerce->cart->empty_cart();

                global $wpdb;
                $table_name = $wpdb->prefix . 'tpayway_ipg';
                $wpdb->update(
                    $table_name,
                    array(
                        'response_code' => $responseCode,
                        'response_code_desc' => $this->get_response_codes($responseCode),
                        'reason_code' => 0,
                        'status' => 0,
                    ),
                    array('transaction_id' => $order_id)
                );

                $text = '<html><meta charset="utf-8"><body><center>';
                $text .= __('A payment was not cancelled', 'tcom-payway-wc') . '<br>';
                $text .= __('Reason: ', 'tcom-payway-wc');
                $text .= $this->get_response_codes($responseCode) . '<br>';
                $text .= __('Order Id: ', 'tcom-payway-wc');
                $text .= $order_id . '<br>';
                $text .= __('Redirecting...', 'tcom-payway-wc');
                $text .= '</center><script>setTimeout(function(){ window.location.replace("' . $this->response_url_fail . '"); },3000);</script></body></html>';

                echo $text;

                exit;
            }
        }
    }

    function get_pages($title = false, $indent = true)
    {
        $wp_pages = get_pages('sort_column=menu_order');
        $page_list = array();
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
