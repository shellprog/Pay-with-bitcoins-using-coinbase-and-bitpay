<?php
 return [
     'bitpay_url'     => 'https://bitpay.com/api/invoice/',
     'bitpay_api_key'  => '',
     'bitpay_notify_url' => URL::to('/')."/notify/bitpay",
     'coinbase_url'     => 'https://coinbase.com/api/v1/buttons',
     'coinbase_api_key'  => '',
     'coinbase_api_secret'  => '',
     'coinbase_notify_url' => URL::to('/')."/notify/coinbase",
 ];