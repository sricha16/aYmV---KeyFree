// Dial Tone (DTMF) encoding decoding example.
//
// The signals are read in through the mic jack and played
// back out through the headphone jack
//
// Use the Arduino Serial Monitor to watch for incoming
// dial tones, and to send digits to be played as dial tones.
//
// This example code is in the public domain.


#include <Audio.h>
#include <Wire.h>
#include <SPI.h>
#include <SD.h>

// Create the Audio components.  These should be created in the
// order data flows, inputs/sources -> processing -> outputs
//
AudioInputI2S            audioIn;
AudioAnalyzeToneDetect   row1;     // 7 tone detectors are needed
AudioAnalyzeToneDetect   row2;     // to receive DTMF dial tones
AudioAnalyzeToneDetect   row3;
AudioAnalyzeToneDetect   row4;
AudioAnalyzeToneDetect   column1;
AudioAnalyzeToneDetect   column2;
AudioAnalyzeToneDetect   column3;
AudioAnalyzeToneDetect   column4;
AudioAnalyzeToneDetect   rec;      // Signal to start recording
AudioAnalyzeToneDetect   ply;      // Signal to start playing
AudioAnalyzeToneDetect   stp;      // Signal to stop playing or recording
AudioSynthWaveformSine   sine1;    // 2 sine waves
AudioSynthWaveformSine   sine2;    // to create DTMF
AudioMixer4              mixer;
AudioOutputI2S           audioOut;

// Create Audio connections between the components
// For Dial Tone
AudioConnection patchCord01(audioIn, 0, row1, 0);
AudioConnection patchCord02(audioIn, 0, row2, 0);
AudioConnection patchCord03(audioIn, 0, row3, 0);
AudioConnection patchCord04(audioIn, 0, row4, 0);
AudioConnection patchCord05(audioIn, 0, column1, 0);
AudioConnection patchCord06(audioIn, 0, column2, 0);
AudioConnection patchCord07(audioIn, 0, column3, 0);
AudioConnection patchCord08(audioIn, 0, column4, 0);
// For Start/Stop Signals
AudioConnection patchCord09(audioIn, 0, rec, 0);
AudioConnection patchCord10(audioIn, 0, ply, 0);
AudioConnection patchCord11(audioIn, 0, stp, 0);
// For Output
AudioConnection patchCord12(sine1, 0, mixer, 0);
AudioConnection patchCord13(sine2, 0, mixer, 1);
AudioConnection patchCord14(mixer, 0, audioOut, 0);
AudioConnection patchCord15(mixer, 0, audioOut, 1);

// Create an object to control the audio shield.
AudioControlSGTL5000 audioShield;

// The input on the audio shield to use
const int myInput = AUDIO_INPUT_MIC;

// Remember which mode we're doing
int mode = 0;  // 0=stopped, 1=recording, 2=playing

// The file where data is stored
File frec;

// The file to save the data under the new name
File fnew;

// Unique filename to store data under
char *filename;

// On board LED
int led = 13;

void setup() {
  // Audio connections require memory to work.  For more
  // detailed information, see the MemoryAndCpuUsage example
  AudioMemory(60);

  // Enable the audio shield and set the output volume.
  audioShield.enable();
  audioShield.inputSelect(myInput);
  audioShield.volume(0.5);
  audioShield.micGain(0);

  while (!Serial) ;
  delay(100);

  // Configure the tone detectors with the frequency and number
  // of cycles to match.  Have two different option. Choose
  // which ever you prefer.
//  row1.frequency(697, 21);  // 30.1291 ms
//  row2.frequency(770, 23);  // 29.8701 ms
//  row3.frequency(852, 25);  // 29.3427 ms
//  row4.frequency(941, 28);  // 29.7556 ms
//  column1.frequency(1209, 36);  // 29.7767 ms
//  column2.frequency(1336, 40);  // 29.9401 ms
//  column3.frequency(1477, 44);  // 29.7901 ms
//  column4.frequency(1633, 48);  // 29.3938 ms
//  rec.frequency(1951, 59);  // 30.2409 ms 1951
//  ply.frequency(2097, 63);  // 30.0429 ms 2097
//  stp.frequency(2229, 67);  // 30.0583 ms 2229
  
  row1.frequency(697, 7);  // 10.043 ms
  row2.frequency(770, 8);  // 10.390 ms
  row3.frequency(852, 9);  // 10.563 ms
  row4.frequency(941, 9);  // 09.564 ms
  column1.frequency(1209, 12);  // 09.926 ms
  column2.frequency(1336, 13);  // 09.731 ms
  column3.frequency(1477, 15);  // 10.156 ms
  column4.frequency(1633, 16);  // 09.798 ms
  rec.frequency(1951, 20);  // 10.251 ms
  ply.frequency(2097, 20);  // 09.537 ms
  stp.frequency(2229, 22);  // 09.870 ms

  // Initialize the SD card
  SPI.setMOSI(7);
  SPI.setSCK(14);
  if (!(SD.begin(10))) {
    // stop here if no SD card, but print a message
    while (1) {
      Serial.println("Unable to access the SD card");
      delay(500);
    }
  }
  
// pinMode method breaks read() function on tones... have no idea why...
// actually it might be because the mic port also uses pin 13 so they fight over it
  // Initialize LED
//  pinMode(led, OUTPUT);
//  digitalWrite(led, LOW);
}

