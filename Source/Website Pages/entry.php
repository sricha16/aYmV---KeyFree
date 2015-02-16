<!-- entry.php
     used to enter sensitive information and store it on the device
     encrypts the sensitive information and sends it to the device via the headphone jack -->
<!-- audio listening code modified from http://typedarray.org/wp-content/projects/WebAudioRecorder/script.js -->
<!-- base64 to hex code modified from http://stackoverflow.com/questions/23190056/hex-to-base64-converter-for-javascript -->
<!-- oscillator code modified from https://sigusrone.com/articles/building-a-synth-with-the-web-audio-api-part-one -->
     
<!DOCTYPE HTML>
<html>
	<head>
		<?php include 'style.php';?>
		<script src="crypto-js/aes.js"></script>
		<script src="crypto-js/sha256.js"></script>
		<script src="jquery-2.1.1.min.js"></script>
		
		<script>
			//variables
			window.audioContext = new (window.AudioContext || window.webkitAudioContext)();
			
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
				
				var hexVal = b64ToHex(storage);
				$('#hexVal').html('hex of storage ' + hexVal);
				
				alert('about to convert your data! WARNING: if you switch to another tab in your browser, this process will slow down drastically.');
				genDialTones(hexVal , 0);
				
			}			
			
			function b64ToHex(storage)
			{
				for (var i = 0, bin = atob(storage.replace(/[ \r\n]+$/, "")), hex = ""; i < bin.length; ++i) 
				{
					    var tmp = bin.charCodeAt(i).toString(16);
					    if (tmp.length === 1) tmp = "0" + tmp;
					    hex += tmp;
				}
				return hex;
			}
			
			function genDialTones(hexVal, num)
			{	
				var high=0;
				var low=0;
				var hexChar = hexVal.charAt(num);

				switch(hexChar) 
				{
				    case '0':
				        low = 941;
				        high = 1336;
				        break;
				    case '1':
				        low = 697;
				        high = 1209;
				        break;
				    case '2':
				        low = 697;
				        high = 1336;
				        break;
				    case '3':
				        low = 697;
				        high = 1477;
				        break;
				    case '4':
				        low = 770;
				        high = 1209;
				        break;
				    case '5':
				        low = 770;
				        high = 1336;
				        break;
				    case '6':
				        low = 770;
				        high = 1477;
				        break;
				    case '7':
				        low = 852;
				        high = 1209;
				        break;
				    case '8':
				        low = 852;
				        high = 1336;
				        break;
				    case '9':
				        low = 852;
				        high = 1477;
				        break;
				    case 'e':
				        low = 941;
				        high = 1477;
				        break;
				    case 'f':
				        low = 941;
				        high = 1209;
				        break;
				    case 'a':
				        low = 697;
				        high = 1633;
				        break;
				    case 'b':
				        low = 770;
				        high = 1633;
				        break;
				    case 'c':
				        low = 852;
				        high = 1633;
				        break;
				    case 'd':
				        low = 941;
				        high = 1633;
				        break;
				    default:
				    	alert('breaking!');
				        break;
				}
				playTone(low, high, num, hexVal);
			}
			
			function playTone(low, high, num, hexVal)
			{
				var oscillator = window.audioContext.createOscillator();
				var osc= window.audioContext.createOscillator();
				
				oscillator.frequency.value = low;
				oscillator.connect(window.audioContext.destination);
				//firefox doesn't support .noteOn and .noteOff, but can handle .start and .stop
				oscillator.start(window.audioContext.currentTime);
				oscillator.stop(window.audioContext.currentTime + .11);			
				
				osc.frequency.value = high;
				osc.connect(window.audioContext.destination);
				osc.start(window.audioContext.currentTime);
				osc.stop(window.audioContext.currentTime + .11);  //.25 s = 250 ms
				
				if(num+1 < hexVal.length)
					setTimeout(function(){ genDialTones(hexVal, num+1); }, 150); //250 ms
				else
					alert('Transfer has completed successfully! :) ');
			
			}	
			function verify ()//low, up, num, spec, len)	
			{
				alert("Pressed Verify");
				var test= $('#pw').val();
				alert(test);
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
		</p>
		<p class = "text"> 
			Instructions for verifying Password go here
			<br/>
			<button class="button-style" onclick="verify();">Verify Password</button><br>
		</p>
		<br/>
		<p class = "text"> 
				Please select select the valid characters in the generated password<br>
				lower case: <input type="checkbox" checked ="yes" id="pw" value="a"  /><br />
				UPPER CASE: <input type="checkbox" checked ="yes" id="pw1" value="b"  /><br /> 
				Numbers: <input type="checkbox" checked ="yes" id="pw2" value="c"  /><br /> 
				Special Characters: <input type="checkbox" id="pw3" value="d"  /><br><br>
				Please select your password length.<br>
				<input type="radio" name="len" /> : 8 <br />
				<input type="radio" name="len" /> : 12 <br />
				<input type="radio" name="len" /> : 16 <br />
				<input type="radio" name="len" /> : 20 <br />
				<input type="radio" name="len" /> : 12 <br />
				<button class="button-style" onclick="verify();">Verify</button><br>

				
			</form>
		</p>
				
	</body>
</html>

//			<form name="pwgen" action="verify(a,b,c,d)" method="post">
//			</form>