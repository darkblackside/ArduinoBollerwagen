//secrets: https://forum.arduino.cc/t/how-to-store-common-secret-values-across-programs/1138023
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <secrets.h>

#define LED 2
#define PWM D6
#define VSENS1 D8
#define VSENS2 D7

#define STATUSLED D0
#define LED1 D1
#define LED2 D2
#define LED3 D3
#define LED4 D4
#define LED5 D5

//1 = every second
int callUpdatePhp = 20;

char ssid[] = WIFI_SSID;
char pass[] = WIFI_PASSWORD;
String updateUrl(BOLLERWAGEN_UPDATE_URL);
String configUrl(BOLLERWAGEN_CONFIG_URL);

int minResistanceSlider = 50;
int maxResistanceSlider = 750;
int deltafaktor = 1000;
int boostfaktor = 0;
int useFormula = 1;

int failedUpdates = 0;

void setup() {
  Serial.begin(115200);
  
  pinMode(LED,OUTPUT);
  pinMode(STATUSLED,OUTPUT);
  pinMode(LED1,OUTPUT);
  pinMode(LED2,OUTPUT);
  pinMode(LED3,OUTPUT);
  pinMode(LED4,OUTPUT);
  pinMode(LED5,OUTPUT);
  pinMode(PWM,OUTPUT);
  
  WiFi.begin(ssid, pass);
  int totalCount = 0;
  while (WiFi.waitForConnectResult() != WL_CONNECTED && totalCount < 4)
  {
      Serial.println("Connection Failed! Retrying...");
      delay(5000);
      totalCount++;
  }
  updateOutput(0, 0, 0.0, 0.0);
}

//=======================================================================
//                    Main Program Loop
//=======================================================================
void loop() {
  int i = 0;
  while(1) {
    int analogBaseValue = analogRead(A0);
    if(analogBaseValue > maxResistanceSlider)
    {
      maxResistanceSlider = analogBaseValue;
    }
    if(analogBaseValue < minResistanceSlider)
    {
      minResistanceSlider = analogBaseValue;
    }
    
    int x = (analogBaseValue - minResistanceSlider) * (1024.0 / (maxResistanceSlider - minResistanceSlider));
    int y = (x*x)/deltafaktor + boostfaktor;
    if(useFormula == 2)
    {
      y = (x*1000/deltafaktor) * boostfaktor;
    } else if(useFormula == 3) {
      y = x * boostfaktor;
    } else if(useFormula == 4) {
      y = ((x-500)*(x-500)*(x-500)/deltafaktor) + 500;
    }
    if(y < 0) {
      y = 0;
    } 
    if(y > 1024) {
      y = 1024;
    }

    //ten means one second
    i = (i + 1) % (10 * callUpdatePhp);

    if(i == 0) {
      updateOutput(x, y, 0.0, 0.0);
    }

    writeLed(y);
    
    analogWrite(PWM, y);
    delay(100);
  }
}

void writeLed(int setValue)
{
  digitalWrite(STATUSLED, HIGH);
  digitalWrite(LED1, LOW);
  digitalWrite(LED2, LOW);
  digitalWrite(LED3, LOW);
  digitalWrite(LED4, LOW);
  digitalWrite(LED5, LOW);

  if(setValue > 10)
  {
    digitalWrite(LED1, HIGH);
  }
  if(setValue > 200)
  {
    digitalWrite(LED2, HIGH);
  }
  if(setValue > 400)
  {
    digitalWrite(LED3, HIGH);
  }
  if(setValue > 600)
  {
    digitalWrite(LED4, HIGH);
  }
  if(setValue > 800)
  {
    digitalWrite(LED5, HIGH);
  }
}

void updateOutput(int readValue, int outputValue, float voltage1, float voltage2)
{
  HTTPClient http;
  WiFiClient client;
  String fullCallUpdateUrl = updateUrl + "&readValue=" + readValue + "&outputValue=" + outputValue + "&voltage1=" + voltage1 + "&voltage2=" + voltage2 + "&failedUpdates=" + failedUpdates + "&useFunc=" + useFormula + "&deltaFak=" + deltafaktor + "&boostFak=" + boostfaktor + "&updateSeconds=" + callUpdatePhp;
  http.begin(client, fullCallUpdateUrl);
  int httpCode = http.GET();

  if (httpCode > 0)
  {
    Serial.println(fullCallUpdateUrl);
      if (httpCode == HTTP_CODE_OK)
      {
          Serial.println("update settings success");
          String payload = http.getString();

          String deltafaktorStr = payload.substring(0, 9);
          String useFormulaStr = payload.substring(10, 19);
          String boostfaktorStr = payload.substring(20, 29);
          String updateSecondsStr = payload.substring(30, 39);

          deltafaktor = deltafaktorStr.toInt();
          boostfaktor = boostfaktorStr.toInt();
          useFormula = useFormulaStr.toInt();
          callUpdatePhp = updateSecondsStr.toInt();
          
          http.end();
       } else
       {
        failedUpdates++;
       }

        http.end();
    }
    else
    {
        Serial.printf("[HTTP] GET... failed, error: %s\n", http.errorToString(httpCode).c_str());
        failedUpdates++;
        http.end();
    }

    http.end();
}
