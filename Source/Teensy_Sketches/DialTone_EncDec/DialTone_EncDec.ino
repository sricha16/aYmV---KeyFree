// Dial Tone (DTMF) decoding example.
//
// The audio with dial tones is connected to audio shield
// Left Line-In pin.  Dial tone output is produced on the
// Line-Out and headphones.
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
AudioAnalyzePeak         peak;          //xy=278,108
AudioRecordQueue         queue;         //xy=281,63
AudioPlaySdRaw           playRaw;       //xy=302,157
AudioAnalyzeToneDetect   row1;     // 7 tone detectors are needed
AudioAnalyzeToneDetect   row2;     // to receive DTMF dial tones
AudioAnalyzeToneDetect   row3;
AudioAnalyzeToneDetect   row4;
AudioAnalyzeToneDetect   column1;
AudioAnalyzeToneDetect   column2;
AudioAnalyzeToneDetect   column3;
AudioAnalyzeToneDetect   column4;
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
AudioConnection patchCord10(sine1, 0, mixer, 0);
AudioConnection patchCord11(sine2, 0, mixer, 1);
AudioConnection patchCord12(mixer, 0, audioOut, 0);
AudioConnection patchCord13(mixer, 0, audioOut, 1);
// For Recording and SD Card
AudioConnection patchCord21(audioIn, 0, queue, 0);
AudioConnection patchCord22(audioIn, 0, peak, 0);
AudioConnection patchCord23(playRaw, 0, audioOut, 0);
AudioConnection patchCord34(playRaw, 0, audioOut, 1);

// Create an object to control the audio shield.
AudioControlSGTL5000 audioShield;

// The input on the audio shield to use
const int myInput = AUDIO_INPUT_MIC;

// Remember which mode we're doing
int mode = 0;  // 0=stopped, 1=recording, 2=playing

// The file where data is stored
File frec;

// Objects for recording
char digit = 0;
char past = 0;

void setup() {
  // Audio connections require memory to work.  For more
  // detailed information, see the MemoryAndCpuUsage example
  AudioMemory(60);

  // Enable the audio shield and set the output volume.
  audioShield.enable();
  audioShield.inputSelect(myInput);
  audioShield.volume(0.8);
  audioShield.micGain(0);
  
  while (!Serial) ;
  delay(100);
  
  // Configure the tone detectors with the frequency and number
  // of cycles to match.  These numbers were picked for match
  // times of approx 30 ms.  Longer times are more precise.
  row1.frequency(697, 21);
  row2.frequency(770, 23);
  row3.frequency(852, 25);
  row4.frequency(941, 28);
  column1.frequency(1209, 36);
  column2.frequency(1336, 40);
  column3.frequency(1477, 44);
  column4.frequency(1633, 48);
  
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
}

const float row_threshold = 0.2;
const float column_threshold = 0.2;

void loop() {
  // check if any data has arrived from the serial monitor
  if (Serial.available()) {
    char c = Serial.read();
    if ((c == 'r' || c == 'R')) {
      Serial.println("Record command sent");
      if (mode == 2) stopPlaying();
      if (mode == 0) startRecording();
    } else if ((c == 's' || c == 'S')) {
      Serial.println("Stop command sent");
      if (mode == 1) stopRecording();
      if (mode == 2) stopPlaying();
    } else if ((c == 'p' || c == 'P')) {
      Serial.println("Play command sent");
      if (mode == 1) stopRecording();
      if (mode == 0) startPlaying();
    }
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
  Serial.println("startRecording");
  if (SD.exists("test.RAW")) {
    // The SD library writes new data to the end of the
    // file, so to start a new recording, the old file
    // must be deleted before new data is written.
    SD.remove("test.RAW");
  }
  frec = SD.open("test.RAW", FILE_WRITE);
  if (frec) {
    mode = 1;
    digit = 0;
    past = 0;
  } 
  else {
    Serial.println("File could not be opened");
  }
}

// Read in another block of recorded data
void continueRecording() {
    float r1, r2, r3, r4, c1, c2, c3, c4;
    
    // read all seven tone detectors
    r1 = row1.read();
    r2 = row2.read();
    r3 = row3.read();
    r4 = row4.read();
    c1 = column1.read();
    c2 = column2.read();
    c3 = column3.read();
    c4 = column4.read();
  
    // print the raw data, for troubleshooting
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
  if (r1 >= row_threshold) {
    if (c1 > column_threshold) {
      digit = '1';
    } else if (c2 > column_threshold) {
      digit = '2';
    } else if (c3 > column_threshold) {
      digit = '3';
    } else if (c4 > column_threshold) {
      digit = 'A';
    }
  } else if (r2 >= row_threshold) { 
    if (c1 > column_threshold) {
      digit = '4';
    } else if (c2 > column_threshold) {
      digit = '5';
    } else if (c3 > column_threshold) {
      digit = '6';
    } else if (c4 > column_threshold) {
      digit = 'B';
    }
  } else if (r3 >= row_threshold) { 
    if (c1 > column_threshold) {
      digit = '7';
    } else if (c2 > column_threshold) {
      digit = '8';
    } else if (c3 > column_threshold) {
      digit = '9';
    } else if (c4 > column_threshold) {
      digit = 'C';
    }
  } else if (r4 >= row_threshold) { 
    if (c1 > column_threshold) {
      digit = 'E';
    } else if (c2 > column_threshold) {
      digit = '0';
    } else if (c3 > column_threshold) {
      digit = 'F';
    } else if (c4 > column_threshold) {
      digit = 'D';
    }
  }
  
  // Check if digit has already been recorded
  if( digit == past ) digit = 0;
  
    // print the key, if any found
  if ((digit > 0)) {
    past = digit;
    frec.write(digit);
    Serial.print("  --> Key: ");
    Serial.print(digit);
    Serial.println(" written to file.");
  }
}

// Stop recording and close the file
void stopRecording() {
  Serial.println("stopRecording");
  if (mode == 1) {
    frec.close();
  }
  mode = 0;
}

// Initial steps needed to play the file
void startPlaying() {
  Serial.println("startPlaying");
  frec = SD.open("test.RAW");
  mode = 2;
}

// Check if the end has been reached, stop if yes continue if no
void continuePlaying() {
  if (frec.available()) {
    int low=0;
    int high=0;
    // Read in a value from the file
    char key = frec.read();
    // Match the character to the correct tones
    if (key == '1') {
      low = 697;
      high = 1209;
    } else if (key == '2') {
      low = 697;
      high = 1336;
    } else if (key == '3') {
      low = 697;
      high = 1477;
    } else if (key == 'A') {
      low = 697;
      high = 1633;
    } else if (key == '4') {
      low = 770;
      high = 1209;
    } else if (key == '5') {
      low = 770;
      high = 1336;
    } else if (key == '6') {
      low = 770;
      high = 1477;
    } else if (key == 'B') {
      low = 770;
      high = 11633;
    } else if (key == '7') {
      low = 852;
      high = 1209;
    } else if (key == '8') {
      low = 852;
      high = 1336;
    } else if (key == '9') {
      low = 852;
      high = 1477;
    } else if (key == 'C') {
      low = 852;
      high = 1633;
    } else if (key == 'E') {
      low = 941;
      high = 1209;
    } else if (key == '0') {
      low = 941;
      high = 1336;
    } else if (key == 'F') {
      low = 941;
      high = 1477;
    } else if (key == 'D') {
      low = 941;
      high = 1633;
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
  Serial.println("stopPlaying");
  if (mode == 2) frec.close();
  mode = 0;
}


