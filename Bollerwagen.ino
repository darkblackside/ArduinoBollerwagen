//secrets: https://forum.arduino.cc/t/how-to-store-common-secret-values-across-programs/1138023
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <secrets.h>

#define LED 2
#define PWM D1

char ssid[] = WIFI_SSID;
char pass[] = WIFI_PASSWORD;
char updateUrl[] = BOLLERWAGEN_UPDATE_URL;
char configUrl[] = BOLLERWAGEN_CONFIG_URL;

int minResistanceSlider = 50;
int maxResistanceSlider = 750;
int deltafaktor = 1000;
int negativeBoost = 0;
int useFormula = 1;

void setup() {
  Serial.begin(115200);
  
  pinMode(LED,OUTPUT);
  pinMode(PWM,OUTPUT);
  
  WiFi.begin(ssid, pass);
  int totalCount = 0;
  while (WiFi.waitForConnectResult() != WL_CONNECTED && totalCount < 4)
  {
      Serial.println("Connection Failed! Retrying...");
      delay(5000);
      totalCount++;
  }
  updateSettings();
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
    int y = (x*x)/deltafaktor - negativeBoost;
    Serial.print("input             ");
    Serial.println(analogRead(A0));
    Serial.print("input transformed ");
    Serial.println(x);
    Serial.print("output            ");
    Serial.println(y);

    i = (i + 1) % 200;

    if(i == 0) {
      Serial.println("call update.php");
      updateOutput();
    }
    
    analogWrite(PWM, y);
    delay(100);
  }
}

void updateOutput()
{
  HTTPClient http;
  WiFiClient client;
  http.begin(client, updateUrl);
  int httpCode = http.POST("voltage1=12&voltage2=12&minValue=8&maxValue=800");

  if (httpCode > 0)
  {
      if (httpCode == HTTP_CODE_OK)
      {
          Serial.println("Connection Success!");
          String payload = http.getString();
          Serial.println(payload);
          http.end();
       }

        http.end();
    }
    else
    {
        Serial.printf("[HTTP] POST... failed, error: %s\n", http.errorToString(httpCode).c_str());
        http.end();
    }

    http.end();
}

void updateSettings()
{
  HTTPClient http;
  WiFiClient client;
  http.begin(client, configUrl);
  int httpCode = http.GET();

  if (httpCode > 0)
  {
      Serial.print("Output ");
      Serial.println(httpCode);
      if (httpCode == HTTP_CODE_OK)
      {
          Serial.println("update settings success");
          String payload = http.getString();

          String deltafaktorStr = payload.substring(0, 9);
          String useFormulaStr = payload.substring(10, 19);
          String negativeBoostStr = payload.substring(20, 29);

          deltafaktor = deltafaktorStr.toInt();
          negativeBoost = negativeBoostStr.toInt();
          useFormula = useFormulaStr.toInt();

          Serial.print("updated deltafaktor=");
          Serial.print(deltafaktor);
          Serial.print(" useFormula=");
          Serial.print(useFormulaStr);
          Serial.print(" negativeBoost=");
          Serial.print(negativeBoost);
          
          http.end();
       }

        http.end();
    }
    else
    {
        Serial.printf("[HTTP] GET... failed, error: %s\n", http.errorToString(httpCode).c_str());
        http.end();
    }

    http.end();
}
