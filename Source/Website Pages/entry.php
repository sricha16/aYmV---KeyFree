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
			// variables
			var leftchannel = [];
			var rightchannel = [];
			var recorder = null;
			var recording = false;
			var recordingLength = 0;
			var volume = null;
			var audioInput = null;
			var sampleRate = 44100;
			var audioContext = null;
			var context = null;
			var outputElement = document.getElementById('output');
			var outputString;
			
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
				$('#stored').html('Information successfully stored for ' + description +' as ' + CryptoJS.enc.Base64.stringify(iv) + ciphertext);
				
				var audio = dataToAudio(description, ciphertext, iv);
				dataFromAudio(audio);
				
				
			}			
			
			function dataToAudio(description, ciphertext, iv )
			{
			
				var data = [],
					sampleRateHz = 44100,
					storedData = [description, ciphertext,iv],
				
					baseFreq = function(index){
						var r = 2*Math.PI * 440 * Math.pow(2,(storedData[index]-69)/12.0) / sampleRateHz;
						return r;
					};
				for(var j=0; j<2*sampleRateHz; j++){
					var l = 2*sampleRateHz / storedData.length;
					data[j] = 64 + 32 * Math.round(Math.sin(baseFreq(Math.round(j/l))*j));
				}
				alert('data length');
				alert(data.length);
				var wave = new RIFFWAVE();
				wave.header.sampleRate = sampleRateHz;
				wave.header.numChannels = 1;
				wave.Make(data);
				var audio = new Audio();
				audio.src = wave.dataURI;
				audio.textContent = storedData;
				return audio;
				/*var data = []; // just an array
				data[0] = description;
				data[1] = ciphertext;
				data[2] = iv;
				var wave = new RIFFWAVE(data); // create the wave file
				var audio = new Audio(wave.dataURI); // create the HTML5 audio element
				audio.textContent = wave.data;
				return audio
				*/
					
			}
			
			function dataFromAudio(audio)
			{
				startRecording();
				//alert('playing audio');
				audio.play();	
				//alert('audio over');			
				audio.onended = function(){buildWav()};
				//alert('returned from buildwav');
				//sleep(1000);
				//buildWav();															
				$('#audioResult').html('Pulling information back out of audio: ' + audio.textContent);				
				$('#hooray').html('format of above is description, encoded data without IV, hex representation of IV (which is base64)');
			}
			
			function startRecording()
			{				
				//alert('starting to record');
				recording = true;			
			        // reset the buffers for the new recording
			        leftchannel.length = rightchannel.length = 0;			        
			        recordingLength = 0;
			        //outputElement.innerHTML = 'Recording now...';
			}
			
			function buildWav()
			{
				// we stop recording
			        recording = false;
			        //alert('recording done');
			        
			        //outputElement.innerHTML = 'Building wav file...';
			
			        // we flat the left and right channels down
			        var leftBuffer = mergeBuffers ( leftchannel, recordingLength );
			        var rightBuffer = mergeBuffers ( rightchannel, recordingLength );
			        // we interleave both channels together
			        var interleaved = interleave ( leftBuffer, rightBuffer );
			        
			        // we create our wav file
			        var buffer = new ArrayBuffer(44 + interleaved.length * 2);
			        var view = new DataView(buffer);
			        
			        // RIFF chunk descriptor
			        writeUTFBytes(view, 0, 'RIFF');
			        view.setUint32(4, 44 + interleaved.length * 2, true);
			        writeUTFBytes(view, 8, 'WAVE');
			        // FMT sub-chunk
			        writeUTFBytes(view, 12, 'fmt ');
			        view.setUint32(16, 16, true);
			        view.setUint16(20, 1, true);
			        // stereo (2 channels)
			        view.setUint16(22, 2, true);
			        view.setUint32(24, sampleRate, true);
			        view.setUint32(28, sampleRate * 4, true);
			        view.setUint16(32, 4, true);
			        view.setUint16(34, 16, true);
			        // data sub-chunk
			        writeUTFBytes(view, 36, 'data');
			        view.setUint32(40, interleaved.length * 2, true);
			        
			        // write the PCM samples
			        var lng = interleaved.length;
			        var index = 44;
			        var volume = 1;
			        for (var i = 0; i < lng; i++){
			            view.setInt16(index, interleaved[i] * (0x7FFF * volume), true);
			            index += 2;
			        }
			        
			        // our final binary blob
			        var blob = new Blob ( [ view ], { type : 'audio/wav' } );
			        
			        // let us save it locally
			        //outputElement.innerHTML = 'Handing off the file now...';
			        var url = (window.URL || window.webkitURL).createObjectURL(blob);
			        var link = window.document.createElement('a');
			        link.href = url;
			        link.download = 'output.wav';
			        var click = document.createEvent("Event");
			        click.initEvent("click", true, true);
			        link.dispatchEvent(click);
			}
			
			function interleave(leftChannel, rightChannel)
			{
				var length = leftChannel.length + rightChannel.length;
				var result = new Float32Array(length);
				
				var inputIndex = 0;
				
				for (var index = 0; index < length; )
				{
					result[index++] = leftChannel[inputIndex];
				    	result[index++] = rightChannel[inputIndex];
				    	inputIndex++;
				}
				return result;
			}
				
			function mergeBuffers(channelBuffer, recordingLength)
			{
				var result = new Float32Array(recordingLength);
				var offset = 0;
				var lng = channelBuffer.length;
				for (var i = 0; i < lng; i++)
				{
					var buffer = channelBuffer[i];
				    	result.set(buffer, offset);
				    	offset += buffer.length;
				}
				return result;
			}
				
			function writeUTFBytes(view, offset, string)
			{ 
				var lng = string.length;
				for (var i = 0; i < lng; i++)
				{
					view.setUint8(offset + i, string.charCodeAt(i));
				}
			}
				
			function success(e)
			{
				// creates the audio context
				audioContext = window.AudioContext || window.webkitAudioContext;
				context = new audioContext();
				
				console.log('succcess');
				   
				// creates a gain node
				volume = context.createGain();
				
				// creates an audio node from the microphone incoming stream
				audioInput = context.createMediaStreamSource(e);
				
				// connect the stream to the gain node
				audioInput.connect(volume);
				
				/* From the spec: This value controls how frequently the audioprocess event is 
				dispatched and how many sample-frames need to be processed each call. 
				Lower values for buffer size will result in a lower (better) latency. 
				Higher values will be necessary to avoid audio breakup and glitches */
				var bufferSize = 2048;
				recorder = context.createScriptProcessor(bufferSize, 2, 2);
				//recorder = context.createJavaScriptNode(buffersize, 2, 2);
				
				recorder.onaudioprocess = function(e)
				{
				    //alert('loop forever!');
				    if (!recording) return;
				    var left = e.inputBuffer.getChannelData (0);
				    var right = e.inputBuffer.getChannelData (1);
				    // we clone the samples
				    leftchannel.push (new Float32Array (left));
				    rightchannel.push (new Float32Array (right));
				    recordingLength += bufferSize;
				    console.log('recording');				   
				    //alert('recorded something');
				}
				
				
				// we connect the recorder
				volume.connect (recorder);
				recorder.connect (context.destination); 
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
			<p id="audioResult" class="message"></p><br>		
			<p id="hooray" class="message"></p><br>
		</p>
		<p class = "text"> 
			Instructions for verifying Password go here
			<br/>
			<button class="button-style" onclick="verify();">Verify Password</button><br>
		</p>
		<br/>
				
	</body>
</html>