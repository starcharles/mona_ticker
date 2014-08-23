<?php
require_once "Mail.php";

//ask,bidのしきい値と、現在のbtc価格を設定
$target_ask=18; //この値以上で通知
$target_bid=10; //この値以下で通知

$btc_jpy=55300;//BtcPrice in Japan


while(1){
	call_ticker($target_ask,$target_bid,$btc_jpy);
	sleep(60); #10秒ごとに実行する
}


function call_ticker($target_ask,$target_bid,$btc_jpy){

//&monatr($btc_jpy,$ask,$bid);#もなとれ価格は別に表示

//apiアドレスを配列で複数指定
$api_add = array(
	"etwings" => "https://exchange.etwings.com/api/1/ticker/mona_jpy",
	//"もなっくす" => "https://monax.jp/api/pricemncjpyv2",
	);


//価格情報を取得する
foreach ($api_add as $key =>$value){
	$res=http_get($value);
	//print($res);
	preg_match('/{.*\}/', $res,$json);
	$json=json_decode($json[0]);
	$ask=$json->ask;
	$bid=$json->bid;

	print "$key  ask:$ask bid:$bid \n";

	if($ask>$target_ask || $bid<$target_bid){
		send_mail($key,$ask,$bid);
	}



}
}

function send_mail($key,$ask,$bid){
//日本語メールを送る際に必要
mb_language("Japanese");
mb_internal_encoding("UTF-8");


$sendto="＊＊＊＊";  //あなたのメール送信先
$sendfrom="＊＊＊＊"; //メール送信元（自由）

// SMTPサーバーの情報を連想配列にセット
$params = array(
  "host" => "＊＊＊＊＊＊",   // SMTPサーバー名
  "port" => ＊＊＊,              // ポート番号
  "auth" => true,            // SMTP認証を使用する
  "username" => "＊＊＊＊＊",  // SMTPのユーザー名
  "password" => "＊＊＊＊＊＊"   // SMTPのパスワード
);

// PEAR::Mailのオブジェクトを作成
// ※バックエンドとしてSMTPを指定
$mailObject = Mail::factory("smtp", $params);


// 送信先のメールアドレス
$recipients = "$sendto";

// メールヘッダ情報を連想配列としてセット
$headers = array(
  "To" => "$sendto",         // →ここで指定したアドレスには送信されない
  "From" => "$sendfrom",
  "Subject" =>  mb_encode_mimeheader("mona_tickerからの通知") // 日本語の件名を指定する場合、mb_encode_mimeheaderでエンコード
);

// メール本文
$body="mona_tickerからの通知\n\n $key  買値:$ask 売値:$bid \n";

// sendメソッドでメールを送信
$mailObject->send($recipients, $headers, $body);
}

?>