const float tone_threshold = 0.1;

void loop() {
  float r, p, s;
  
  // read for start-stop and playData signals
  r = rec.read();
  p = ply.read();
  s = stp.read();
  
//  Serial.print(r);
//  Serial.print(", ");
//  Serial.print(p);
//  Serial.print(", ");
//  Serial.println(s); 
  
  // compare to threshold
  if( r >= tone_threshold ) {
    // start recording if in wait mode
    if( mode == 0 ) startRecording();
  }
  if( p >= tone_threshold ){
    // start playing if in wait mode
    if( mode == 0 ) startPlaying();
  }
  if( s >= tone_threshold ){
    // stop recording if recording
    if( mode == 1 ) stopRecording();
    // stop playing if playing
    if( mode == 2 ) stopPlaying();
  }  

  // If we're playing or recording, carry on...
  if (mode == 1) {
    continueRecording();
  }
  if (mode == 2) {
    continuePlaying();
  }

  delay(25);
}

// Initial steps required to record
void startRecording() {
  // Signify the start of recording via serial output and turning LED on
  Serial.println("startRecording");
  //digitalWrite(led, HIGH);
  
  // Check if file already exists, delete it if it does
  if (SD.exists("test.RAW")) {
    SD.remove("test.RAW");
  }
  
  // Open the file
  frec = SD.open("test.RAW", FILE_WRITE);
  
  // Check that the file was open correctly
  if (frec) {
    mode = 1;
  } 
  else {
    Serial.println("File could not be opened for recording");
  }
}

// Read in another block of recorded data
void continueRecording() {
  float r1, r2, r3, r4, c1, c2, c3, c4;
  char digit = 0;

  // read all seven tone detectors
  r1 = row1.read();
  r2 = row2.read();
  r3 = row3.read();
  r4 = row4.read();
  c1 = column1.read();
  c2 = column2.read();
  c3 = column3.read();
  c4 = column4.read();

//  // print the raw data, for troubleshooting
//  Serial.print("tones: ");
//  Serial.print(r1);
//  Serial.print(", ");
//  Serial.print(r2);
//  Serial.print(", ");
//  Serial.print(r3);
//  Serial.print(", ");
//  Serial.print(r4);
//  Serial.print(",   ");
//  Serial.print(c1);
//  Serial.print(", ");
//  Serial.print(c2);
//  Serial.print(", ");
//  Serial.print(c3);
//  Serial.print(", ");
//  Serial.println(c4);

  // check all 12 combinations for tone heard
  if (r1 >= tone_threshold) {
    if (c1 > tone_threshold) {
      digit = '1';
    } 
    else if (c2 > tone_threshold) {
      digit = '2';
    } 
    else if (c3 > tone_threshold) {
      digit = '3';
    } 
    else if (c4 > tone_threshold) {
      digit = 'A';
    }
  } 
  else if (r2 >= tone_threshold) { 
    if (c1 > tone_threshold) {
      digit = '4';
    } 
    else if (c2 > tone_threshold) {
      digit = '5';
    } 
    else if (c3 > tone_threshold) {
      digit = '6';
    } 
    else if (c4 > tone_threshold) {
      digit = 'B';
    }
  } 
  else if (r3 >= tone_threshold) { 
    if (c1 > tone_threshold) {
      digit = '7';
    } 
    else if (c2 > tone_threshold) {
      digit = '8';
    } 
    else if (c3 > tone_threshold) {
      digit = '9';
    } 
    else if (c4 > tone_threshold) {
      digit = 'C';
    }
  } 
  else if (r4 >= tone_threshold) { 
    if (c1 > tone_threshold) {
      digit = 'E';
    } 
    else if (c2 > tone_threshold) {
      digit = '0';
    } 
    else if (c3 > tone_threshold) {
      digit = 'F';
    } 
    else if (c4 > tone_threshold) {
      digit = 'D';
    }
  }

  // print the key, if any found437
  if ((digit > 0)) {
    frec.write(digit);
    Serial.print("  --> Key: ");
    Serial.print(digit);
    Serial.println(" written to file.");
  }
}

