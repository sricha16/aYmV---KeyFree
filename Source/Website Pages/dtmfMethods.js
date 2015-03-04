			function stringToHex(str){
				    var hex = '';
				    for(var i=0;i<str.length;i++) {
					    hex += ''+str.charCodeAt(i).toString(16);
				    }
				    return hex;
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
				    case 'f':
				        low = 941;
				        high = 1477;
				        break;
				    case 'e':
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
				    case 'R':
				    	low = 1993; //record
				    	high = 1209;
				    	break;
				    case 'S':
				    	low = 1993; //stop
				    	high = 1477;
				    	break;
				    case 'P':
				    	low = 1993; //stop
				    	high = 1336;
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
				//firefox does not support .noteOn and .noteOff, but can handle .start and .stop
				oscillator.start(window.audioContext.currentTime);
				oscillator.stop(window.audioContext.currentTime + .11);	             //THIS IS THE DURATION OF THE NOTE		

				osc.frequency.value = high;
				osc.connect(window.audioContext.destination);
				osc.start(window.audioContext.currentTime);
				osc.stop(window.audioContext.currentTime + .11);  //.25 s = 250 ms   THIS IS THE DURATION OF THE NOTE

				if(num+1 < hexVal.length)
					setTimeout(function(){ genDialTones(hexVal, num+1); }, 150); //250 ms   THIS IS HOW LONG IT WAITS TO PLAY THE NEXT NOTE
				else
					//;//alert('Transfer has completed successfully! :) ');
					$('#info').html('Done!');
			
			}	