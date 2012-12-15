<?
	class curllib
	{
		static function get($url, $request = array())
		{
			return curllib::call('GET', $url, $request);
		}
		
		static function post($url, $request = array())
		{
			return curllib::call('POST', $url, $request);
		}
		
		static function call($method, $url, $request = array(), $standard_headers = true)
		{
			if($method == 'GET')
			{
				$query	=	http_build_query($request, null, '&');
				$url	=	$url . '?' . $query;
			}
			
			if($standard_headers)
			{
				$headers	=	array(
					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
					'Accept-Language: en-us,en;q=0.5',
					'Accept-Charset: utf-8;q=0.7,*;q=0.7'
				);
			}
			else
			{
				$headers	=	array();
			}
			
			//open connection
			$ch = curl_init();
			
			//set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if($method == 'POST')
			{
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
			}
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9'); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
			//execute post
			$response	=	curl_exec($ch);
			
			//close connection
			curl_close($ch);
			
			// easy debugging
			//echo 'cmd: '.$url . '   '. json_encode($request) ."\n";
			//echo $response . "\n\n\n";
			
			return $response;
		}
	}
?>