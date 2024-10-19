<?php
/**
 * Papara - Wisecp Papara Ödeme Yöntemi
 *
 * Yazar: Ömer ATABER - OmerAti JRodix.Com Internet Hizmetleri
 * Versiyon: 1.0.0
 * Tarih: 03.09.2024
 * Web: https://www.jrodix.com
 *
 */
    class Papara extends PaymentGatewayModule
    {
        function __construct()
        {
            $this->name             = __CLASS__;
            $this->standard_card    = true;

            parent::__construct();
        }
        public function get_auth_token(){
            $syskey = Config::get("crypt/system");
            $token  = md5(Crypt::encode("Papara-Auth-Token=".$syskey,$syskey));
            return $token;
        }
        public function config_fields()
        {
            return [
                'Papara_api_url'          => [
                    'name'              => "Papara Api Url",
                    'description'       => "Paparadan Aldıgınız Api Url",
                    'type'              => "text",
                    'value'             => $this->config["settings"]["Papara_api_url"] ?? '',
                    'placeholder'       => "lütfen api url yazın",
                ],
                'Papara_api_key'          => [
                    'name'              => "Papara Api Key",
                    'description'       => "Paparadan Aldıgınız Api Key",
                    'type'              => "password",
                    'value'             => $this->config["settings"]["Papara_api_key"] ?? '',
                    'placeholder'       => "lütfen api key yazınız",
                ]
            ];
        }
        public function commission_fee_calculator($amount){
            $rate = $this->config["settings"]["commission_rate"];
            $calculate = Money::get_discount_amount($amount,$rate);
            return $calculate;
        }

        public function get_commission_rate(){
            return $this->config["settings"]["commission_rate"];
        }

        public function get_ip(){
            if( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            } elseif( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else {
                $ip = $_SERVER["REMOTE_ADDR"];
            }
            return $ip;
        }

        public function capture($params=[])
        {
			if($checkout_data["currency"] == 4) $currency = 1;
            elseif($checkout_data["currency"] == 5) $currency = 2;
            else $currency = 0;
			$ip = $this->get_ip();
            $api_key            = $this->config["settings"]["Papara_api_key"] ?? 'N/A';
            $amount = number_format($params['amount'], 2, '.', '');
            $fields             = [
			   'OrderId'                => $params["checkout_id"],
       	'Amount'           => $amount,  
        'FinalAmount'      => $amount,  
			   'Currency'              => $this->currency($params['currency']),
			   'Installment'              => 1,
                'CardNumber'        => $params['num'],
				'ExpireYear'        => '20' . $params['expiry_y'],
				'ExpireMonth'        => $params['expiry_m'],
				'Cvv'           => $params['cvc'],
                'CardHolderName'   => $params['holder_name'],
				'CustomerName'   => $params["clientInfo"]->name . ' ' . $params["clientInfo"]->surname,
				'ClientIP'   => $ip,
            ];

        $host = parse_url($this->config["settings"]["Papara_api_url"],  PHP_URL_HOST);
        $api_url = "https://{$host}/v1/vpos/sale";
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $api_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'ApiKey: '.$api_key,
                'Content-Type: application/json',
            ));
            curl_setopt($curl,CURLOPT_POST,1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($curl);

    if (curl_errno($curl)) {
        return [
            'status' => 'error',
            'message' => curl_error($curl)
        ];
    }
      if (strpos($result, '<html') !== false) {
        return [
            'status' => 'error',
            'message' => 'API yanıtı HTML formatında geldi, 401 Yetkilendirme Hatası Sanal Post Kapalı Papara Başvurun.',
            'raw_result' => $result,
        ];
    }  
    $result = json_decode($result, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'status' => 'error',
            'message' => 'JSON Parse Error: ' . json_last_error_msg(),
            'raw_result' => $result,
        ];
    }

    if ($result && $result['succeeded'] === false) {
        $error_message = $result['error']['message'] ?? '!API ERROR!';
        $error_code = $result['error']['code'] ?? null;

        return [
            'status' => 'error',
            'message' => $error_message,
            'code' => $error_code,
        ];

    }

    if ($result && isset($result['succeeded']) && $result['succeeded'] === true) {
        return [
            'status' => 'successful',
            'message' => ['Merchant Transaction ID' => $result['transaction_id']],
        ];
    } else {
        return [
            'status' => 'error',
            'message' => $result['error']['message'] ?? '!API ERROR!',
        ];
    }
}

    }
