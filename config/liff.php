<?php
return [
    'entry_token' => env('LIFF_ENTRY_TOKEN', 'gX3StE0v6h'), // 審査済みのトランポリン用トークン
    'liff_id'     => env('LIFF_ID', '2007333449-YeL40yrd'),
    'default_shop_id'  => env('LIFF_DEFAULT_SHOP_ID'),
];

//テスト用
// return [
//     'entry_token' => env('LIFF_ENTRY_TOKEN', 'dummyshop456'),
//     'liff_id'     => env('LIFF_ID', '2007333447-pWdJ8mL7'),
// ];