// Stop recording and close the file
void stopRecording() {
  // Signify stop recording by serial output and turning LED off
  Serial.println("stopRecording");
  //digitalWrite(led, LOW);
  
  // Close the file and update the mode
  if (mode == 1) {
    frec.close();
    saveFile(frec);
  }
  mode = 0;
}

// Save the just recorded file under the user specified name
void saveFile(File file) {
  file = SD.open("test.RAW");
  if(file){
    if (file.available()) {
      int nameLen = file.read() - 48;
      Serial.println(nameLen);
      for( int i = 0; i < nameLen; i++){
        Serial.println("ok");
        char value = file.read();
        Serial.println("still");
        // this doesn't work; stalls out and kills program
        filename[i] = value;
        Serial.println("now");
      }
      Serial.print("File saved as ");
      Serial.println(filename);
    }
  }
}

// Initial steps needed to play the file
void startPlaying() {
  // Signify start playing by serial output and turning LED on
  Serial.println("startPlaying");
  //digitalWrite(led, HIGH);
  
  // Open file
  frec = SD.open("test.RAW");
  
  // Check if file opened correctly and update mode
  if (frec) {
    mode = 2;
  } 
  else {
    Serial.println("File could not be opened for playing");
  }
}

// Check if the end has been reached, stop if yes continue if no
void continuePlaying() {
  if (frec.available()) {
    int low=0;
    int high=0;
    // Read in a value from the file
    char key = frec.read();
    // Match the character to the correct tones
    switch( key ){
      case '1' :
        low = 697;
        high = 1209;
        break;
      case '2' :
        low = 697;
        high = 1336;
        break;
      case '3' :
        low = 697;
        high = 1477;
        break;
      case '4' :
        low = 770;
        high = 1209;
        break;
      case '5' :
        low = 770;
        high = 1336;
        break;
      case '6' :
        low = 770;
        high = 1477;
        break;
      case '7' :
        low = 852;
        high = 1209;
        break;
      case '8' :
        low = 852;
        high = 1336;
        break;
      case '9' :
        low = 852;
        high = 1477;
        break;
      case '0' :
        low = 941;
        high = 1336;
        break;
      case 'A' :
        low = 697;
        high = 1633;
        break;
      case 'B' :
        low = 770;
        high = 11633;
        break;
      case 'C' :
        low = 852;
        high = 1633;
        break;
      case 'D' :
        low = 941;
        high = 1633;
        break;
      case 'E' :
        low = 941;
        high = 1209;
        break;
      case 'F' :
        low = 941;
        high = 1477;
        break;
    }

    // Play the DTMF tone specified by the file
    if (low > 0 && high > 0) {
      Serial.print("Output sound for key ");
      Serial.print(key);
      Serial.print(", low freq=");
      Serial.print(low);
      Serial.print(", high freq=");
      Serial.print(high);
      Serial.println();
      AudioNoInterrupts();  // disable audio library momentarily
      sine1.frequency(low);
      sine1.amplitude(0.4);
      sine2.frequency(high);
      sine2.amplitude(0.45);
      AudioInterrupts();    // enable, both tones will start together
      delay(100);           // let the sound play for 0.1 second
      AudioNoInterrupts();
      sine1.amplitude(0);
      sine2.amplitude(0);
      AudioInterrupts();
      delay(50);            // make sure we have 0.05 second silence after
    }
  } 
  else {
    frec.close();
    mode = 0;
  }
}

// Stop playing the file
void stopPlaying() {
  // Signify stop playing by serial output and turning LED off
  Serial.println("stopPlaying");
  //digitalWrite(led, LOW);
  
  // Close file and update mode
  if (mode == 2) frec.close();
  mode = 0;
}



