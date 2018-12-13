<?php
	require_once __DIR__ . '/lineBot.php';
	require('../vendor/autoload.php');
	use Kreait\Firebase\Factory;
	use Kreait\Firebase\ServiceAccount;
	$serviceAccount = ServiceAccount::fromJsonFile('../booking-lapang-ad110303eebf.json');
	$serviceAccount2 = ServiceAccount::fromJsonFile('../newagent-f2ffc-98936eb37a7e.json');
		$firebase = (new Factory)
			->withServiceAccount($serviceAccount)
			->create();
		$database = $firebase->getDatabase();
		$firebase2 = (new Factory)
			->withServiceAccount($serviceAccount2)
			->create();
		$database2 = $firebase2->getDatabase();
		
	$bot = new Linebot();
	$userid = $bot->getUserId();
	$cek_user = $database->getReference("line_user/".$userid)->getSnapshot()->exists();
	//$displayName = $bot->getDisplayName($userid);
	if (!$cek_user){
		$create_user = $database->getReference("line_user/".$userid)
						->set([
							"status" => 0,
							"dialogflow" => 0,
							"last_button" => 0
						]);
	}
	
/* 	if (strtoupper($text) == "PESAN"){
		$bot->replyTemplate($image_carousel_template); 
	}else{
		$bot->reply($text);
	} */
	
	
	

	
