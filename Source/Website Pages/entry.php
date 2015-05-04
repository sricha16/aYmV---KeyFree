<!-- entry.php
     used to enter sensitive information and store it on the device
     encrypts the sensitive information and sends it to the device via the headphone jack -->
<!-- audio listening code modified from http://typedarray.org/wp-content/projects/WebAudioRecorder/script.js -->
<!-- base64 to hex code modified from http://stackoverflow.com/questions/23190056/hex-to-base64-converter-for-javascript -->
<!-- oscillator code modified from https://sigusrone.com/articles/building-a-synth-with-the-web-audio-api-part-one -->
<!-- The code for the Password generator was modified from: https://developer.mozilla.com/en-US/docs/web/api/window/crypto  -->
     
<!DOCTYPE HTML>
<html>
	<head>
		<?php include 'style.php';?>
		<script src="crypto-js/aes.js"></script>
		<script src="crypto-js/sha256.js"></script>
		<script src="jquery-2.1.1.min.js"></script>
		<script src="dtmfMethods.js"></script>
		<script src="dtmf.js"></script>
		<script src="goertzel.js"></script>
		
		<script>
			
			//variables
			var output="";
			var hexVal;
			window.audioContext = new (window.AudioContext || window.webkitAudioContext)();
			
			window.onload = function() { document.getElementById("key").focus(); };
			window.onload = function() {verify()};
			
			function encrypt()
			{
				//get message
				<!--var pt = 'Username: ' + $('#username').val() + ' | Password: ' + $('#password').val();-->
				var pt = $('#username').val() + '|' + $('#password').val();
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
				if((key=="")||(description==""))
				{
					alert("The 'Key' and 'Description' fields are required! ");
					return;
				}
				var storage = CryptoJS.enc.Base64.stringify(iv) + ciphertext;
				//description in hex
				var desHex = stringToHex(description.toString());
				// length of the description
				var desLength = description.length.toString();
				
				
				//final storage is desctiption.length + desctiption + iv + ciphertext
				$('#stored').html('Information successfully stored for ' + description +' as ' + storage);
				//var hexVal = b64ToHex(storage);
				hexVal = b64ToHex(storage);
				$('#hexVal').html('hex of storage ' + hexVal);
				//add start & stop signals
				var hexVal2 = hexVal;
				hexVal2 = desHex + hexVal2 ;
				hexVal2 = desLength + hexVal2 ;
				hexVal2 = 'R' + hexVal2 ;
				hexVal2 = hexVal2 + 'S';
				$('#signals').html('with start & stop & hexDesLength & desHex added ' + hexVal2 );
				$('#info').html('Converting your data! Stay in this tab!');
				
				genDialTones(hexVal2, 0, 0); 
				
			}	
			
			function verifyPassword(){
				//send play signal and generate tones of description
				var description = $('#description').val();
				var desHex = stringToHex(description);
				desHex = description.length.toString() + desHex;
				desHex = 'P' + desHex;
				genDialTones(desHex, 0, 1);
				$('#playing').html("telling Waldorf to play: " + desHex);
				
				if (!navigator.getUserMedia)
				    navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia ||
				                  navigator.mozGetUserMedia || navigator.msGetUserMedia;
				
				if (navigator.getUserMedia){
				    navigator.getUserMedia({audio:true}, success, function(e) {
				    alert('No mic input. Make sure your Key-Free device is plugged into the mic jack');
				    });
				} else alert('getUserMedia not supported in this browser.');
			}
			function success(e)
			{
				$('#info').html("Password being verified!");
				window.audioContext = new (window.AudioContext || window.webkitAudioContext)();
				var context = new AudioContext();
				var volume = context.createGain();
				audioInput = context.createMediaStreamSource(e);
				audioInput.connect(volume);
				var bufferSize = 512;
				var recorder = context.createScriptProcessor(bufferSize, 1, 1);
				var started = false;
				
				/*
					*sample rate is the sample rate of the audio buffer being given to the dtmf object.
					*peakFilterSensitivity filters out "bad" energy peaks. Can be any number between 1 and infinity.
					*repeatMin requires that a DTMF character be repeated enough times across buffers to be considered a valid DTMF tone.
					*downsampleRate value decides how much the buffers are downsampled(by skipping every Nth sample). Default setting is 1.
					*threshold value gets passed to the goertzel object that gets created by the dtmf object. This is the noise threshold value. Default setting is 0.
				*/
				var dtmf = new DTMF(context.sampleRate, 0, 6, 1, 0);
				dtmf.onDecode = function(value){
				
				
				if(started){
				    output += value;
    				    $('#DTMFinput').html(output);
				}
				if(value == "N"){
				    started = false;
				    $('#info').html("No File under that name. Please resubmit information.");
				}
			        if(value == "R")
				  started = true;
				if(value == "S"){
				    started = false;
				    compareHex();
				}
				    
				}
				recorder.onaudioprocess = function(e){
				  var buffer = e.inputBuffer.getChannelData(0);
				  dtmf.processBuffer(buffer);
				}
				volume.connect (recorder);
				recorder.connect (context.destination) ;
			}
			
			function compareHex()
			{
				if(output.charAt(0) === '0')
				    output = output.substr(1);
				if(output.charAt(output.length-1) === 'S')
				    output = output.substr(0,output.length-1);
				output = output.replace(/#/g,'e');		//replacing all # with f and * with e HERE
				output= output.replace(/\*/g,'f');
				  b64 = btoa(String.fromCharCode.apply(null,
				    output.replace(/\r|\n/g, "").replace(/([\da-fA-F]{2}) ?/g, "0x$1 ").replace(/ +$/, "").split(" "))
				  );
				  output = output.toUpperCase();
				  hexVal = hexVal.toUpperCase();
				  $('#output').html("what Waldorf played: " + output);
				  $('#hexval2').html("what browser played: " + hexVal);
				  if(output == hexVal) //if what we just heard from Waldorf matches what we previously sent to Waldorf, passwords match
					  $('#info').html("Passwords match!");
				  else
				  	$('#info').html("Passwords DO NOT match! Please re-submit.");
			}
			
			function verify ()//low, up, num, spec, len)	
			{
				var lc= $('#lc').prop('checked');
				var uc= $('#uc').prop('checked');
				var num= $('#num').prop('checked');
				var spec= $('#spec').prop('checked');
				var len = $('#len').prop('');
				//alert(lc + uc + num + spec);
				var arr;
				if (lc)
				{
					if (uc)
					{
						if(num)
						{
							if (spec)
							{
								arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];
							}
							else
							{	
								arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0'];
							}
						}
						else if (spec)
						{
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','!','@','?','$','*'];
						}
						else
						{
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
						}
					}
					else if(num)
					{
						if(spec)
						{
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];
						}
						else
						{
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0'];
						}
					}
					else if (spec)
					{
						arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','!','@','?','$','*'];
					}
					else
					{
						arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
					}
				}
				else if (uc)
				{
					if (num)
					{
						if (spec)
						{
							arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];
						}
						else 
						{
							arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0'];
						}
					}
					else if (spec)
					{
						arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','!','@','?','$','*'];
					}
					else
					{
						arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
					}
				}
				else if (num)
				{
					if (spec)
					{
						arr = ['1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];		
					}
					else
					{
						arr = ['1','2','3','4','5','6','7','8','9','0'];
					}
				}
				else if (spec)
				{
					arr = ['!','@','?','$','*'];
				}
				else
				{
					alert ("Please select some characters for the password")
				}
				var length;
				var box = ($('#pwlength').val());
				
				if  ((box.length <1)||(isNaN(box))||(box<0))
				{
					alert ("Improper Length Format, length set to 10");
					length = 10;
					
				}
				else
				{
					length = $('#pwlength').val()
				}
								
				var randomArray= new Uint32Array(length);
				window.crypto.getRandomValues(randomArray);
				var generatedPassword="";
				for (var i = 0; i < randomArray.length; i++)
				{
					var temporary = randomArray[i] % arr.length;
					var tempChar = arr[temporary];
					generatedPassword += tempChar;	
				}
				document.getElementById("password").value =generatedPassword;

			}	
						
		</script>
	</head>
	<body>
		<p class = "text"> 
			Please enter the necessary information on the left. It is best practice to generate a random string for your password, which can be done on the right. Your description must be less than 10 characters.
		</p>
		<br/>
		<table class="text" width="75%">
			<tr>
				<td width="35%">
					<input type="password" class="input-box" id="key" placeholder="Key"></input><br>
					<input type="text" class="input-box" id="description" placeholder="Description" maxLength="9"></input><br>
					<input type="password" class="input-box" id="username" placeholder="User Name"></input><br>
					<input type="password" class="input-box" id="password" placeholder="Password"></input><br>
					<button class="button-style" onclick="encrypt();">Store</button><br>
					<button class="button-style" onclick="location.reload();">Store Another Password</button><br>
				</td>
				<td width="35%">
					Please select select the valid <br>
					characters in the generated password<br>
					lower case: <input type="checkbox"  checked="yes" id="lc" /><br />
					UPPER CASE: <input type="checkbox" checked ="yes" id="uc" /><br /> 
					Numbers: <input type="checkbox" checked ="yes" id="num"  /><br /> 
					Special Characters: <input type="checkbox" checked = "yes" id="spec"  /><br><br>
					Please input your password length.<br>
					<input type="text" class="input-box" id="pwlength" placeholder="Length of Password" value = 10></input><br>
					<button class="button-style" onclick="verify();">Generate</button><br>
					Not on Chrome? Click below to go to a website to generate a random password.<br>
					<button class="button-style" onclick="window.open('http://strongpasswordgenerator.com/','_blank');">Random Password</button>
				</td>
			</tr>
		</table>
			<p></p>
			<p id="info" class="message"></p><br>
		<!-- Comment out all the info boxes for cleanliness
			<p id="stored" class="message"></p><br>
			<p id="hexVal" class="message"></p><br>
			<p id="signals" class="message"></p><br>
			<p class="message" id="playing"></p>
			<p id="DTMFinput" class="message"></p>
			<p id="output" class="message"></p>
			<p id="hexval2" class="message"></p>
			<p class="message" id="passMatch"></p>
		-->
		<p class = "text"> 
			Please verify that your infomration was stored correctly by clicking on the button below.
			<br/>
			<button class="button-style" onclick="verifyPassword();">Verify Password</button><br>
		</p>
		<br/>
		
				
	</body>
</html>