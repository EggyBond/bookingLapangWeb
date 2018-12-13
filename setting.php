<?php

class Setting {
	public function getChannelAccessToken(){
		$channelAccessToken = "V14+7cf4ncME2w1ax4ikDa+IjdzTvMVEFBCeBEMxohKsTdN0BS+bmtxDyH+IRV53akXOjZVAg7ncbIWr3wraHQbq99j86WL2ePl37ZP7r4dEupHverzZs82YqthAtZnNwNzvGm34Daeo3CtRV3kabAdB04t89/1O/w1cDnyilFU=";
		return $channelAccessToken;
	}
	public function getChannelSecret(){
		$channelSecret = "45139ac5eb301dc971c8257c14ef24b6";
		return $channelSecret;
	}
	public function getApiReply(){
		$api = "https://api.line.me/v2/bot/message/reply";
		return $api;
	}
	public function getApiPush(){
		$api = "https://api.line.me/v2/bot/message/push";
		return $api;
	}
}
