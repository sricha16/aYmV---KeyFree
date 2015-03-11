// Dial Tone (DTMF) encoding decoding example.
//
// A final version of the code for Waldorf
//
// This example code is in the public domain.


#include <Audio.h>
#include <Wire.h>
#include <SPI.h>
#include <SD.h>
#include <string.h>

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
AudioAnalyzeToneDetect   command;  // Signal for commanding rec, stp, ply
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
// For Command Signals
AudioConnection patchCord09(audioIn, 0, command, 0);
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

// File objects for recording, storing, and playing data
File frec;
File fply;

// The file name to play
//char *filename;

// The threshold for the signals
const float tone_threshold = 0.3;

void setup() {
  // Audio connections require memory to work.  For more
  // detailed information, see the MemoryAndCpuUsage example
  AudioMemory(60);

  // Enable the audio shield and set the output volume.
  audioShield.enable();
  audioShield.inputSelect(myInput);
  audioShield.volume(0.5);
  audioShield.micGain(0);
  
  //This is likely why it won't work without Serial...
  //while (!Serial) ;
  //delay(100);
  
  // Setting frequency and cycles for 16 hex tones
  row1.frequency(697, 21);
  row2.frequency(770, 23);
  row3.frequency(852, 25);
  row4.frequency(941, 28);
  column1.frequency(1209, 36);
  column2.frequency(1336, 40);
  column3.frequency(1477, 44);
  column4.frequency(1633, 49);
  // Setting frequency and cycle for command tone
  command.frequency(1993, 60);
  
  // Set the threshold value for each signal
  row1.threshold(tone_threshold);
  row2.threshold(tone_threshold);
  row3.threshold(tone_threshold);
  row4.threshold(tone_threshold);
  column1.threshold(tone_threshold);
  column2.threshold(tone_threshold);
  column3.threshold(tone_threshold);
  column4.threshold(tone_threshold);
  command.threshold(tone_threshold);

  // Initialize the SD card
  SPI.setMOSI(7);
  SPI.setSCK(14);
  if (!(SD.begin(10))) {
    // stop here if no SD card, but print a message
    while (1) {
      //Serial.println("Unable to access the SD card");
      delay(500);
    } // while
  } // if
} // setup


