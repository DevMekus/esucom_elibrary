<?php
namespace App\Services;

use App\Utils\MailClient;

class EmailServices{

    public static function registrationEmail($data)
    {

        $templateData = [
            '{{logo_url}}' => BASE_URL . 'assets/images/logo_white.png',
            '{{banner_image_url}}' => BASE_URL . 'assets/images/emails/registration_banner.jpeg',
            '{{user_name}}' => $data['fullname'],
            '{{user_email}}' => $data['email_address'],
            '{{user_mobile}}' => $data['phone'],
            '{{user_password}}' => $data['user_password'],
            '{{site_name}}' => BRAND_NAME,
            '{{company_address}}' => COMPANY_ADDRESS,
            '{{instagram_url}}' => '',
            '{{twitter_url}}' => '',
            '{{facebook_url}}' => '',
            '{{login_link}}' => BASE_URL . 'auth/login',
            '{{support_url}}' => BASE_URL . 'contact-us',
            '{{current_year}}' => date('Y'),
        ];

        return MailClient::sendMail(
            $data['email_address'],
            'Welcome to ' . BRAND_NAME,
            ROOT_PATH . '/apps/templates/registration.html',
            $templateData,
            $data['fullname']
        );
    }

    public static function passwordResetEmail($data)
    {
        $resetLink = BASE_URL . "auth/reset-password?token=$data[reset_token]&email=" . urlencode($data['email_address']);

        $templateData = [
            '{{logo_url}}' => BASE_URL . 'assets/images/logo_white.png',
            '{{user_name}}' => $data['fullname'],
            '{{platform_name}}' => BRAND_NAME,
            '{{reset_link}}' => $resetLink,
            '{{support_url}}' => BASE_URL . 'contact-us',
            '{{current_year}}' => date('Y'),
            '{{user_email}}' => $data['email_address']
        ];

        return MailClient::sendMail(
                $data['email_address'],
                'Account Recovery',
                ROOT_PATH . '/apps/templates/reset_password.html',
                $templateData,
                $data['fullname']
        );
    }

    public static function sendInsufficientStockNotification($product_id, $size_id, $required_qty)
    {   
           
    }

    public static function sendOrderConfirmationEmail($data){
         $templateData = [
            '{{logo_url}}'       => BASE_URL . 'assets/images/logo_white.png',
            '{{site_name}}'      => BRAND_NAME,
            '{{user_name}}'      => $data['customer_name'] ?? 'Customer',
            '{{order_number}}'   => $data['order_id'],
            '{{tracking_link}}'  => BASE_URL . 'track-order?search=' . $data['order_id'],
            '{{support_email}}'  => $_ENV['DB_HOST'],
            '{{year}}'           => date('Y'),
            '{{company_address}}' => COMPANY_ADDRESS,
        ];

        return MailClient::sendMail(
            $data['customer_email'],
            'Order Confirmation - ' . BRAND_NAME,
            ROOT_PATH . '/apps/templates/new_order.html',
            $templateData,
            $data['customer_name'] ?? 'Customer'
        );
    }  
    


