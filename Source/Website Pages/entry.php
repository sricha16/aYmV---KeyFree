<!-- entry.php
     used to enter sensitive information and store it on the device
     encrypts the sensitive information and sends it to the device via the headphone jack -->
<!--riffwave library from http://codebase.es/riffwave/riffwave.js" -->
<!-- audio listening code modified from http://typedarray.org/wp-content/projects/WebAudioRecorder/script.js -->
     
<!DOCTYPE HTML>
<html>
	<head>
		<?php include 'style.php';?>
		<script src="riffwave.js"></script>
		<script src="crypto-js/aes.js"></script>
		<script src="crypto-js/sha256.js"></script>
		<script src="jquery-2.1.1.min.js"></script>
		
		
		<script>
			
			// feature detection 
			if (!navigator.getUserMedia)
			    navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia ||
			                  navigator.mozGetUserMedia || navigator.msGetUserMedia;
			
			if (navigator.getUserMedia){
			    navigator.getUserMedia({audio:true}, success, function(e) {
			    alert('No mic input. Make sure your Key-Free device is plugged into the mic jack');
			    });
			} else alert('getUserMedia not supported in this browser.');
			
			function encrypt()
			{
				//get message
				var pt = 'Username: ' + $('#username').val() + ' | Password: ' + $('#password').val();
				//convert plaintext from Utf8 to binary array
				var msg = CryptoJS.enc.Utf8.parse(pt);				
				//get key
				var eKey = $('#key').val();
				//create hash of key
				var key = CryptoJS.SHA256(eKey);
				//create random IV
				var iv = CryptoJS.lib.WordArray.random(24);
				//encrypt the binary array message using hash of key in CBC mode with fixed IV
				var ciphertext = CryptoJS.AES.encrypt(msg, key, { mode: CryptoJS.mode.CBC, iv: iv });
				//get description
				var description = $('#description').val();
				//output inputs and ciphertext
				var storage = CryptoJS.enc.Base64.stringify(iv) + ciphertext;
				$('#stored').html('Information successfully stored for ' + description +' as ' + storage);
				
				var hexVal = asciiToHex(storage);
				$('#hexVal').html('hex of storage ' + hexVal);
				var cleanedHexVal = hexCleanup(hexVal);
				$('#cleanedHexVal ').html('cleanedHexVal of storage ' + cleanedHexVal );
				
				genDialTones(cleanedHexVal, 0);
				
			}			
			
			function asciiToHex(storage)
			{
				var hex = "";
				for (a = 0; a < storage.length; a++) {
					//gets the unicode value of the character & converts to hex
				    hex+=(storage.charCodeAt(a).toString(16));
				}
				return hex;
			}
			
			function hexCleanup (hexVal)
			{
				hexVal = hexVal.replace(/e/g,'#');
				hexVal = hexVal.replace(/f/g,'*');
				return hexVal ;
			}
			
			function genDialTones(cleanedHexVal, num)
			{
				var audio = new Audio();
				var filename = cleanedHexVal.charAt(num);
				if(filename == "#")
					filename = "pound";
				else if (filename == "*")
					filename = "star";	
				audio.src = filename + ".mp3";
				audio.play();
				audio.onended = function(){playNext(cleanedHexVal, num)};
				
			}
			function playNext(cleanedHexVal, num)
			{
				if(num+1 < cleanedHexVal.length)
					genDialTones(cleanedHexVal, num+1);
			}
			
			function success(e)
			{
			}
											
		</script>
	</head>
	<body>
		<p class = "text"> 
			Instructions go in this box
		</p>
		<br/>
		<p class = "text"> 
			<input type="password" class="input-box" id="key" placeholder="Key"></input><br>
			<input type="text" class="input-box" id="description" placeholder="Description"></input><br>
			<input type="password" class="input-box" id="username" placeholder="User Name"></input><br>
			<input type="password" class="input-box" id="password" placeholder="Password"></input><br>
			<button class="button-style" onclick="encrypt();">Store</button><br>
			<p id="stored" class="message"></p><br>
			<p id="hexVal" class="message"></p><br>
			<p id="cleanedHexVal" class="message"></p><br>
		</p>
		<p class = "text"> 
			Instructions for verifying Password go here
			<br/>
			<button class="button-style" onclick="verify();">Verify Password</button><br>
		</p>
		<br/>
				
	</body>
</html>