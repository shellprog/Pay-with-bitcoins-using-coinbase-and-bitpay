<?php

class HomeController extends BaseController {

    public function index(){
        return View::make('index');
    }

	public function processPayment()
	{
        $v = Validator::make(["amount"=>Input::get("amount"),"email"=>Input::get("email")],
            ["amount"=>"required|integer","email"=>"required|email"]);

        if($v->passes()){

            $billing = new Billing();
            $billing->name = Input::get("name");
            $billing->address = Input::get("address");
            $billing->city = Input::get("city");
            $billing->state = Input::get("state");
            $billing->zip = Input::get("zip");
            $billing->email = Input::get("email");
            $billing->phone = Input::get("phone");
            $billing->save();

            $order = new Order();
            $order->amount = Input::get("amount");
            $order->billing_id = $billing->id;
            $order->save();

            $bitcoinRedirectURL = URL::to("/");

            if(Input::get('type')=="bitpay"){

                try {
                    $bitpayResponse = $this->bitpayRequestCurl($order->id,$order->amount,$billing);
                } catch (\Exception $e) {
                    Session::flash("error_msg",$e->getMessage());
                    return Redirect::back();
                }

                //Set order status to pending since user didnt paid yet and serialize the response maybe useful later
                $order->type = "bitpay";
                $order->status = "Pending";
                $order->response = serialize($bitpayResponse);

                $bitcoinRedirectURL = $bitpayResponse->url;

            }

            if(Input::get('type')=="coinbase"){

                try {
                    $coinbaseResponse =  $this->coinbaseRequestCurl($order->id,$order->amount);
                } catch (\Exception $e) {
                    Session::flash("error_msg",$e->getMessage());
                    return Redirect::back();
                }

                $order->type = "coinbase";
                $order->status = "Pending";
                $order->response = serialize($coinbaseResponse);

                $bitcoinRedirectURL = "https://www.coinbase.com/checkouts/".$coinbaseResponse->button->code;

            }

            return Redirect::to($bitcoinRedirectURL);

        }else{

            $response = "";
            $messages = $v->messages()->all();

            foreach($messages as $message){
                $response.="<li style='margin-left:10px;'>{$message}</li>";
            }

            Session::flash("error_msg",$response);
            return Redirect::back()->withInput();
        }

    }

    public function notify(){

    }