    static function sendOrderNotificationToAdmin($data){
        $items = json_decode($data['order_details'], true);

        $html = '
        <table style="width:100%; border-collapse: collapse; font-family: Arial, sans-serif;">
            <thead>
                <tr>
                    <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Item</th>
                    <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Size</th>
                    <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Qty</th>
                    <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Price</th>
                    <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Toppings</th>
                    <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Total</th>
                </tr>
            </thead>
            <tbody">
        ';

        foreach ($items as $item) {

            // Build toppings
            $toppingsHtml = '';

            if (!empty($item['toppings'])) {                 
                foreach ($item['toppings'] as $tp) {                    

                    $toppingsHtml .= "- {$tp['extras']} (₦" . number_format($tp['price']) . ") x {$tp['qty']} <br>";
                }
            } else {
                $toppingsHtml = '<span style="color:#999;">None</span>';
            }

            $gTotal = intval($item['price']) * intval($item['qty']);

            $html .= '
                <tr>
                    <td style="padding:8px; border-bottom:1px solid #eee;">' . $item['title'] . '</td>
                    <td style="padding:8px; border-bottom:1px solid #eee;">' . $item['size'] . '</td>
                    <td style="padding:8px; border-bottom:1px solid #eee;">' . $item['qty'] . '</td>
                    <td style="padding:8px; border-bottom:1px solid #eee;">₦' . number_format($item['price']) . '</td>
                    <td style="padding:8px; border-bottom:1px solid #eee;">' . $toppingsHtml . '</td>
                    <td style="padding:8px; border-bottom:1px solid #eee;">₦' . number_format($gTotal) . '</td>
                </tr>
            ';
        }

        $html .= '</tbody></table>';

        $data['order_details'] = $html;

         $templateData = [
            '{{logo_url}}'       => BASE_URL . 'assets/images/logo_white.png',
            '{{site_name}}'      => BRAND_NAME,
            '{{order_number}}'   => $data['order_id'],
            '{{order_platform}}'   => $data['platform'],
            '{{customer_name}}'  => $data['customer_name'] ?? 'Customer',
            '{{customer_email}}' => $data['customer_email'] ?? 'N/A',
            '{{customer_phone}}' => $data['customer_phone'] ?? 'N/A',
            '{{delivery_type}}' => $data['delivery_type'] ?? 'N/A',
            '{{delivery_address}}' => $data['delivery_address'] ?? 'N/A',
            '{{delivery_area}}' => $data['city'] ? strtoupper($data['city']) : 'N/A',
            '{{delivery_fee}}' => $data['delivery_fee'] ? number_format($data['delivery_fee'], 2): 0,
            '{{delivery_instructions}}' => $data['delivery_instructions'] ?? 'N/A',
            '{{total_amount}}'   => number_format($data['total_amount'], 2),
            '{{order_details}}'  => $data['order_details'] ?? '',
            '{{support_email}}'  => BRAND_EMAIL,
            '{{year}}'           => date('Y'),
            '{{company_address}}' => COMPANY_ADDRESS,
        ];

        return MailClient::sendMail(
            ADMIN_EMAIL,
            'New Order Placed - ' . BRAND_NAME,
            ROOT_PATH . '/apps/templates/admin_new_order.html',
            $templateData,
            'Admin'
        );
    }


    static function sendOrderUpdateNotification($data){
        $templateData = [
                    '{{logo_url}}'        => BASE_URL . 'assets/images/logo_white.png',
                    '{{site_name}}'       => BRAND_NAME,
                    '{{user_name}}'       => $data['customer_name'] ?? 'Customer',
                    '{{order_number}}'    => $data['order_id'],
                    '{{order_status}}'    => ucfirst($data['status']),
                    '{{tracking_link}}'   => BASE_URL . 'track-order?search=' . $data['order_id'],
                    '{{support_email}}'   => $_ENV['SUPPORT_EMAIL'],
                    '{{year}}'            => date('Y'),
                    '{{company_address}}' => COMPANY_ADDRESS,
                ];


                return MailClient::sendMail(
                    $data['customer_email'],
                    'PizzaSquare Order Update',
                    ROOT_PATH . '/apps/templates/order_update.html',
                    $templateData,
                    $data['customer_name'] ?? "Customer"
                );
    }

    static function sendContactusMessageToAdmin($data){
        $templateData = [
            '{{logo_url}}' => BASE_URL . 'assets/images/logo_white.png',
            '{{site_name}}' => BRAND_NAME,
            '{{sender_name}}' => $data['name'],
            '{{sender_email}}' => $data['email'],
            '{{sender_subject}}' => $data['subject'],
            '{{sender_message}}' => nl2br($data['message']),
            '{{year}}' => date('Y'),
            '{{company_address}}' => COMPANY_ADDRESS,               
        ];

        return  MailClient::sendMail(
            ADMIN_EMAIL,
            'New Contact Message: ' . $data['subject'],
            ROOT_PATH . '/apps/templates/contact_us.html',
            $templateData,
            $data['name']
        );
    }
    
}