void loop() {
  char cmd = 0;
  
  while( command ){
    while( column1 ) cmd = 'r';
    while( column2 ) cmd = 'p';
    while( column3 ) cmd = 's';
  }
  
  switch( cmd ){
    case 'r' :
      // start recording if in wait mode
      if( mode == 0 ) startRecording();
      break;
    case 'p':
      // start playing if in wait mode
      if( mode == 0 ) startPlaying();
      break;
    case 's':
      // stop recording if recording
      if( mode == 1 ) stopRecording();
      // stop playing if playing
      if( mode == 2 ) stopPlaying();
      break;
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
  // Signify the start of recording via serial output
  //Serial.println("startRecording");
  
  // Check if file already exists, delete it if it does
  if (SD.exists("temp.RAW")) {
    SD.remove("temp.RAW");
  }
  
  // Open the file
  frec = SD.open("temp.RAW", FILE_WRITE);
  
  // Check that the file was open correctly
  if (frec) {
    mode = 1;
  } 
  else {
    //Serial.println("File could not be opened for recording");
  }
}

// Read in another block of recorded data
void continueRecording() {
  // Read a value from the mic jack
  char digit = readValue();
  frec.write(digit);
  //Serial.print("  --> Key: ");
  //Serial.print(digit);
  //Serial.println(" heard from browser.");
  
  // Check if stop Signal
  if( digit == 'S' ) stopRecording();
}

// Stop recording and close the file
void stopRecording() {
  // Signify stop recording by serial output
  //Serial.println("stopRecording");
  
  // Close the file and save the recorded data under the user specified name
  if (mode == 1) {
    frec.close();
    saveFile();
  }
  // Update the mode
  mode = 0;
}

// Initial steps needed to play the file
void startPlaying() {
  // Signify start playing by serial output
  //Serial.println("startPlaying");
  
  int nameLen = readValue() - 48;

  // Create an array of that length plus 1 for terminating char
  //Serial.println(nameLen);
  char filename[nameLen+5];
  // Set to empty string so has reference
  strcpy(filename, "");
  // Read the name in one char at a time and add it to filename variable
  for( int i = 0; i < (nameLen) ; i++) {
    char value, h1, h2;
    h1 = readValue();
    h2 = readValue();
    //Serial.println(h1);
    //Serial.println(h2);
    value = convertToChar(h1, h2);
    //Serial.println(value);
    if( value > 0 ) strncat(filename, &value, 1);
  }
  // output name for testing
  //Serial.print("File to play: ");
  //Serial.println(filename);
  
  // Open file
  fply = SD.open(filename);
  //fply = SD.open(fname);
  
  // Check if file opened correctly and update mode
  if (fply) {
    // Give user time to accept mic use
    delay(5000);
    
    // Signal start recording
    playNote('R');
    mode = 2;
  } 
  else {
    //Serial.println("File could not be opened for playing");
  }
}


// Check if the end has been reached, stop if yes continue if no
void continuePlaying() {
  if (fply.available()) {
    // Read in a value from the file
    char data = fply.read();
    // Play that value
    playNote(data);
  } 
  else {
    fply.close();
    playNote('S');
    mode = 0;
  }
}

// Stop playing the file
void stopPlaying() {
  // Signify stop playing by serial output
  //Serial.println("stopPlaying");
  
  // Close file and update mode
  if (mode == 2){
    fply.close();
    playNote('S');
  }
  mode = 0;
}

// Save the just recorded file under the user specified name
void saveFile() {
  File file = SD.open("temp.RAW");
  // Check that the file opened correctly
  if(file){
    // Check that there is data in the file
    if (file.available()) {
      // Read first byte to determine length of desired name
      int nameLen = file.read() - 48;
      // Create an array of that length plus 1 for terminating char
      char filename[nameLen+5];
      // Set to empty string so has reference
      strcpy(filename, "");
      // Read the name in one char at a time and add it to filename variable
      for( int i = 0; i < (nameLen) ; i++){
        char value, h1, h2;
        h1 = file.read();
        h2 = file.read();
        value = convertToChar(h1, h2);
        strncat(filename, &value, 1);
      }
      // output name for testing
      //Serial.print("File saved as ");
      //Serial.println(filename);
      
      // Check to see if a file with that name already exists
      // Deletes file if it does so user can update information
      if (SD.exists(filename)) {
        SD.remove(filename);
      }
      // Open the file to write to
      File fnew = SD.open(filename, FILE_WRITE);
      if(fnew) {
        // Copy rest of data to a new file with specified name
        while( file.available() ) {
          byte meow = file.read();
          fnew.write(meow);
        }
      }
      else {
        //Serial.println("File could not be opened for copy");
      }
      fnew.close();
      file.close();
      //Serial.println("Data successfully saved.");
    } // if (file.available())
  } // if(file)
} // saveFile( File file)

// Returns the char associated with the given signal
char readValue() {  
  char digit = 0;  
  while( digit <= 0 ) {
    while( row1 ) {
      while( column1 ) digit = '1';
      while( column2 ) digit = '2';
      while( column3 ) digit = '3';
      while( column4 ) digit = 'A';
    }
    while( row2 ) {
      while( column1 ) digit = '4';
      while( column2 ) digit = '5';
      while( column3 ) digit = '6';
      while( column4 ) digit = 'B';
    }
    while( row3 ) {
      while( column1 ) digit = '7';
      while( column2 ) digit = '8';
      while( column3 ) digit = '9';
      while( column4 ) digit = 'C';
    }
    while( row4 ) {
      while( column1 ) digit = 'E';
      while( column2 ) digit = '0';
      while( column3 ) digit = 'F';
      while( column4 ) digit = 'D';
    }
    while( command ) {
      while( column1 ) digit = 'R';
      while( column2 ) digit = 'P';
      while( column3 ) digit = 'S';
    }
  }
  return digit;
}

void playNote(char note){
  int low=0;
  int high=0;
  // Match the character to the correct tones
  switch( note ){
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
      high = 1633;
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
    case 'R' :
      low = 1993;
      high = 1209;
      break;
    case 'P' :
      low = 1993;
      high = 1336;
      break;
    case 'S' :
      low = 1993;
      high = 1477;
      break;
  }

  // Play the DTMF tone specified by the file
  if (low > 0 && high > 0) {
    //Serial.print("Output sound for key ");
    //Serial.print(note);
    //Serial.print(", low freq=");
    //Serial.print(low);
    //Serial.print(", high freq=");
    //Serial.print(high);
    //Serial.println();
    AudioNoInterrupts();  // disable audio library momentarily
    sine1.frequency(low);
    sine1.amplitude(0.4);
    sine2.frequency(high);
    sine2.amplitude(0.45);
    AudioInterrupts();    // enable, both tones will start together
    delay(110);           // let the sound play for 0.1 second
    AudioNoInterrupts();
    sine1.amplitude(0);
    sine2.amplitude(0);
    AudioInterrupts();
    delay(40);            // make sure we have 0.04 second silence after
  }
}

char convertToChar(char h1, char h2){
  char val = 0x00;
  switch( h1 ){
      case '1' :
        val = val | 0x10;
        break;
      case '2' :
        val = val | 0x20;
        break;
      case '3' :
        val = val | 0x30;
        break;
      case '4' :
        val = val | 0x40;
        break;
      case '5' :
        val = val | 0x50;
        break;
      case '6' :
        val = val | 0x60;
        break;
      case '7' :
        val = val | 0x70;
        break;
      case '8' :
        val = val | 0x80;
        break;
      case '9' :
        val = val | 0x90;
        break;
      case '0' :
        val = val | 0x00;
        break;
      case 'A' :
        val = val | 0xA0;
        break;
      case 'B' :
        val = val | 0xB0;
        break;
      case 'C' :
        val = val | 0xC0;
        break;
      case 'D' :
        val = val | 0xD0;
        break;
      case 'E' :
        val = val | 0xE0;
        break;
      case 'F' :
        val = val | 0xF0;
        break;
    }
    switch( h2 ){
      case '1' :
        val = val | 0x01;
        break;
      case '2' :
        val = val | 0x02;
        break;
      case '3' :
        val = val | 0x03;
        break;
      case '4' :
        val = val | 0x04;
        break;
      case '5' :
        val = val | 0x05;
        break;
      case '6' :
        val = val | 0x06;
        break;
      case '7' :
        val = val | 0x07;
        break;
      case '8' :
        val = val | 0x08;
        break;
      case '9' :
        val = val | 0x09;
        break;
      case '0' :
        val = val | 0x00;
        break;
      case 'A' :
        val = val | 0x0A;
        break;
      case 'B' :
        val = val | 0x0B;
        break;
      case 'C' :
        val = val | 0x0C;
        break;
      case 'D' :
        val = val | 0x0D;
        break;
      case 'E' :
        val = val | 0x0E;
        break;
      case 'F' :
        val = val | 0x0F;
        break;
    }
    if( val > 0x00 ) return val;
    else return 0x00;
}



