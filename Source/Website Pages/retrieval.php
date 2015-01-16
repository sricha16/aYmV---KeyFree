<!-- retrieval.php
     portal to retrieve the information from the device-->
<!-- audio listening code modified from http://typedarray.org/wp-content/projects/WebAudioRecorder/script.js -->

<!DOCTYPE HTML>
<html>
	<head>
		<?php include 'style.php';?>
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
			
			//this is never being called
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
		<input type="password" class="input-box" id="dKey" placeholder="Key"></input><br>
		<input type="text" class="input-box" id="description" placeholder="Description"></input><br>
		<input type="text" class="input-box" id="ct" placeholder="Ciphertext"></input><br>
		<button class="button-style" onclick="decrypt()">Retrieve</button><br>
		<p class="message" id="retrieved"></p>
	</body>
</html>