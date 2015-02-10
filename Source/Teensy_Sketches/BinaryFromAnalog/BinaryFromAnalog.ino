// Record sound from mic jack and display data as ASCII.
//
// Requires the audio shield:
//   http://www.pjrc.com/store/teensy3_audio.html
//
// Takes input from the serial monitor window
//   R: starts recording
//   S: stops recording
//   B: displays data as binary
//   A: displays data as ascii
//
// This example code is in the public domain.

#include <Bounce.h>
#include <Audio.h>
#include <Wire.h>
#include <SPI.h>
#include <SD.h>

// GUItool: begin automatically generated code
AudioInputI2S            i2s2;           //xy=105,63
AudioAnalyzePeak         peak;          //xy=278,108
AudioRecordQueue         queue;         //xy=281,63
AudioPlaySdRaw           playRaw;       //xy=302,157
AudioOutputI2S           i2s1;           //xy=470,120
AudioConnection          patchCord1(i2s2, 0, queue, 0);
AudioConnection          patchCord2(i2s2, 0, peak, 0);
AudioConnection          patchCord3(playRaw, 0, i2s1, 0);
AudioConnection          patchCord4(playRaw, 0, i2s1, 1);
AudioControlSGTL5000     sgtl5000_1;     //xy=265,212
// GUItool: end automatically generated code

// which input on the audio shield will be used?
//const int myInput = AUDIO_INPUT_LINEIN;
const int myInput = AUDIO_INPUT_MIC;

// Remember which mode we're doing
int mode = 0;  // 0=stopped, 1=recording, 2=playing

// Differentiates between displaying as binary or utf8
char dis;

// The file where data is recorded
File frec;

// The file where the ascii/binary is written
File fout;

void setup() {

  // Audio connections require memory, and the record queue
  // uses this memory to buffer incoming audio.
  AudioMemory(60);

  // Enable the audio shield, select input, and enable output
  sgtl5000_1.enable();
  sgtl5000_1.inputSelect(myInput);
  sgtl5000_1.volume(0.5);

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


void loop() {

  if (Serial.available()) {
    char c = Serial.read();
    if ((c == 'r' || c == 'R')) {
      Serial.println("Record command sent");
      if (mode == 2) stopDisplaying();
      if (mode == 0) startRecording();
    }
    else if ((c == 's' || c == 'S')) {
      Serial.println("Stop command sent");
      if (mode == 1) stopRecording();
      if (mode == 2) stopDisplaying();
    }
    else if ((c == 'd' || c == 'D' || c == 'h' || c == 'H' || c == 'b' || c == 'B' || c == 'a' || c == 'A')) {
      dis = c;
      Serial.println("Binary command sent");
      if (mode == 1) stopRecording();
      if (mode == 0) startDisplaying();
    }
    else {
      Serial.println("Command not recognized. Please use one of the following:");
      Serial.println("R to begin recording");
      Serial.println("S to stop current action");
      Serial.println("D to display the contents of the recorded file in decimal");
      Serial.println("H to display the contents of the recorded file in hex");
      Serial.println("B to display the contents of the recorded file in binary");
      Serial.println("A to display the contents of the recorded file in extended ASCII");
    }
  }

  // If we're playing or recording, carry on...
  if (mode == 1) {
    continueRecording();
  }
  if (mode == 2) {
    continueDisplaying();
  }

  // when using a microphone, continuously adjust gain
  if (myInput == AUDIO_INPUT_MIC) adjustMicLevel();
}


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
    queue.begin();
    mode = 1;
  }
}

void continueRecording() {
  if (queue.available() >= 2) {
    byte buffer[512];
    // Fetch 2 blocks from the audio library and copy
    // into a 512 byte buffer.  The Arduino SD library
    // is most efficient when full 512 byte sector size
    // writes are used.
    memcpy(buffer, queue.readBuffer(), 256);
    queue.freeBuffer();
    memcpy(buffer+256, queue.readBuffer(), 256);
    queue.freeBuffer();
    // write all 512 bytes to the SD card
    elapsedMicros usec = 0;
    frec.write(buffer, 512);
    // Uncomment these lines to see how long SD writes
    // are taking.  A pair of audio blocks arrives every
    // 5802 microseconds, so hopefully most of the writes
    // take well under 5802 us.  Some will take more, as
    // the SD library also must write to the FAT tables
    // and the SD card controller manages media erase and
    // wear leveling.  The queue object can buffer
    // approximately 301700 us of audio, to allow time
    // for occasional high SD card latency, as long as
    // the average write time is under 5802 us.
    //Serial.print("SD write, us=");
    //Serial.println(usec);
  }
}

void stopRecording() {
  Serial.println("stopRecording");
  queue.end();
  if (mode == 1) {
    while (queue.available() > 0) {
      frec.write((byte*)queue.readBuffer(), 256);
      queue.freeBuffer();
    }
    frec.close();
  }
  mode = 0;
}


void startDisplaying() {
  Serial.println("startDisplaying");
  frec = SD.open("test.RAW");
  fout = SD.open("output.txt", FILE_WRITE);
  if (frec && fout) {
    mode = 2;
  }
}

void continueDisplaying() {
  // read a value from the file
  byte val = 0x00;
  if (!frec.available()) {
    stopDisplaying();
  }
  else {
    val = frec.read();
  }
  
  // display the value in the desired way
  // can also output to a file, but much slower when this is done
  // fout.print(val, BYTE);
  if (dis == 'd' || dis == 'D') Serial.println(val, DEC);
  if (dis == 'h' || dis == 'H') Serial.println(val, HEX);
  if (dis == 'b' || dis == 'B') Serial.println(val, BIN);
  if (dis == 'a' || dis == 'A') Serial.println(val, BYTE);
}

void stopDisplaying() {
  Serial.println("stopDisplaying");
  frec.close();
  fout.close();
  mode = 0;
}

void adjustMicLevel() {
  // TODO: read the peak object and adjust sgtl5000_1.micGain()
  // if anyone gets this working, please submit a github pull request :-)
}

