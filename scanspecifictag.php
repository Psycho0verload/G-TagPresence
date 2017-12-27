<?php
	/**
	 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
	 *
	 * MIT License
	 * 
	 * Copyright (c) 2017 Psycho0verload (Jonathan Starck). All rights reserved.
	 *	
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in all
	 * copies or substantial portions of the Software.
	 * 
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	 * SOFTWARE.
	 **/

	function sendCommand($item,$itemValue){
		$ch = curl_init("http://[IP from openHAB2]:8080/rest/items/".$item."/state");
		$curlOptions = array(
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HTTPHEADER => array('Content-Type: text/plain','Accept: application/json'),
			CURLOPT_CUSTOMREQUEST => 'PUT',
			CURLOPT_POSTFIELDS => NULL,
			CURLOPT_POSTFIELDS => $itemValue
		);
		curl_setopt_array($ch, $curlOptions);
		if(curl_exec($ch) === false){
			$this->updateDatabase(curl_error($ch));
			echo 'Curl-Fehler: ' . curl_error($ch);
			exit();
		}else{
			curl_exec($ch);
			return true;
		}
		curl_close($ch);
	}
	function scanSpecificTag($tagMac,$item) {
		exec("sudo sh /var/www/html/presence/script/scanspecifictag.sh $tagMac", $output);
		if ($output['0']==1){
			sendCommand($item,"ON");
		}else{
			sendCommand($item,"OFF");
		}
		exit();
	}
	
	foreach ($argv as $arg) {
		$e=explode("=",$arg);
		if(count($e)==2)
		$_GET[$e[0]]=$e[1];
		else
		$_GET[$e[0]]=0;
	}
	
	if(isset($_GET['tagMac']) && isset($_GET['item']) && isset($_GET['itemValue'])){
		error_log('$tagMac / $item / $itemValue können nicht gleichzeitig gesetzt werden', 0);
		exit();
	}
	
	if(
		isset($_GET['tagMac']) &&
		isset($_GET['item'])
	){
		scanSpecificTag($_GET['tagMac'],$_GET['item']);
	}elseif(
		isset($_GET['item']) &&
		isset($_GET['itemValue'])
	){
		sendCommand($_GET['item'],$_GET['itemValue']);
	}else {
		error_log('Parameter fehlen!', 0);
		exit();
	}
?>
