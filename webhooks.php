<?php // callback.php
class messageAQI{
    private function checkPollutionAQI($value){
		try{
			if($value<=25){
				return '􀁋 คุณภาพอากาศดีมาก เหมาะสำหรับกิจกรรมกลางแจ้งและการท่องเที่ยว';
			}else if($value<=50&&$value>25){
				return '􀀹 คุณภาพอากาศดี สามารถทำกิจกรรมกลางแจ้งและการท่องเที่ยวได้ตามปกติ';
			}else if($value<=100&&$value>50){
				return '􀁟 คุณภาพอากาศปานกลาง สามารถทำกิจกรรมกลางแจ้งได้ตามปกติและเฝ้าระวังสุขภาพ';
			}else if($value<=150&&$value>100){
				return '􀁻 คุณภาพอากาศอากาศไม่ดี ควรลดระยะเวลาการทำกิจกรรมกลางแจ้ง (ผู้ที่ได้รับผลกระทบได้แก่ กลุ่มคนที่มีปัญหาหรือมีโรคเกี่ยวกับด้านทางเดินหายใจเป็นหลัก)';
			}else if($value<=200&&$value>150){
				return '􀀧 เริ่มมีผลกระทบต่อสุขภาพ ควรลดระยะเวลาการทำกิจกรรมกลางแจ้ง';
			}else if($value<=300&&$value>200){
				return '􀀵 มีผลกระทบต่อสุขภาพ ทุกคนควรหลีกเลี่ยงกิจกรรมกลางแจ้ง';
			}else{
				return '􀁌 มีผลกระทบต่อสุขภาพอย่างมาก ทุกคนไม่ควรทำกิจกรรมกลางแจ้ง';
			}
		}catch(\Exception $e){
			return '';
		}
	}
	private function checkPollution($value,$pollution){
		try{
			if($value<=$pollution[0]){
				return '􀀄 ยอดเยี่ยม';
			}else if($value<=$pollution[1]&&$value>$pollution[0]){
				return '􀀃 ดี';
			}else if($value<=$pollution[2]&&$value>$pollution[1]){
				return '􀀔 ปานกลาง';
			}else if($value<=$pollution[3]&&$value>$pollution[2]){
				return '􀀛 เริ่มส่งผลกระทบ';
			}else{
				return '􀀠 ส่งผลกระทบต่อสุขภาพ';
			}
		}catch(\Exception $e){
			return '';
		}
	}
	private function getPollution($lat,$lng){
		$text = '';
        $data = $this->setPollution($lat,$lng);
		try{
			foreach($data as $key=>$value){
				if(!empty($value['value'])){
					switch($key){
						case 'time':
						$text=$text.$value['text'].': '.$value['value']."\n";
						break;
						case 'city':
						$text=$text.$value['text'].":\n".$value['value']."\n ห่างจากจุดตรวจ ".$this->distance($value['lat'],$value['lng'],$lat,$lng)."\n";
						break;
						case 'co':
						$text=$text.$value['text'].': '.$this->checkPollution($value['value'],[4.4,6.4,9.0,30]).' ('.$value['value'].")\n";
						break;
						case 'no2':
						$text=$text.$value['text'].': '.$this->checkPollution($value['value'],[60,106,170,340]).' ('.$value['value'].")\n";
						break;
						case 'so2':
						$text=$text.$value['text'].': '.$this->checkPollution($value['value'],[100,200,300,400]).' ('.$value['value'].")\n";
						break;
						case 'o3':
						$text=$text.$value['text'].': '.$this->checkPollution($value['value'],[35,50,70,120]).' ('.$value['value'].")\n";
						break;
						case 'pm10':
						$text=$text.$value['text'].': '.$this->checkPollution($value['value'],[50,80,120,180]).' ('.$value['value'].")\n";
						break;
						case 'pm25':
						$text=$text.$value['text'].': '.$this->checkPollution($value['value'],[25,37,50,90]).' ('.$value['value'].")\n";
						break;
						case 'aqi':
						$text=$text.$value['text'].' : '.$this->checkPollutionAQI($value['value']).' ('.$value['value'].')';
						break;
					}
				}
			}
			if(!empty($text)){
				$text=$text."\n________________________\n";
			}
			return $text;
		}catch(\Exception $e){
			return '';
		}
    }
    private function distance($latitudeFrom,$longitudeFrom,$latitudeTo,$longitudeTo,$earthRadius=6371000){
		try{
			$latFrom=deg2rad($latitudeFrom);
			$latTo=deg2rad($latitudeTo);
			$lonDelta=deg2rad($longitudeTo)-deg2rad($longitudeFrom);
			$miles=atan2(sqrt(pow(cos($latTo)*sin($lonDelta),2)+pow(cos($latFrom)*sin($latTo)-sin($latFrom)*cos($latTo)*cos($lonDelta),2)),sin($latFrom)*sin($latTo)+cos($latFrom)*cos($latTo)*cos($lonDelta))*$earthRadius;
			if($miles>=1000){
				return number_format(($miles/1000),2,'.','').' กิโลเมตร';
			}else{
				return number_format(($miles/1000),2,'.','').' เมตร';
			}
		}catch(\Exception $e){
			return '';
		}
	}
    private function setPollution($lat,$lng){
        $resPollution = $this->getPollutionAPI($lat,$lng);
		try{
			$data = [
				'time'=>[
					'value'=>'',
					'text'=>'􀀪 มลพิษอากาศ'
				],
				'city'=>[
					'lat'=>'',
					'lng'=>'',
					'value'=>'',
					'text'=>'สถานีตรวจมลพิษทางอากาศ'
				],
				'co'=>[
					'value'=>'',
					'text'=>"\r\r\r\r\rCO"
				],
				'no2'=>[
					'value'=>'',
					'text'=>"\r\r\rNO2"
				],
				'so2'=>[
					'value'=>'',
					'text'=>"\r\r\rSO2"
				],
				'o3'=>[
					'value'=>'',
					'text'=>"\r\r\r\r\rO3"
				],
				'pm10'=>[
					'value'=>'',
					'text'=>'PM10'
				],
				'pm25'=>[
					'value'=>'',
					'text'=>'PM2.5'
				],
				'aqi'=>[
					'value'=>'',
					'text'=>'ดัชนีคุณภาพอากาศ'
				]
			];
			if(!empty($resPollution->data->time->s)){
				$data['time']['value']=$this->dateThai($resPollution->data->time->s);
			}
			if(!empty($resPollution->data->city)){
				if(!empty($resPollution->data->city->name)){
					if($resPollution->data->city->name=='Provincial Health Office, Loei, Thailand (สำนักงานสาธารณสุขจังหวัดเลย)'){
						$data['city']['value']='Ministry of Science and Technology, Bangkok, Thailand (กระทรวงวิทยาศาสตร์และเทคโนโลยี)';
					}else{
						$data['city']['value']=$resPollution->data->city->name;
					}
				}
				if(!empty($resPollution->data->city->geo)){
					$data['city']['lat']=$resPollution->data->city->geo[0];
					$data['city']['lng']=$resPollution->data->city->geo[1];
				}
			}
			if(!empty($resPollution->data->aqi)){
				$data['aqi']['value']=$resPollution->data->aqi;
			}
			if(!empty($resPollution->data->iaqi)){
				if(!empty($resPollution->data->iaqi->co->v)){
					$data['co']['value']=$resPollution->data->iaqi->co->v;
				}
				if(!empty($resPollution->data->iaqi->no2->v)){
					$data['no2']['value']=$resPollution->data->iaqi->no2->v;
				}
				if(!empty($resPollution->data->iaqi->o3->v)){
					$data['o3']['value']=$resPollution->data->iaqi->o3->v;
				}
				if(!empty($resPollution->data->iaqi->pm10->v)){
					$data['pm10']['value']=$resPollution->data->iaqi->pm10->v;
				}
				if(!empty($resPollution->data->iaqi->pm25->v)){
					$data['pm25']['value']=$resPollution->data->iaqi->pm25->v;
				}
				if(!empty($resPollution->data->iaqi->so2->v)){
					$data['so2']['value']=$resPollution->data->iaqi->so2->v;
				}
			}
			return $data;
		}catch(\Exception $e){
			return [];
		}
    }
    private function checkWeatherCondition($value){
		switch($value){
			case 1:
			return '􀀭 ท้องฟ้าแจ่มใส (Clear)';
			break;
			case 2:
			return '􀂄 มีเมฆบางส่วน (Partly Cloudy)';
			break;
			case 3:
			return '􀂬 เมฆเป็นส่วนมาก (Cloudy)';
			break;
			case 4:
			return '􀂅 มีเมฆมาก (Overcast)';
			break;
			case 5:
			return '􀀉 ฝนตกเล็กน้อย (Light Rain)';
			break;
			case 6:
			return '􀂪 ฝนปานกลาง (Moderate Rain)';
			break;
			case 7:
			return '􀀩 ฝนตกหนัก (Heavy Rain)';
			break;
			case 8:
			return '􀀺 ฝนฟ้าคะนอง (Thunderstorm)';
			break;
			case 9:
			return '􀂫 อากาศหนาวจัด (Very Cold)';
			break;
			case 10:
			return '􀀜 อากาศหนาว (Cold)';
			break;
			case 11:
			return '􀂈 อากาศเย็น (Cool)';
			break;
			case 12:
			return '􀂩 อากาศร้อนจัด (Very Hot)';
			break;
			default:
			return '􀁞 ไม่ทราบผล พื้นที่นอกเขตควบคุม';
		}
    }
	private function getTMDAPI($lat,$lng){
		$response = [];
		$ch=curl_init('https://data.tmd.go.th/nwpapi/v1/forecast/location/hourly/at?lat='.$lat.'&lon='.$lng);
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"GET");
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array("accept: application/json",
			"authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImM2MzIzNzM4NTYzOTAwMTA1ZWZlMDVjMGEwZTExNjg3ODY0M2YwNTk4YTJiNDhkOWFiNTFkN2ZlMzU5Y2ZhZGViM2UxZjE0NTMwZDUwMzYwIn0.eyJhdWQiOiIyIiwianRpIjoiYzYzMjM3Mzg1NjM5MDAxMDVlZmUwNWMwYTBlMTE2ODc4NjQzZjA1OThhMmI0OGQ5YWI1MWQ3ZmUzNTljZmFkZWIzZTFmMTQ1MzBkNTAzNjAiLCJpYXQiOjE1NTMxMDIwOTgsIm5iZiI6MTU1MzEwMjA5OCwiZXhwIjoxNTg0NzI0NDk4LCJzdWIiOiI0MTEiLCJzY29wZXMiOltdfQ.f9OeHjPdiCkqFSItHCJIc_aJc5sx1dwR83iUn4Ug0hh57inHDjAv6HwOgiHabHGmERjDHtUXVZ7BOVoXgNL8-IJpuXwcE2pAhcmec86WJReCPGooFha-m9xTAiWh8Hmrtksu35o_XDsKy31RkaQjnJPVoDrg83tANYyRaVk5722dE_Nhsqv0Y710afp7z6lpFc9D_sgI0DVdp8_LHH4ySm40jQZoBg_cCH_aHvXb1RmuvKWWUsD1ZRXaaDqKTWaXrLSXBoaMT6TjPntlI5XHwGeRl9MaHUtuGKMIpXkGmWEV_J-4Le6bvKU9e7x9X6w0jYcTXictMmVcgsh7tTI_7C-d0kop3SVU78YcZQdX832vHoSmoAQ122qtL-2x05mWdn1jwEcM52g1wWRrF5CkV-mYsXY1JJNGdURXzdWOkZQXF5sJddiOCxNZPffDfGY49YPSx7gumnAAgjFrv0rnudR_14T6g7mvUBuHTl2-oMUKZS-mpGVBLjfyBwRGdiCcPOaa13Z3gTFK-I4iqt0M9XwmyrNqipFZw3NDp0MkujiPtzzbd0dAMzze1U8b6rghG9ORMskMjzyiJp74T1YQUC5ZNGZUvWKsY-K-DfbWVj7kR_qnkm6k1s043XfLvcxCpFsJO5i2z96fbUyCWXd_YDyr49AdDbgO2uZORlvBiNg","cache-control: no-cache"));
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
		if(!curl_error($ch)){
			$response = curl_exec($ch);
		}
		curl_close($ch);
		return json_decode($response);
	}
	private function setWeather($lat,$lng){
		$resTMDAPI = $this->getTMDAPI($lat,$lng);
		try{
			$data = [
				'time'=>[
					'value'=>'',
					'text'=>'􀂳 พยากรณ์อากาศ'
				],
				'tc'=>[
					'value'=>'',
					'text'=>'อุณหภูมิ',
					'unit'=>'°C'
				],
				'rh'=>[
					'value'=>'',
					'text'=>'ความชื้น',
					'unit'=>'%'
				],
				'slp'=>[
					'value'=>'',
					'text'=>'ความกดอากาศที่ระดับน้ำทะเล',
					'unit'=>'hpa'
				],
				'rain'=>[
					'value'=>'',
					'text'=>'ปริมาณฝนรายชั่วโมง',
					'unit'=>'mm'
				],
				'cond'=>[
					'value'=>'',
					'text'=>''
				]
			];
			foreach($resTMDAPI->WeatherForecasts as $array){
				foreach($array->forecasts as $obj){
					if(!empty($obj->time)){
						$data['time']['value'] = $this->dateThai(date_format(date_create($obj->time),'Y/m/d H:i:s'));
					}
					foreach($obj->data as $key=>$value){
						if(isset($data[$key])){
							$data[$key]['value'] = $value;
						}
					}
				}
			}
			return $data;
		}catch(\Exception $e){
			return [];
		}
	}
	private function getWeather($lat,$lng){
		$data=$this->setWeather($lat,$lng);
		try{
			$text = '';
			foreach($data as $key=>$value){
				if(!empty($value['value'])){
					if($key=='time'){
						$text=$text.$value['text'].': '.$value['value']."\n";
					}else if($key=='cond'){
						$text=$text.$this->checkWeatherCondition($value['value']);
					}else{
						$text=$text.$value['text'].': '.$value['value'].' '.$value['unit']."\n";
					}
				}
			}
			if(!empty($text)){
				$text=$text."\n________________________\n";
			}
			return $text;
		}catch(\Exception $e){
			return '';
		}
	}
	private function dateThai($strDate){
		$strTime=strtotime($strDate);
		$strMonthCut = ["","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
		return date("j",$strTime)." ".$strMonthCut[date("n",$strTime)]." ".(date("Y",$strTime)+543).", ".date("H",$strTime).":".date("i",$strTime);
	}
    private function getPollutionAPI($lat,$lng){
		$response = [];
		$ch=curl_init('https://api.waqi.info/feed/geo:'.$lat.';'.$lng.'/?token=1005c9bbc3fc91acbd3fc5d05c1b41e58335dd5d');
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"GET");
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
		if(!curl_error($ch)){
			$response = curl_exec($ch);
		}
		curl_close($ch);
		return json_decode($response);
	}
    public function replytoken($replyToken,$messages){
        $url = 'https://api.line.me/v2/bot/message/reply';
$data = [
'replyToken' => $replyToken,
'messages' => [$messages],
];
$post = json_encode($data);
$headers = array('Content-Type: application/json', 'Authorization: Bearer JYNgvjr8mHBeU+I0abPoL9kwOaXO7wP5GR1jE4dXrI9HEHGGX1GfI5R/B5Fx/FKb+L9UmfbA5J5RqjX+iJ2e7Q+O6aL4tDl3xs0dovNP03CxtFQjUAsGKA3rPyZ1c3vt39H3PIBDhXuxh3GrgA5dUAdB04t89/1O/w1cDnyilFU=');
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_exec($ch);
curl_close($ch);
    }
    public function sentapiline() {

    
   
// Get POST body content
$content = file_get_contents('php://input');
// Parse JSON
$events = json_decode($content, true);
// Validate parsed JSON data
if (!is_null($events['events'])) {
// Loop through each event
foreach ($events['events'] as $event) {
// Reply only when message sent is in 'text' format
$text = $event['source']['userId'];
// Get replyToken
$replyToken = $event['replyToken'];
if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
// Get text sent

// Build message to reply back

// Make a POST Request to Messaging API to reply to sender

$this->replytoken($replyToken,[
    'type' => 'text',
    'text' => " ขอบคุณสำหรับข้อมูล กรุณาเลือก BULLETIN ด้านล่าง หรือ คลิ๊ก! -> line://nv/location "
    ]);
                }else if($event['message']['type'] == 'location'){

                    $this->replytoken($replyToken,[
                        'type' => 'text',
                        'text' => $this->getWeather($event['message']['latitude'],$event['message']['longitude']).$this->getPollution($event['message']['latitude'],$event['message']['longitude'])
                        ]);
                }
            }
        }
    }
}
(new messageAQI())->sentapiline();
