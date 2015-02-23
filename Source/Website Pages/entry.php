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
				alert(iv);
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
				var hexVal = b64ToHex(storage);
				$('#hexVal').html('hex of storage ' + hexVal);
				//add start & stop signals
				hexVal = desHex + hexVal;
				hexVal = desLength + hexVal;
				hexVal = 'R' + hexVal;
				hexVal = hexVal + 'S';
				$('#signals').html('with start & stop & hexDesLength & desHex added ' + hexVal);
				
				alert('about to convert your data! WARNING: if you switch to another tab in your browser, this process will slow down drastically.');
				genDialTones(hexVal , 0);
				
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
								alert ("lower case, Upper Case, Numbers, and Special Characters");
								arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];
								alert (arr[40]);
							}
							else
							{	
								alert ("lower case, uppercase and numbers");
								arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0'];
							}
						}
						else if (spec)
						{
							alert("Lower Case, Upper Case and Special Characters");
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','!','@','?','$','*'];
						}
						else
						{
							alert("Lower Case and Upper Case");
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
						}
					}
					else if(num)
					{
						if(spec)
						{
							alert("LowerCase, Numbers and Special Characters");
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];
						}
						else
						{
							alert ("Lower Case and Numbers");
							arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0'];
						}
					}
					else if (spec)
					{
						alert ("Lower Case and Special Characters Only");
						arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','!','@','?','$','*'];
					}
					else
					{
						alert("Lower Case Only");
						arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
					}
				}
				else if (uc)
				{
					if (num)
					{
						if (spec)
						{
							alert("UpperCase, Numbers and Special Characters");
							arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];
						}
						else 
						{
							alert("Upper Case and Numbers Only");
							arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0'];
						}
					}
					else if (spec)
					{
						alert("Upper Case and Special Characters only");
						arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','!','@','?','$','*'];
					}
					else
					{
						alert("UpperCase only");
						arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
					}
				}
				else if (num)
				{
					if (spec)
					{
						alert ("Number and Special Characters")	
						arr = ['1','2','3','4','5','6','7','8','9','0','!','@','?','$','*'];			
					}
					else
					{
						alert ("Numbers Only")
						arr = ['1','2','3','4','5','6','7','8','9','0'];
					}
				}
				else if (spec)
				{
					alert ("Special Characters Only")
					arr = ['!','@','?','$','*'];
				}
				else
				{
					alert ("Please select some characters for the password")
				}

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
			<p id="signals" class="message"></p><br>
		</p>
		<p class = "text"> 
			Instructions for verifying Password go here
			<br/>
			<button class="button-style" onclick="verify();">Verify Password</button><br>
		</p>
		<br/>
		<p class = "text"> 
				Please select select the valid characters in the generated password<br>
				lower case: <input type="checkbox"  id="lc" /><br />
				UPPER CASE: <input type="checkbox" checked ="yes" id="uc" /><br /> 
				Numbers: <input type="checkbox" checked ="yes" id="num"  /><br /> 
				Special Characters: <input type="checkbox" id="spec"  /><br><br>
				Please select your password length.<br>
				<input type="radio" id="len" value = 8 />  : 8 <br />
				<input type="radio" id="len" value = 12/> : 12 <br />
				<input type="radio" id="len" value = 16/> : 16 <br />
				<input type="radio" id="len" value = 20/> : 20 <br />
				<input type="radio" id="len" value = 30/> : 12 <br />
				<button class="button-style" onclick="verify();">Verify</button><br>

				
			</form>
		</p>
				
	</body>
</html>

//			<form name="pwgen" action="verify(a,b,c,d)" method="post">
//			</form>