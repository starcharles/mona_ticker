#!/home/naoto/.plenv/shims/perl

use strict;
use warnings;
use URI;
use LWP::UserAgent;
use utf8;
use JSON qw/encode_json decode_json/;
use Encode;
use Email::MIME;
use Email::MIME::Creator;
use Email::Sender::Simple 'sendmail';
use Email::Sender::Transport::SMTP;



#ask,bidのしきい値と、現在のbtc価格を設定
#しきい値を超えるとメール通知が行われます
my $ask=16;
my $bid=10;
my $btc_jpy=65300;

while(1){
        call_ticker();
	sleep(1800); #指定秒数後に再実行
}


sub call_ticker{

my $date;
my %tmp;

#$tmp　に各サイトの値（ask,bid)を保存
$tmp{"monatr"}=	&monatr();#もなとれ価格は別に表示


#api取得先アドレスを配列で複数指定
my %api_add=(
	"etwings" => "https://exchange.etwings.com/api/1/ticker/mona_jpy",
	"monax" => "https://monax.jp/api/pricemncjpyv2",
	);


#LWPで接続し、価格情報JSONをGETする
foreach my $key (keys %api_add){
	my $uri=URI->new($api_add{$key});
	my $ua = LWP::UserAgent->new();
	my $res= $ua-> get($uri);
	warn $res->status_line if $res->is_error;

#受け取ったJSONデータをパース
my $json=$res->content;
my $ref=decode_json($json); #Perlのデータ構造にデコード変換


print "$key => ask:$ref->{ask}  bid:$ref->{bid}\n";

#%tmpに保存
$tmp{$key}={ask=>$ref->{ask},bid=>$ref->{bid}};

}

#最後に%tmpの各ask,bidがしきい値を満たすか調べ、満たす場合はsend_mail

foreach my $key (keys %tmp){
    if(($tmp{$key}{ask}>=$ask) || ($tmp{$key}{bid}<=$bid)){

		&send_mail(\%tmp); #メール送信
		last;
    }
}
	print "\n\n";
}


sub monatr{

	my $uri=URI->new("https://api.monatr.jp/ticker");

#取得したい通貨ペアを指定

	$uri->query_form(
		market=> "MONA_BTC"
		);

	my $ua = LWP::UserAgent->new();
	my $res= $ua-> get($uri);
	warn $res->status_line if $res->is_error;

	#JSONをパース
	my $json=$res->content;
	my $ref=decode_json($json);
	my $cur_ask=$ref->{current_ask}*$btc_jpy;
	my $cur_bid=$ref->{current_bid}*$btc_jpy;
	my $key="monatr";

	#$tmp{$key}={ask=>$cur_ask,bid=>$cur_bid};

	printf ("monatr=> ask:%.1f  bid:%.1f \n",$cur_ask,$cur_bid);
	
	return {ask=>$cur_ask,bid=>$cur_bid};

}



sub send_mail{
    my $ref_tmp=shift;
    my $from=q/"monatick" <送信元アドレス>/;
    my $to=q/"送信先名" <送信先アドレス>/;
    my $subject=q/価格情報のお知らせ　From mona_ticker/;
    my $text;

    $text="monatickからmonacoin価格情報のお知らせです。\n\n\n";

	foreach my $key(keys %$ref_tmp){
              #print "$key \n";
	    $text.="$key=>  ask:$ref_tmp->{$key}->{ask}  bid:$ref_tmp->{$key}->{bid} \n";
        }

my $email = Email::MIME->create(
  header => [
    From    => encode('MIME-Header-ISO_2022_JP' => $from),
    To      => encode('MIME-Header-ISO_2022_JP' => $to),
    Subject => encode('MIME-Header-ISO_2022_JP' => $subject ),
  ],
  attributes => {
    content_type => 'text/plain',
    charset      => 'ISO-2022-JP',
    encoding     => '7bit',
  },
  body => encode('iso-2022-jp' =>

		 "$text"

),

);

#自分の設定したSMTPサーバからメールを送信
my $smtp = Email::Sender::Transport::SMTP->new({
               'host'          => 'SMTPサーバのアドレス',
               'port'          => ポート番号,
               'sasl_username' => 'ユーザ名＊＊＊＊＊',
               'sasl_password' => 'パスワード＊＊＊＊＊＊',
           });

sendmail($email, { 'transport' => $smtp });

}

