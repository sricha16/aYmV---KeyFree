#include <Audio.h>
#include <SD.h>
#include <SPI.h>
#include <Wire.h>
//#include "entry.h"
//#include "retrieval.h"

boolean micPluggedIn = false;
boolean audioPluggedIn = true;

const int myInput = AUDIO_INPUT_MIC;
//const int myInput = AUDIO_INPUT_LINEIN;
//create Audio components
AudioInputI2S audioInput; //audio shield: mic or line-in
AudioOutputI2S audioOutput; //audio shield: headphones & line-out
AudioPlaySdWav playWav;       //xy=388,248
AudioRecordQueue queue;
AudioPlaySdRaw   playRaw1;       //xy=302,157
AudioAnalyzePeak peak1;          //xy=278,108

//create connections to play wav over headphone output
AudioConnection c1(playWav, 0, audioOutput, 0);
AudioConnection c2(playWav, 1, audioOutput, 1);
//create audio connections between mic and recorder
AudioConnection c3(audioInput, 0, queue, 0);
AudioConnection c4(playRaw1, 0, audioOutput, 0);
AudioConnection c5(playRaw1, 0, audioOutput, 1);
AudioConnection c6(audioInput, 0, queue, 0);
AudioConnection c7(audioInput, 0, peak1, 0);

//create object to control audio shield
AudioControlSGTL5000 audioShield;

// Remember which mode we're doing
int mode = 0;  // 0=stopped, 1=recording, 2=playing

// The file where data is recorded
File frec;

void setup(){
  
  //Audio connections require memory to work
  AudioMemory(60);
  //enable audio shield & set volume
  audioShield.enable();
  audioShield.inputSelect(myInput); 
  audioShield.volume(0.6);
 
  Serial.begin(115200);
  //need to find a way to determine if recording or playing
  //set booleans
  if(micPluggedIn)
    setupEntry();
  if(audioPluggedIn)
    setupRetrieval();
}
void loop() {
  if(micPluggedIn){
    //Serial.println("Mic input detected");
    //begin recording while input flows
    listenForInput();
  }
  if(audioPluggedIn){
    //Serial.println("Audio output detected");
    //play requested track
    //setupRetrieval();
    playFile("SDTEST1.WAV");
  }
}
//RETRIEVAL~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
void setupRetrieval(){

  SPI.setMOSI(7);
  SPI.setSCK(14);
  if (!(SD.begin(10))) {
    // stop here, but print a message repetitively
    while (1) {
      Serial.println("Unable to access the SD card");
      delay(500);
    }
  }
}

void playFile(const char *filename)
{
  Serial.print("Playing file: ");
  Serial.println(filename);

  // Start playing the file.  This sketch continues to
  // run while the file plays.
  playWav.play(filename);

  // A brief delay for the library read WAV info
  delay(5);

  // Simply wait for the file to finish playing.
  while (playWav.isPlaying()) {
    // uncomment these lines if you audio shield
    // has the optional volume pot soldered
    //float vol = analogRead(15);
    //vol = vol / 1024;
    // audioShield.volume(vol);
  }
}

//ENTRY~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
void setupEntry(){
  Serial.println("setting up entry");

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


void startPlaying() {
  Serial.println("startPlaying");
  playRaw1.play("test.RAW");
  mode = 2;
}

void continuePlaying() {
  if (!playRaw1.isPlaying()) {
    playRaw1.stop();
    mode = 0;
  }
}

void stopPlaying() {
  Serial.println("stopPlaying");
  if (mode == 2) playRaw1.stop();
  mode = 0;
}

void adjustMicLevel() {
  // TODO: read the peak1 object and adjust audioShield.micGain()
  // if anyone gets this working, please submit a github pull request :-)
}
void listenForInput(){
  if (Serial.available()) {
    char c = Serial.read();
    if ((c == 'r' || c == 'R')) {
      Serial.println("Record command sent");
      if (mode == 2) stopPlaying();
      if (mode == 0) startRecording();
    }
    if ((c == 's' || c == 'S')) {
      Serial.println("Stop command sent");
      if (mode == 1) stopRecording();
      if (mode == 2) stopPlaying();
    }
    if ((c == 'p' || c == 'P')) {
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

  // when using a microphone, continuously adjust gain
  if (myInput == AUDIO_INPUT_MIC) adjustMicLevel();
}
