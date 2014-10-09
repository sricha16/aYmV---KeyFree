<!-- retrieval.php
     portal to retrieve the information from the device-->

<!DOCTYPE HTML>
<html>
	<head>
		<?php include 'style.php';?>
		<script src="crypto-js/aes.js"></script>
		<script src="crypto-js/sha256.js"></script>
		<script src="jquery-2.1.1.min.js"></script>
		
		<script>
		
			function decrypt()
			{
				//get url
				var url = $('#dUrl').val();
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
		</script>
		
	</head>
	<body>
		<input type="text" class="input-box" id="dKey" placeholder="Key"></input><br>
		<input type="text" class="input-box" id="dUrl" placeholder="URL"></input><br>
		<input type="text" class="input-box" id="ct" placeholder="Ciphertext"></input><br>
		<button class="button-style" onclick="decrypt()">Retrieve</button><br>
		<p class="message" id="retrieved"></p>
	</body>
</html>