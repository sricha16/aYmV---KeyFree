/*
  SendValue
  
  Sends a value specified via the serial window.
  Creates a pulse of that value and outputs over headphone jack
  Reads in that same value via mic jack
  Sends back to serial window for confirmation
 
 This example code is in the public domain.
 */

#include <Bounce.h>
#include <Audio.h>
#include <Wire.h>
#include <SPI.h>
#include <SD.h>

// GUItool: begin automatically generated code
AudioSynthWaveform       waveform;      //xy=188,240
AudioEffectEnvelope      envelope;      //xy=371,237
AudioInputI2S            i2s2;           //xy=105,63
AudioAnalyzePeak         peak1;          //xy=278,108
AudioRecordQueue         queue;         //xy=281,63
AudioPlaySdRaw           playRaw1;       //xy=302,157
AudioOutputI2S           i2s1;           //xy=470,120
AudioConnection          patchCord1(i2s2, 0, queue, 0);
AudioConnection          patchCord2(i2s2, 0, peak1, 0);
AudioConnection          patchCord3(playRaw1, 0, i2s1, 0);
AudioConnection          patchCord4(playRaw1, 0, i2s1, 1);
AudioConnection          patchCord5(waveform, envelope);
AudioConnection          patchCord6(envelope, 0, i2s1, 0);
AudioConnection          patchCord7(envelope, 0, i2s1, 1);
AudioControlSGTL5000     audioShield;     //xy=265,212
// GUItool: end automatically generated code

// the input to be used
const int myInput = AUDIO_INPUT_MIC;

// the setup routine runs once when you press reset:
void setup() {
  // initialize serial communication at 9600 bits per second:
  Serial.begin(9600);
  
  // Audio connections require memory, and the record queue
  // uses this memory to buffer incoming audio.
  AudioMemory(60);
  
  // Enable the audio shield, select input, and set volume
  audioShield.enable();
  audioShield.inputSelect(myInput);
  audioShield.volume(0.5);
  
  // set up waveform
  waveform.pulseWidth(0.5);
  waveform.begin(0.4, 220, WAVEFORM_PULSE);

  // set up envelope
  envelope.attack(50);
  envelope.decay(50);
  envelope.release(250);
}


void loop() {
  
  byte value;
  byte buffer[512];
  float w;
  
  // look for a value from serial
  if (Serial.available()) {
    //read value from serial
    value = Serial.read(); 
    Serial.print("Value received: ");
    Serial.println(value, DEC);
    value = value - 48;
  
  
    // make a pulse of that value
    //waveform.amplitude(value);
    for (uint32_t i =value; i<10; i++) {
    w = i / 20.0;
    waveform.pulseWidth(w);
    envelope.noteOn();
    delay(800);
    envelope.noteOff();
    delay(600);
    }
    
    // output pulse
    envelope.noteOn();
    
    // delay slightly for stability
    delay(5);
    
    //read in value
    queue.begin();
    delay(5); // delay for stability
    if (queue.available() >=2) {
      // Fetch 2 blocks from the audio library and copy into a 512 byte buffer.
      memcpy(buffer, queue.readBuffer(), 256);
      queue.freeBuffer();
      memcpy(buffer+256, queue.readBuffer(), 256);
      queue.freeBuffer();
    }
    queue.end();
    
    // stop pulse
    envelope.noteOff();
    
    // output value read to serial monitor
    Serial.print("Value read: ");
    Serial.println(buffer[0], DEC);
  }
}
