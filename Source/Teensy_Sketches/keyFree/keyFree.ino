#include <Audio.h>
#include <SD.h>
#include <SPI.h>
#include <Wire.h>

const int myInput = AUDIO_INPUT_MIC;
//const int myInput = AUDIO_INPUT_LINEIN;
boolean micPluggedIn = false;
boolean audioPluggedIn = false;
//create Audio components
AudioInputI2S audioInput; //audio shield: mic or line-in
AudioOutputI2S audioOutput; //audio shield: headphones & line-out
AudioPlaySdWav playWav1;       //xy=388,248
AudioRecordQueue queue;

//create connections to play wav over headphone output
AudioConnection c1(playWav1, 0, audioOutput, 0);
AudioConnection c2(playWav1, 1, audioOutput, 1);
//create audio connections between mic and recorder
AudioConnection c3(audioInput, 0, queue, 0);

//create object to control audio shield
AudioControlSGTL5000 audioShield;


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
}
void loop() {
  if(micPluggedIn){
    Serial.println("Mic input detected");
    //begin recording while input flows
  }
  else if(audioPluggedIn){
    Serial.println("Audio output detected");
    //play requested track
  }
}