    public function bitpayRequestCurl($orderId, $grandTotal,$billing) {

        $config = Config::get('bitcoins');

        $url = $config['bitpay_url'];
        $apiKey = $config['bitpay_api_key'];

        $post = array(
            'orderID' =>$orderId,
            'price' => $grandTotal,
            'currency' => 'USD',
            'itemDesc' => 'KodeInfo Checkout Invoice',
            'notificationEmail'=>'developers@kodeinfo.com',
            'notificationURL'=>$config['bitpay_notify_url'],
            'physical'=>true,
            'fullNotifications'=>true,
            'buyerName'=>$billing->name,
            'buyerAddress1'=>$billing->address,
            'buyerAddress2'=>'',
            'buyerCity'=>$billing->city,
            'buyerState'=>$billing->state,
            'buyerZip'=>$billing->zip,
            'buyerEmail'=>$billing->email,
            'buyerPhone'=>$billing->phone
        );

        $post = json_encode($post);

        if((isset($url) && trim($url) != '') && (isset($apiKey) && trim($apiKey) != '')) {
            try {
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                $length = strlen($post);

                $uname = base64_encode($apiKey);

                if($uname) {
                    $header = array(
                        'Content-Type: application/json',
                        'Content-Length: ' . $length,
                        'Authorization: Basic ' . $uname,
                        'X-BitPay-Plugin-Info: phplib1.5',
                    );

                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_PORT, 443);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // verify certificate
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // check existence of CN and verify that it matches hostname
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
                    curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);

                    $responseString = curl_exec($curl);

                    if($responseString == false) {
                        throw new \Exception('Error: ' . curl_error($curl));
                    } else {
                        if (!json_decode($responseString, true)) {
                            throw new \Exception('Error - Invalid JSON: ' . $responseString);
                        }
                    }

                    curl_close($curl);

                    return json_decode($responseString);
                } else {
                    curl_close($curl);
                    throw new \Exception('Invalid data found in apiKey value passed to bitpayRequestCurl. (Failed: base64_encode(apikey))');
                }
            } catch (Exception $e) {
                @curl_close($curl);
                throw new \Exception('Error: ' . $e->getMessage());
            }
        } else {
            throw new \Exception('Error: You must supply non-empty url and apiKey parameters.');
        }

    }

    public function coinbaseRequestCurl($orderId,$priceString){

        $config = Config::get('bitcoins');
        $apikey = $config["coinbase_api_key"];
        $apisecret = $config["coinbase_api_secret"];
        $nonce = sprintf('%0.0f',round(microtime(true) * 1000000));

        $url = $config['coinbase_url']."?nonce=" . $nonce;

        $parameters = [];
        $parameters["button"]["name"] = "KodeInfo Checkout Invoice";
        $parameters["button"]["custom"] = $orderId;
        $parameters["button"]["price_string"] = $priceString;
        $parameters["button"]["type"] = "buy_now";
        $parameters["button"]["subscription"] = false;
        $parameters["button"]["price_currency_iso"] = "USD";
        $parameters["button"]["description"] = "KodeInfo Checkout Invoice";
        $parameters["button"]["style"] = "custom_large";
        $parameters["button"]["include_email"] = true;
        $parameters["button"]["callback_url"] = $config['coinbase_notify_url'];
        $parameters = http_build_query($parameters, true);

        $signature = hash_hmac("sha256", $nonce . $url . $parameters, $apisecret);

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                "ACCESS_KEY: " . $apikey,
                "ACCESS_NONCE: " . $nonce,
                "ACCESS_SIGNATURE: " . $signature
            )));

        curl_setopt_array($ch, array(
            CURLOPT_POSTFIELDS => $parameters,
            CURLOPT_POST => true,
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        $decodeResponse = json_decode($response);

        if(sizeof($decodeResponse->errors)>0){
            Log::alert(Request::all());
            throw new \Exception($decodeResponse->errors[0]);
        }else{
            return $decodeResponse;
        }
    }

    //You can also use this method which works without Curl
    public function bitpayRequest($orderId,$grandTotal,$billing){

        $config = Config::get('bitcoins');

        $url = $config['bitpay_url'];
        $apiKey = $config['bitpay_api_key'];

        $post = array(
            'orderID' =>$orderId,
            'price' => $grandTotal,
            'currency' => 'USD',
            'itemDesc' => 'KodeInfo Checkout Invoice',
            'notificationEmail'=>'developers@kodeinfo.com',
            'notificationURL'=>$config['bitpay_notify_url'],
            'physical'=>true,
            'fullNotifications'=>true,
            'buyerName'=>$billing->name,
            'buyerAddress1'=>$billing->address,
            'buyerAddress2'=>'',
            'buyerCity'=>$billing->city,
            'buyerState'=>$billing->state,
            'buyerZip'=>$billing->zip,
            'buyerEmail'=>$billing->email,
            'buyerPhone'=>$billing->phone
        );

        $post = json_encode($post);
        $length = strlen($post);
        $basic_auth = base64_encode($apiKey);

        $context_options = array(
            "http" => array(
                "method" => "POST",
                "header"  => "Content-type: application/json\r\n" .
                    "Content-Length: $length\r\n" .
                    "Authorization: Basic $basic_auth\r\n"
            )
        );

        $context_options["http"]["content"] = $post;
        $context = stream_context_create($context_options);
        $response = file_get_contents($url, false, $context);

        $decodeResponse = json_decode($response);

        if(empty($decodeResponse)){
            Log::alert(Request::all());
            throw new \Exception("Unable to process payment contact customer support");
        }else{
            return $decodeResponse;
        }

    }

}
