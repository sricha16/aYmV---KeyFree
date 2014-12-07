<!-- EncryptTrail.php
	 Used to test the encrypt and decrypt functions
	 Allows storage of the ciphertext to be retrieved by user -->
	 
<!DOCTYPE HTML>
<html>
	<head>
		<?php include 'style.php';?>
		<script src="crypto-js/aes.js"></script>
		<script src="crypto-js/sha256.js"></script>
		<script src="jquery-2.1.1.min.js"></script>
		
		<script>
			var storage = Object.create(null);
		
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
				var url = $('#eUrl').val();
				//add to storage
				storage[url] = CryptoJS.enc.Base64.stringify(iv) + ciphertext;
				//output inputs and ciphertext
				$('#stored').html('{' + pt + '} stored for ' + url +' as ' + ciphertext.toString());
			}
			
			function decrypt()
			{
				//get url
				var url = $('#dUrl').val();
				if(storage[url] != undefined)
				{
					//get ciphertext
					var ct = storage[url];
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
				else
				{
					$('#retrieved').html('No stored data for ' + url);
				}
			}
		</script>
		
	</head>
	<body>
	
		<input type="text" class="input-box" id="eKey" placeholder="Key"></input><br>
		<input type="text" class="input-box" id="eUrl" placeholder="URL"></input><br>
		<input type="text" class="input-box" id="username" placeholder="User Name"></input><br>
		<input type="text" class="input-box" id="password" placeholder="Password"></input><br>
		<button class="button-style" onclick="encrypt()">Store</button><br>
		<p class="message" id="stored"></p>
		
		<input type="text" class="input-box" id="dKey" placeholder="Key"></textarea><br>
		<input type="text" class="input-box" id="dUrl" placeholder="URL"></textarea><br>
		<button class="button-style" onclick="decrypt()">Retrieve</button><br>
		<p class="message" id="retrieved"></p>

	
	</body>
</html>