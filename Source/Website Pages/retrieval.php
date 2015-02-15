<!-- retrieval.php
     portal to retrieve the information from the device-->
<!-- audio listening code modified from http://typedarray.org/wp-content/projects/WebAudioRecorder/script.js -->
<!-- hex to base64 conversion modified from http://stackoverflow.com/questions/23190056/hex-to-base64-converter-for-javascript -->
<!-- the goertzel algorithm implementation by Ben Titcomb @Ravenstine modified from https://github.com/Ravenstine/goertzeljs -->

<!DOCTYPE HTML>
<html>
	<head>
		<?php include 'style.php';?>
		<script src="crypto-js/aes.js"></script>
		<script src="crypto-js/sha256.js"></script>
		<script src="jquery-2.1.1.min.js"></script>
		<script src="dtmf.js"></script>
		<script src="goertzel.js"></script>
		<script>		

			// variables
			var output = 0;
			var b64;
			
			// feature detection 
			if (!navigator.getUserMedia)
			    navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia ||
			                  navigator.mozGetUserMedia || navigator.msGetUserMedia;
			
			if (navigator.getUserMedia){
			    navigator.getUserMedia({audio:true}, success, function(e) {
			    alert('No mic input. Make sure your Key-Free device is plugged into the mic jack');
			    });
			} else alert('getUserMedia not supported in this browser.');
		
			function decrypt()
			{
				//get url
				var description = $('#description').val();
				//get ciphertext
				var ct = $('#ct').val();
				//get key
				var dKey = $('#dKey').val();
				//create hash of password
				var key = CryptoJS.SHA256(dKey);
				//get IV from ct and convert to binary array
				var iv = CryptoJS.enc.Base64.parse(ct.substring(0, 32));
				//get ciphertext from ct
				var ciphertext = ct.substring(32);
				//decrypt ciphertext using hash of key in CBC mode with random IV
				var message = CryptoJS.AES.decrypt(ciphertext, key, { mode: CryptoJS.mode.CBC, iv: iv });
				//convert message into Utf8 from binary array
				var msg = CryptoJS.enc.Utf8.stringify(message);
				//output decrypted values
				//$('#retrieved').html(' IV: ' + tempIV);
				$('#retrieved').html(msg);
				
			}
			
			function decryptTest()
			{
				//var description = $('#description').val();
				var ct = b64;
				//to get this test to work, make sure you've entered meow as the key on the entry page
				var dKey = "meow";
				var key = CryptoJS.SHA256(dKey);
				var iv = CryptoJS.enc.Base64.parse(ct.substring(0, 32));
				var ciphertext = ct.substring(32);
				var message = CryptoJS.AES.decrypt(ciphertext, key, { mode: CryptoJS.mode.CBC, iv: iv });
				var msg = CryptoJS.enc.Utf8.stringify(message);
				$('#decrypted').html("decrypted data: " + msg );
			}
			
			function hexToB64()
			{
				//strange problem where a '0' is consistently prepended as the first character.
				//the following two lines remove that zero.
				if(output.charAt(0) === '0')
				    output = output.substr(1);
				output = output.replace(/#/g,'e');
				output= output.replace(/\*/g,'f');
				$('#cleanedHex').html("cleaned hex: " + output);
				  b64 = btoa(String.fromCharCode.apply(null,
				    output.replace(/\r|\n/g, "").replace(/([\da-fA-F]{2}) ?/g, "0x$1 ").replace(/ +$/, "").split(" "))
				  );
				  $('#b64').html("base64: " + b64);
				  
				  decryptTest();
			}

			function success(e)
			{
				window.audioContext = new (window.AudioContext || window.webkitAudioContext)();
				var context = new AudioContext();
				var volume = context.createGain();
				audioInput = context.createMediaStreamSource(e);
				audioInput.connect(volume);
				var bufferSize = 512;
				var recorder = context.createScriptProcessor(bufferSize, 1, 1);
				var dtmf = new DTMF(context.sampleRate,1.4,6,1,0.0002);
				//.005
				dtmf.onDecode = function(value){
				    output += value;
				    $('#DTMFinput').html(output);
				}
				recorder.onaudioprocess = function(e){
				  var buffer = e.inputBuffer.getChannelData(0);
				  dtmf.processBuffer(buffer);
				}
				volume.connect (recorder);
				recorder.connect (context.destination) ;
			}
			
		</script>
		
	</head>
	<body>
		<p class = "text"> 
			Instructions go in this box
		</p>
		<br/>
		<p class = "text"> 
			<input type="password" class="input-box" id="dKey" placeholder="Key"></input><br>
			<input type="text" class="input-box" id="description" placeholder="Description"></input><br>
			<input type="text" class="input-box" id="ct" placeholder="Ciphertext"></input><br>
			<button class="button-style" onclick="decrypt()">Retrieve</button><br>
			<button class="button-style" onclick="hexToB64()">to base64</button><br>
			<p class="message" id="retrieved"></p>
			<p class="message" id="DTMFinput"></p>
			<p class="message" id="cleanedHex"></p>
			<p class="message" id="b64"></p>
			<p class="message" id="decrypted"></p>
			
		</p>
	</body>
</html>