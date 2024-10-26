<?php
/**
 * Papara - Wisecp Papara Ödeme Yöntemi
 *
 * Yazar: Ömer ATABER - OmerAti JRodix.Com Internet Hizmetleri
 * Versiyon: 1.5.0
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
	    $callback_url           = Controllers::$init->CRLink("payment", ['Papara', $this->get_auth_token(), 'callback']);
	    $ip = $this->get_ip();
            $api_key            = $this->config["settings"]["Papara_api_key"] ?? 'N/A';
            $amount = number_format($params['amount'], 2, '.', '');
            $fields             = [
	'OrderId'          => $params["checkout_id"] . '_'. mt_rand(100000, 999999),
       	'Amount'           => $amount,  
        'FinalAmount'      => $amount,  
	'Currency'              => $this->currency($params['currency']),
	'Installment'              => 1,
        'CardNumber'        => $params['num'],
	'ExpireYear'        => '20' . $params['expiry_y'],
	'ExpireMonth'        => $params['expiry_m'],
	'Cvv'           => $params['cvc'],
        'CardHolderName'   => $params['holder_name'],
    'CustomerId'  => $params["checkout_id"],
	'CustomerName'   => $params["clientInfo"]->name . ' ' . $params["clientInfo"]->surname,
	'ClientIP'   => $ip,
	'CallbackUrl' =>  $callback_url,
            ];

    
        $host = parse_url($this->config["settings"]["Papara_api_url"],  PHP_URL_HOST);
        $api_url = "https://{$host}/v1/vpos/3dsecure";
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
            'status' => '3D',
            'output' => "<iframe srcdoc='" . htmlspecialchars($result['data']) . "' width='100%' height='500' frameborder='0'></iframe>",
        ];
    } else {
        return [
            'status' => 'error',
            'message' => $result['error']['message'] ?? '!API ERROR!',
        ];
    }
}
public function callback()
{
      $post = $_POST;
    $result_code = Filter::init("POST/ResultCode", "string");
    $result_message = Filter::init("POST/ResultMessage", "string");
    $error_messages = [
        '7000' => 'Girilen bilgi(ler)de format hatası bulunmaktadır.',
        '7001' => 'Üye işyeri pos tanımlarında eksik/hata bulunmaktadır.',
        '7002' => 'Üye işyeri tanımlarında eksik/hata bulunmaktadır.',
        '7003' => 'Geçersiz para birimi.',
        '7004' => 'İşlem bulunamamıştır.',
        '7005' => 'İptal/İade edilmek istenen işlem bulunamamıştır.',
        '7006' => 'İşlem daha önce iade edilmiştir.',
        '7007' => 'İade işlemi için geçersiz tutar girilmiştir.',
        '7008' => 'İşlem iade edilemez.',
        '7009' => 'İşlem iptal edilemez.',
        '7010' => 'Sipariş numarası (OrderId) daha önce kullanılmıştır.',
        '7034' => 'İşlem Tamamlanamaz.',
        '7035' => 'İşlem Tamamlanamaz.',
        '7036' => '3D işlemi daha önce tamamlanmış.',
        '1999' => 'Teknik bir hata oluşmuştur ve ilgili ekiplerimize bilgi verilmiştir.',
        '7200' => 'İşlem banka tarafından onaylanmamıştır, bilgi için bankanızı arayın.',
        '7201' => 'Kart bilgileri geçersizdir.',
        '7202' => 'Kartın geçerlilik süresi dolmuştur.',
        '7203' => 'Hatalı pin (doğrulama kodu) girilmiştir.',
        '7204' => 'Kartın bakiyesi/limiti yetersiz.',
        '7205' => 'Kayıp kart kullanılmıştır, kartı imha edin.',
        '7206' => 'Çalıntı kart kullanılmıştır, kartı imha edin.',
        '7207' => 'Kart internet ödemelerine kapalıdır.',
        '7299' => 'İşlem banka tarafında hata almıştır, bilgi için bankanızı arayın.',
    ];
    if (array_key_exists($result_code, $error_messages)) {
        return [
            'status' => 'error',
            'message' => $result_message,
            'callback_message' => $error_messages[$result_code],
        ];
    }
    
    $custom_id      = (int) Filter::init("POST/CustomerId","numbers");

    if(!$custom_id){
        $this->error = 'Custom id not found.';
        return false;
    }

     $checkout       = $this->get_checkout($custom_id);

    if(!$checkout)
    {
        $this->error = 'Checkout ID unknown';
        return false;
    }
    $this->set_checkout($checkout);
return [
        'status'            => 'successful',
        'callback_message' => 'Ödeme işleminiz başarılı',
         'paid'                    => [
            'amount'        => $amount,
            'currency'      => $this->currency($params['currency']),
        ],
    ];
}

    }
