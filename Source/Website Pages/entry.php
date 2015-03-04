<!-- entry.php
     used to enter sensitive information and store it on the device
     encrypts the sensitive information and sends it to the device via the headphone jack -->
<!-- audio listening code modified from http://typedarray.org/wp-content/projects/WebAudioRecorder/script.js -->
<!-- base64 to hex code modified from http://stackoverflow.com/questions/23190056/hex-to-base64-converter-for-javascript -->
<!-- oscillator code modified from https://sigusrone.com/articles/building-a-synth-with-the-web-audio-api-part-one -->
<!-- The code for the Password generator was modified from:  -->
     
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
				//alert(iv);
				//encrypt the binary array message using hash of key in CBC mode with fixed IV
				var ciphertext = CryptoJS.AES.encrypt(msg, key, { mode: CryptoJS.mode.CBC, iv: iv });
				//get description
				var description = $('#description').val();
				//output inputs and ciphertext
				//var storage = description.length + description + CryptoJS.enc.Base64.stringify(iv) + ciphertext;
				var storage = CryptoJS.enc.Base64.stringify(iv) + ciphertext;
				//description in hex
				var desHex = stringToHex(description.toString());
				//length of hex of description
				//var desLength = desHex.length.toString();
				
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
				/*var progress = $('#info').val();
				for(i=0; i<5; i++){
					progress += "  *  ";
					
				}*/
				$('#info').html('Converting your data! Stay in this tab!');
				//alert('about to convert your data! WARNING: if you switch to another tab in your browser, this process will slow down drastically.');
				
				genDialTones(hexVal2 , 0);
				
			}	
			
			function verifyPassword(){
			
				//send play signal and generate tones of description
				var description = $('#description').val();
				var desHex = stringToHex(description);
				desHex = description.length.toString() + desHex;
				desHex = 'P' + desHex;
				//alert('about to play desHex');
				genDialTones(desHex, 0);
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
			
			//success funciton should be moved to dtmfMethods.js because verifying password also requires listening and code is duplicated
			//test if moving breaks things when with Waldorf
			function success(e)
			{
				window.audioContext = new (window.AudioContext || window.webkitAudioContext)();
				var context = new AudioContext();
				var volume = context.createGain();
				audioInput = context.createMediaStreamSource(e);
				audioInput.connect(volume);
				var bufferSize = 512;
				var recorder = context.createScriptProcessor(bufferSize, 1, 1);
				
				/*
					*sample rate is the sample rate of the audio buffer being given to the dtmf object.
					*peakFilterSensitivity filters out "bad" energy peaks. Can be any number between 1 and infinity.
					*repeatMin requires that a DTMF character be repeated enough times across buffers to be considered a valid DTMF tone.
					*downsampleRate value decides how much the buffers are downsampled(by skipping every Nth sample). Default setting is 1.
					*threshold value gets passed to the goertzel object that gets created by the dtmf object. This is the noise threshold value. Default setting is 0.
				*/
				//DTMF(samplerate, peakFilterSensitivity, repeatMin, downsampleRate, threshold)
				var dtmf = new DTMF(context.sampleRate, 0, 6, 1, 0);  //does not sample well with only 44100Hz. context.sampleRate = 48000Hz
				dtmf.onDecode = function(value){
				    output += value;
				    $('#DTMFinput').html(output);
				    if(value == "S"){
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
				//strange problem where a '0' is consistently prepended as the first character.
				//the following two lines remove that zero.
				
				if(output.charAt(0) === '0')
				    output = output.substr(1);
				if(output.charAt(output.length-1) === 'S')
				    output = output.substr(0,output.length-1);
				output = output.replace(/#/g,'e');		//replacing all # with f and * with e HERE
				output= output.replace(/\*/g,'f');
				  b64 = btoa(String.fromCharCode.apply(null,
				    output.replace(/\r|\n/g, "").replace(/([\da-fA-F]{2}) ?/g, "0x$1 ").replace(/ +$/, "").split(" "))
				  );
				  hexVal = "R" + hexVal; //prepend an R onto what browser played for comparison
				  $('#output').html("what Waldorf played: " + output);
				  $('#hexval2').html("what browser played: " + hexVal);
				  if(output == hexVal) //if what we just heard from Waldorf matches what we previously sent to Waldorf, passwords match
					  $('#passMatch').html("passwords match!");
				  else
				  	$('#passMatch').html("passwords DO NOT match! Re-enter");
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
								//alert ("lower case, Upper Case, Numbers, and Special Characters");
								arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];
								//alert (arr[40]);
							}
							else
							{	
								//alert ("lower case, uppercase and numbers");
								arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0'];
								//alert (arr[4]);
							}
						}
						else if (spec)
						{
							//alert("Lower Case, Upper Case and Special Characters");
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','!','@','?','$','*'];
							//alert (arr[4]);
						}
						else
						{
							//alert("Lower Case and Upper Case");
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
							//alert (arr[4]);
						}
					}
					else if(num)
					{
						if(spec)
						{
							//alert("LowerCase, Numbers and Special Characters");
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];
							//alert (arr[4]);
						}
						else
						{
							//alert ("Lower Case and Numbers");
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0'];
							//alert (arr[4]);
						}
					}
					else if (spec)
					{
						//alert ("Lower Case and Special Characters Only");
						arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','!','@','?','$','*'];
						//alert (arr[4]);
					}
					else
					{
						//alert("Lower Case Only");
						arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
						//alert (arr[4]);
					}
				}
				else if (uc)
				{
					if (num)
					{
						if (spec)
						{
							//alert("UpperCase, Numbers and Special Characters");
							arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];
							//alert (arr[4]);
						}
						else 
						{
							//alert("Upper Case and Numbers Only");
							arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0'];
							//alert (arr[4]);
						}
					}
					else if (spec)
					{
						//alert("Upper Case and Special Characters only");
						arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','!','@','?','$','*'];
						//alert (arr[4]);
					}
					else
					{
						//alert("UpperCase only");
						arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
						//alert (arr[4]);
					}
				}
				else if (num)
				{
					if (spec)
					{
						//alert ("Number and Special Characters")	
						arr = ['1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];	
						//alert (arr[4]);		
					}
					else
					{
						//alert ("Numbers Only")
						arr = ['1','2','3','4','5','6','7','8','9','0'];
						//alert (arr[4]);
					}
				}
				else if (spec)
				{
					//alert ("Special Characters Only")
					arr = ['!','@','?','$','*'];
					//alert (arr[4]);
				}
				else
				{
					alert ("Please select some characters for the password")
				}
				var length;
				var box = ($('#pwlength').val());
				
				if  ((box.length <1)||(isNaN(box))||(box<0))
				{
					//alert ("else");
					alert ("Improper Length Format, length set to 8");
					length = 8;
					
				}
				else
				{
					//alert("if");
					length = $('#pwlength').val()
				}
								
				var randomArray= new Uint32Array(length);
				window.crypto.getRandomValues(randomArray);
				//alert("Before for loop");
				var generatedPassword="";
				for (var i = 0; i < randomArray.length; i++)
				{
					//alert ("in for loop");
					var temporary = randomArray[i] % arr.length;
					var tempChar = arr[temporary];
					//alert(tempChar);
					generatedPassword += tempChar;	
					//alert(generatedPassword);
				}
				//alert(generatedPassword);
				document.getElementById("password").value =generatedPassword;

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
			<input type="text" class="input-box" id="password" placeholder="Password"></input><br>
			<button class="button-style" onclick="encrypt();">Store</button><br>
			<p id="info" class="message"></p><br>
			<p id="stored" class="message"></p><br>
			<p id="hexVal" class="message"></p><br>
			<p id="signals" class="message"></p><br>
			<p class="message" id="playing"></p>
			<p id="DTMFinput" class="message"></p>
			<p id="output" class="message"></p>
			<p id="hexval2" class="message"></p>
			<p class="message" id="passMatch"></p>
		</p>
		<p class = "text"> 
			Instructions for verifying Password go here
			<br/>
			<button class="button-style" onclick="verifyPassword();">Verify Password</button><br>
		</p>
		<br/>
		<p class = "text"> 
				Please select select the valid characters in the generated password<br>
				lower case: <input type="checkbox"  id="lc" /><br />
				UPPER CASE: <input type="checkbox" checked ="yes" id="uc" /><br /> 
				Numbers: <input type="checkbox" checked ="yes" id="num"  /><br /> 
				Special Characters: <input type="checkbox" id="spec"  /><br><br>
				Please input your password length.<br>
				<input type="text" class="input-box" id="pwlength" placeholder="Length of Password"></input><br>
				<button class="button-style" onclick="verify();">Generate</button><br>

				
			</form>
		</p>
				
	</body>
</html>