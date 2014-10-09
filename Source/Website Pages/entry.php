<!-- entry.php
     used to enter sensitive information and store it on the device
     encrypts the sensitive information and sends it to the device via the headphone jack -->
     
<!DOCTYPE HTML>
<html>
	<head>
		<?php include 'style.php';?>
		<script src="http://codebase.es/riffwave/riffwave.js"></script>
		<script src="crypto-js/aes.js"></script>
		<script src="crypto-js/sha256.js"></script>
		<script src="jquery-2.1.1.min.js"></script>
		
		<script>
			window.onload = init;
		
			function encrypt()
			{
				//get message
				var pt = 'Username: ' + $('#username').val() + ' | Password: ' + $('#password').val();
				//convert plaintext from Utf8 to binary array
				var msg = CryptoJS.enc.Utf8.parse(pt);				
				//get key
				var eKey = $('#eKey').val();
				//create hash of key
				var key = CryptoJS.SHA256(eKey);
				//create random IV
				var iv = CryptoJS.lib.WordArray.random(24);
				//encrypt the binary array message using hash of key in CBC mode with fixed IV
				var ciphertext = CryptoJS.AES.encrypt(msg, key, { mode: CryptoJS.mode.CBC, iv: iv });
				//get url
				var url = $('#url').val();
				//output inputs and ciphertext
				$('#stored').html('{' + pt + '} stored for ' + url +' as ' + CryptoJS.enc.Base64.stringify(iv) + ciphertext);
				
				var audio = dataToAudio(url, ciphertext, iv);
				dataFromAudio(audio);
			}			
			
			function dataToAudio(url, ciphertext, iv )
			{
				var data = []; // just an array
				data[0] = url;
				data[1] = ciphertext;
				data[2] = iv;
				var wave = new RIFFWAVE(data); // create the wave file
				var audio = new Audio(wave.dataURI); // create the HTML5 audio element
				return audio
					
			}
			
			function dataFromAudio(audio)
			{
				audio.play();																					
				$('#audioResult').html('meow');				
			}
			
			
			
			function init()
			{
				var context = new (window.AudioContext || window.webkitAudioContext)();
				var source;
				source = context.createBufferSource();
				var frameCount = context.sampleRate * 2.0;
				var myArrayBuffer = context.createBuffer(2, frameCount, context.sampleRate);
				
				request = new XMLHttpRequest();
				request.open('GET', 'SDR_0026.WAV', true);
				request.responseType = 'arraybuffer';
				request.onload = function() {
					var audioData = request.response;
					context.decodeAudioData(audioData, function(buffer) {
						source.buffer= buffer;
						source.connect(context.destination);
						},
					function(e){"error decoding" + e.err});
				}
				request.send();
				source.start(0);
				//alert('meow');
				//alert(source.getChannelData());
				//alert(source);
				//alert(myArrayBuffer.byteLength);
				var nowBuffering = myArrayBuffer.getChannelData(0);
				//alert(nowBuffering[0]);
			 
			}	
			
											
		</script>
		
	</head>
	<body>
	
		<input type="text" class="input-box" id="eKey" placeholder="Key"></input><br>
		<input type="text" class="input-box" id="url" placeholder="URL"></input><br>
		<input type="text" class="input-box" id="username" placeholder="User Name"></input><br>
		<input type="text" class="input-box" id="password" placeholder="Password"></input><br>
		<button class="button-style" onclick="encrypt()">Store</button><br>
		<p id="stored" class="message"></p><br>
		<p id="audioResult" class="message"></p><br>		
				
	</body>
</html>