/* 	$button_template = 	array(
						  "type"=> "template",
						  "altText"=> "This is a buttons template",
						  "template"=> array(
							  "type"=> "buttons",
							  "thumbnailImageUrl"=> "https://example.com/bot/images/image.jpg",
							  "imageAspectRatio"=> "rectangle",
							  "imageSize"=> "cover",
							  "imageBackgroundColor"=> "#FFFFFF",
							  "title"=> "Menu",
							  "text"=> "Please select",
							  "defaultAction"=> array(
								  "type"=> "uri",
								  "label"=> "View detail",
								  "uri"=> "http://example.com/page/123"
							  ),
							  "actions"=> array (
								  array(
									"type"=> "postback",
									"label"=> "Buy",
									"data"=> "action=buy&itemid=123"
								  ),
								  array(
									"type"=> "postback",
									"label"=> "Add to cart",
									"data"=> "action=add&itemid=123"
								  ),
								  array(
									"type"=> "uri",
									"label"=> "View detail",
									"uri"=> "http://example.com/page/123"
								  )
							  )
						  )
						);  */
	$text = $bot->getMessageText();
	
	$status = $database->getReference("line_user/".$userid."/status")->getSnapshot()->getValue();
	$last_button_pressed = $database->getReference("line_user/".$userid."/last_button")->getSnapshot()->getValue();
	
	if ($status == 0){
		if (strtoupper(substr($text, 0, 6) == "DAFTAR")){
			$noHP = substr($text, 7, strlen($text));
			$message = array(
			  "type"=> "template",
			  "altText"=> "This is a buttons template",
			  "template"=> array(
				  "type"=> "confirm",
				  "title"=> "Menu",
				  "text"=> "Nomor HP anda adalah ".$noHP."?",
				  "actions"=> array(
					  array(
						"type"=> "postback",
						"label"=> "ya",
						"data"=> "confirm=ya&".$noHP
					  ),
					  array(
						"type"=> "postback",
						"label"=> "tidak",
						"data"=> "confirm=tidak&"
					  )
				  )
			  )
			);
			$bot->replyTemplate($message);
		}elseif(substr($bot->postbackEvent(), 8, 2)=="ya"){
			$create_user = $database->getReference("line_user/".$userid)
				->set([
					"status" => 1,
					"dialogflow" => 0,
					"last_button" => 0,
					"noHP" => substr($bot->postbackEvent(), 10, strlen($bot->postbackEvent()))
				]);
			$message = "Pendaftaran berhasil, silakan ketik PESAN untuk memulai pemesanan";
			$bot->reply($message);
		}else{
			$message = "Silahkan mendaftar terlebih dahulu dengan mengetik DAFTAR_[Nomor HP]";
			$bot->reply($message);
		}
	
	}else{
		if(isset($text)){
			if (strtoupper($text) == "PESAN"){
				 $message = array(
										  "type"=> "template",
										  "altText"=> "this is a carousel template",
										  "template"=> array(
											  "type"=> "image_carousel",
											  "columns"=> array(
												  array(
													"imageUrl"=> "https://d2gg9evh47fn9z.cloudfront.net/800px_COLOURBOX4151260.jpg",
													"action"=> array(
													  "type"=> "postback",
													  "label"=> "Futsal",
													  "data"=> ($last_button_pressed+1)."_olahraga=futsal"
													)
												  ),
												  array(
													"imageUrl"=> "https://d2gg9evh47fn9z.cloudfront.net/800px_COLOURBOX6614021.jpg",
													"action"=> array(
													  "type"=> "postback",
													  "label"=> "Basket",
													  "data"=> ($last_button_pressed+1)."_olahraga=basket"
													)
												  ),
												  array(
													"imageUrl"=> "https://d2gg9evh47fn9z.cloudfront.net/800px_COLOURBOX10285843.jpg",
													"action"=> array(
													  "type"=> "postback",
													  "label"=> "Badminton",
													  "data"=> ($last_button_pressed+1)."_olahraga=badminton"
													)
												  ),
												  array(
													"imageUrl"=> "https://d2gg9evh47fn9z.cloudfront.net/800px_COLOURBOX10342925.jpg",
													"action"=> array(
													  "type"=> "postback",
													  "label"=> "Tennis",
													  "data"=> ($last_button_pressed+1)."_olahraga=tennis"
													)
												  )
											  )
										  )
				);
				
				$bot->replyTemplate($message); 
			
			}
		}else{
			
			$postback = explode("_", $bot->postbackEvent());
			$postbackdata = $postback[1];
			
			if (substr($postbackdata, 0, 8) == "olahraga"){
				$message = 	array(
					  "type"=> "template",
					  "altText"=> "this is a carousel template",
					  "template"=> array(
						  "type"=> "carousel",
						  "columns"=> array(
						  ),
						  "imageAspectRatio"=> "rectangle",
						  "imageSize"=> "cover"
					  )
				);
				$olahraga = strtoupper(substr($postbackdata, 9, strlen($postbackdata)));
				$lapangan = $database->getReference('olahraga/'.$olahraga.'/fields')->getSnapshot()->getValue();
				$img_url = $database->getReference('olahraga/'.$olahraga)->getSnapshot()->getValue();
				$i = 0;
				foreach($lapangan as $lapangan)
					{
						
						$alamat = $database->getReference('fields/'.$lapangan["lapangan"].'/address')->getSnapshot()->getValue();
						$message["template"]["columns"][$i] = array(
																"thumbnailImageUrl"=> $img_url["image_url"],
																"imageBackgroundColor"=> "#FFFFFF",
																"title"=> ucfirst($lapangan["lapangan"])." - ".ucfirst($olahraga),
																"text"=> $alamat,
																"actions"=> array(
																	array(
																		"type"=> "postback",
																		"label"=> "Pilih Lapangan",
																		"data"=> ($last_button_pressed+1)."_lapangan=".$lapangan["lapangan"]."&olahraga=".$olahraga
																	)
																)
															  );
															
						$i++; 
					} 
				//$bot->reply(json_encode($img_url["image_url"]));
				$bot->replyTemplate($message);
					
			} else if (substr($postbackdata, 0, 8) == "lapangan"){
				$postdata = explode("&",$postbackdata);
				$lapangan = substr($postdata[0], 9, strlen($postdata[0]));
				$olahraga = substr($postdata[1], 9, strlen($postdata[1]));
				$create_temp_pesanan = $database->getReference("line_user/".$userid."/temp_pesanan")
					->set([
						"olahraga" => $olahraga,
						"lapangan" => $lapangan
					]);
				$message = array(
							  "type"=> "template",
							  "altText"=> "this is a date template",
							  "template"=> array(
								  "type"=> "buttons",
								  "text"=> "Lapangan : ".ucfirst($lapangan)."\nOlahraga : ".ucfirst($olahraga),
								  "actions"=> array(
										array(  
										   "type"=>"datetimepicker",
										   "label"=>"Select date",
										   "data"=>($last_button_pressed+1)."_waktu&lapangan=".$lapangan."&olahraga=".$olahraga,
										   "mode"=>"datetime",
										   "initial"=>"2017-12-25t00:00",
										   "max"=>"2018-01-24t23:59",
										   "min"=>"2017-12-25t00:00"
										)
								  )
							  )
							);
				$bot->replyTemplate($message);
				
			}else if (substr($postbackdata, 0, 5) == "waktu"){
				$postdata = explode("&",$postbackdata);
				$lapangan = substr($postdata[1], 9, strlen($postdata[1]));
				$olahraga = substr($postdata[2], 9, strlen($postdata[2]));
				$datetime = $bot->postbackdateEvent();
				$datetimesplit = explode("T",$datetime);
				$date	  = $datetimesplit[0];
				$time	  = $datetimesplit[1];
				$create_temp_pesanan = $database->getReference("line_user/".$userid."/temp_pesanan")
					->set([
						"olahraga" => $olahraga,
						"lapangan" => $lapangan
					]);

					$message = array(
								  "type"=> "template",
								  "altText"=> "This is a buttons template",
								  "template"=> array(
									  "type"=> "buttons",
									  "title"=> "".ucfirst($lapangan)." - ".ucfirst($olahraga),
									  "text"=> $date." ".$time."\n"."Please select",
									  "actions"=> array(
										  array(
											"type"=> "postback",
											"label"=> "1 Jam",
											"data"=> ($last_button_pressed+1)."_durasi=1&waktu=".$datetime."&lapangan=".$lapangan."&olahraga=".$olahraga
										  ),
										  array(
											"type"=> "postback",
											"label"=> "2 Jam",
											"data"=> ($last_button_pressed+1)."_durasi=2&waktu=".$datetime."&lapangan=".$lapangan."&olahraga=".$olahraga
										  ),
										  array(
											"type"=> "postback",
											"label"=> "3 Jam",
											"data"=> ($last_button_pressed+1)."_durasi=3&waktu=".$datetime."&lapangan=".$lapangan."&olahraga=".$olahraga
										  ),
										  array(
											"type"=> "postback",
											"label"=> "4 Jam",
											"data"=> ($last_button_pressed+1)."_durasi=4&waktu=".$datetime."&lapangan=".$lapangan."&olahraga=".$olahraga
										  )
									  )
								  )
								);
				$test = $database2->getReference("testing")
					->push([
						"test" => json_encode($message)
					]); 
				$bot->replyTemplate($message);
				
			}else if (substr($postbackdata, 0, 6) == "durasi"){
				$postdata = explode("&",$postbackdata);
				$lapangan = substr($postdata[2], 9, strlen($postdata[2]));
				$olahraga = substr($postdata[3], 9, strlen($postdata[3]));
				$datetime = substr($postdata[1], 6, strlen($postdata[1]));
				$durasi	  = substr($postdata[0], 7, strlen($postdata[0]));
				$datetimesplit = explode("T",$datetime);
				$date	  = $datetimesplit[0];
				$time	  = $datetimesplit[1];
				$create_temp_pesanan = $database->getReference("line_user/".$userid."/temp_pesanan")
					->set([
						"olahraga" => $olahraga,
						"lapangan" => $lapangan
					]);

				$message = array(
							  "type"=> "template",
							  "altText"=> "This is a buttons template",
							  "template"=> array(
								  "type"=> "confirm",
								  "title"=> "Menu",
								  "text"=> "Apakah Pesanan ".$lapangan." - ".$olahraga." - ".$date." ".$time." - ".$durasi." Jam /nSudah Benar?",
								  "actions"=> array(
									  array(
										"type"=> "postback",
										"label"=> "ya",
										"data"=> ($last_button_pressed+1)."_confirm=ya&durasi=".$durasi."&waktu=".$datetime."&lapangan=".$lapangan["lapangan"]."&olahraga=".$olahraga
									  ),
									  array(
										"type"=> "postback",
										"label"=> "tidak",
										"data"=> ($last_button_pressed+1)."_confirm=tidak&durasi=".$durasi."&waktu=".$datetime."&lapangan=".$lapangan["lapangan"]."&olahraga=".$olahraga
									  )
								  )
							  )
							);
				$test = $database2->getReference("testing")
					->push([
						"test" => json_encode($message)
					]); 
				$bot->replyTemplate($message);
			}else if (substr($postbackdata, 0, 7) == "confirm"){
				$postdata = explode("&",$postbackdata);
				$lapangan = substr($postdata[3], 9, strlen($postdata[3]));
				$olahraga = substr($postdata[4], 9, strlen($postdata[4]));
				$datetime = substr($postdata[2], 6, strlen($postdata[2]));
				$durasi	  = substr($postdata[1], 7, strlen($postdata[1]));
				$confirm  = substr($postdata[0], 8, strlen($postdata[0]));
				$datetimesplit = explode("T",$datetime);
				$date	  = $datetimesplit[0];
				$time	  = $datetimesplit[1];
				if ($confirm == "ya"){
					$message = "Mohon menunggu konfirmasi";
				}else{
					$message = "Silahkan memesan lapangan dengan mengtik 'Pesan'";
				}
				$create_temp_pesanan = $database->getReference("line_user/".$userid."/temp_pesanan")
					->set([
						"olahraga" => $olahraga,
						"lapangan" => $lapangan
					]);


				$bot->reply($message);
			}  
			
			
			
			
		}
	}
	//$lapangan = $database->getReference('olahraga/FUTSAL')->getSnapshot()->getValue();
	//echo json_encode($lapangan);
	//$child = $olahraga->hasChild("address");
	//$value = $olahraga->getValue();
	//$key = $olahraga->getkey();
	//$reference = $olahraga->getReference();
	//$img_url = $database->getReference('olahraga/FUTSAL/image_url')->getSnapshot()->getValue();
	$alamat = $database->getReference('fields/PASAGA/address')->getSnapshot()->getValue();
	//echo json_encode($child);
	//echo json_encode($value);
	//echo json_encode($key);
	//echo json_encode($reference);
	//echo json_encode($img_url);
	echo json_encode($alamat);
	echo "Bond";
	
/* 	foreach($json->people as $item)
	{
		if($item->id == "8097")
		{
			echo $item->content;
		}
	} */
	

