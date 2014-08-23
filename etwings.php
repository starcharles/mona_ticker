<?php
//require_once "Mail.php";

//ask,bidのしきい値と、現在のbtc価格を設定
$target_ask=18; //この値以上で通知
$target_bid=10; //この値以下で通知

$btc_jpy=55300;//BtcPrice in Japan



call_ticker($target_ask,$target_bid,$btc_jpy);


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

	print "$key  売値:$ask  買値:$bid \n";

	if($ask>$target_ask || $bid<$target_bid){
		//send_mail($key,$ask,$bid);
	}



}
}

/*
function send_mail($key,$ask,$bid){
//日本語メールを送る際に必要
mb_language("Japanese");
mb_internal_encoding("UTF-8");


$sendto="charlie_inthesky@i.softbank.jp";
$sendfrom="new_comer123@yahoo.co.jp";

// SMTPサーバーの情報を連想配列にセット
$params = array(
  "host" => "smtp.mail.yahoo.co.jp",   // SMTPサーバー名
  "port" => 587,              // ポート番号
  "auth" => true,            // SMTP認証を使用する
  "username" => "new_comer123",  // SMTPのユーザー名
  "password" => "ns21!!2718"   // SMTPのパスワード
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
$body="mona_tickerからの通知\n\n $key  ask:$ask bid:$bid \n";

// 日本語なのでエンコード
//$body = mb_convert_encoding($body, "ISO-2022-JP", "UTF-8");
// sendメソッドでメールを送信
$mailObject->send($recipients, $headers, $body);
}
*/
?>