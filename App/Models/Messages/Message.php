<?php


namespace App\Models\Messages;

use App\Utilities;

/**
 * Message class : messages are sent from the sensor
 *
 * PHP version 7.0
 */
class Message
{
    /**
     * The token value
     * @var array
     */
    protected $token;

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        //Check what kind of message we received from the sensor
        $this->typeMsgFormat = $this->checkTypeMessage($this->type);

        $this->extractDeviceProperties();
        $this->convertTimestampToDateTime();
        
        if ($this->typeMsgFormat == "uplink"){
            $this->group = explode("-", $this->group)[0];
            $this->latitude = $this->lat;
            $this->longitude = $this->lng;
            $this->msgDecoded = $this->decodePayload();
            $this->extractExternalId();

        }
    
    }



    public function getFormatMessage(){
        return $this->typeMsgFormat;
    }

    private function checkTypeMessage($type_msg)
    {
        switch ($type_msg) {
            case "ChangeStatusActive":
                return "event";
            case "ChangeStatusInactive":
                return "event";
            case "ChangeStatusError":
                return "event";
            case "ChangeStatusJoined":
                return "event";
            case "uplink":
                return "uplink";
            case "downlink":
                return "downlink";
            case "join":
                return "join";
        }
    }

    private function extractDeviceProperties(){
        $this->externalId = $this->device_properties['external_id'];
        $this->appeui =  $this->device_properties['appeui'];
        $this->deveui =  $this->device_properties['deveui'];
    }

    /**
     * extract external_id data from Objenious (which correspond to the label of a sensor in Objenious)
     *
     * @param string $external_id 
     * @return array 
     */
    private function extractExternalId()
    {
        #Remove bracket
        $asset_name_no_bracket = str_replace(array('[', ']'), '', $this->externalId);
        $asset_name_array = explode("-", $asset_name_no_bracket);
        $region = $asset_name_array[0];
        $transmission_line_name = $asset_name_array[1];

        $desc_asset = $asset_name_array[2];
        $support_asset = $asset_name_array[3];
        $corniere = $asset_name_array[4];

        #Build the asset name
        $name_asset = $desc_asset . "_" . $support_asset;

        $this->structureName = $name_asset;
        $this->transmissionLineName = $transmission_line_name;
        $this->site = $region;

        if (strpos($this->structureName, 'tower') !== false) {
            $this->typeStructure = "transmission line";
        } else {
            $this->typeStructure = "undefined";
        }
        
    }

    private function convertTimestampToDateTime($datetimeFormat = 'Y-m-d H:i:s', $fromTimeZone = 'UTC', $toTimeZone = 'CET')
    {

        //Split 2019-11-29T16:01:26.572226000Z to keep only the last part 572226000Z
        $part = explode(".", $this->timestamp);
        $second = substr($part[1], 0, 3);
        //Finnaly we get 2019-11-29T16:01:26.572Z
        $secondTimeZone = $second; //. "Z";
        $timestamp = $part[0] . "." . $secondTimeZone;

        //Objenious work with UTC Timezone
        $timezoneUTC = new \DateTimeZone($fromTimeZone);
        //Create object DateTime
        $datetime = new \DateTime($timestamp, $timezoneUTC);
        //Convert to TimeZone France
        $france_time = new \DateTimeZone($toTimeZone);
        $datetime->setTimezone($france_time);

        $date_time = $datetime->format($datetimeFormat);
        $this->dateTime = $date_time;
    }

    /**
     * Decode Payload message in order to extract information. (Inclinometer, battery, choc...)
     *
     * @param string $payload_cleartext uplink payload message
     * @return json  data decoded in json format
     */
    private function decodePayload()
    {
        $preambule_hex = substr($this->payload_cleartext, 0, 2);
        $preambule_bin = substr(Utilities::hexStr2bin($preambule_hex), 0, 2);

        if ($preambule_bin == "00") {
            $this->typeMsg = "inclinometre";
            echo "\n ==> TYPE MESSAGE RECEIVED : Inclinometre data <=== \n";
            $msgDecoded = $this->decodeInclinometreMsg($this->payload_cleartext);

        } else if ($preambule_bin == "10") {
            $this->typeMsg = "choc";
            echo "\n ==> TYPE MESSAGE RECEIVED : choc_data data <===\n";
            $msgDecoded = $this->decodeChocMsg($this->payload_cleartext);

        } else if ($preambule_bin == "11") {
            $this->typeMsg = "global";
            echo "\n ==> TYPE MESSAGE RECEIVED : global data <===\n";
            $msgDecoded = $this->decodeGlobalMsg($this->payload_cleartext);

        } else if ($preambule_bin == "01") {
            $this->typeMsg = "spectre";
            echo "\n ==> TYPE MESSAGE RECEIVED : spectre data <===\n";
            $msgDecoded = $this->decodeSpectreMsg($this->payload_cleartext);
            
        } else {
            $this->typeMsg = "undefined";
            $msgDecoded = "UNDEFINED";
        }

        $payload_decoded_json = json_decode($msgDecoded, true);

        $payload_decoded_json['dateTime'] = $this->dateTime;
        $payload_decoded_json['deveui'] = $this->deveui;

        return $payload_decoded_json;
    }


    /**
     * Decode an inclinometer message
     *
     * @param string $payload_cleartext payload data
     * @return json  data decoded in json format which contain the inclinometer raw data
     */
    private function decodeInclinometreMsg($payload_cleartext)
    {
        #Take the preambule
        $preambule_hex = substr($payload_cleartext, 0, 2);
        $preambule_bin = Utilities::hexStr2bin($preambule_hex);

        $idInclinometre = substr($preambule_bin, 0, 2);
        $occurence = substr($preambule_bin, 2, 2);
        $zeroing = substr($preambule_bin, 4, 2);

        if ($preambule_bin == 0) {
            $idInclinometre = "00";
            $occurence = "00";
            $zeroing = "00";
        }

        #Extract data from the second part
        $msgSecondPart = substr($payload_cleartext, 2, strlen($payload_cleartext) - 2);

        $X = Utilities::hex2dec(substr($msgSecondPart, 0, 4)) * 0.0625;
        $Y = Utilities::hex2dec(substr($msgSecondPart, 4, 4)) * 0.0625;
        $Z = Utilities::hex2dec(substr($msgSecondPart, 8, 4)) * 0.0625;
        $temperature =  Utilities::hex2dec(substr($msgSecondPart, 12, 4)) / 10;

        $inclinometreMsgDecoded = (object) [
            'type' => 'inclinometre',
            'idInclinometre' => $idInclinometre,
            'occurence' => $occurence,
            'zeroing' => $zeroing,
            'X' => $X,
            'Y' => $Y,
            'Z' => $Z,
            'temperature' => $temperature
        ];

        return json_encode($inclinometreMsgDecoded, true);
    }

    /**
     * Decode a choc message
     *
     * @param string $payload_cleartext payload data
     * @return json  data decoded in json format which contain the choc raw data
     */
    private function decodeChocMsg($payload_cleartext)
    {
        #Take the preambule
        $preambule_hex = substr($payload_cleartext, 0, 2);
        $preambule_bin = Utilities::hexStr2bin($preambule_hex);

        #Extract data from prembule
        $idShock = substr($preambule_bin, 0, 2);
        $limiteFrequence = substr($preambule_bin, 2, 2);
        $redondanceMsg = substr($preambule_bin, 4, 1);
        $seuil = substr($preambule_bin, 5, 3);

        #Extract data from the second part
        $msgSecondPart = substr($payload_cleartext, 2, strlen($payload_cleartext) - 2);
        $amplitude1 = Utilities::accumulatedTable16(Utilities::hex2dec(substr($msgSecondPart, 0, 2)));
        $time1 = Utilities::hex2dec(substr($msgSecondPart, 2, 2));
        $time1 = ($time1 + 1) * 200; //# 200 is micro second format

        $amplitude2 = Utilities::accumulatedTable16(Utilities::hex2dec(substr($msgSecondPart, 4, 2)));
        $time2 = Utilities::hex2dec(substr($msgSecondPart, 6, 2));
        $time2 = ($time2 + 1) * 200; //# 200 is micro second format

        $chocMsgDecoded = (object) [
            'type' => 'choc',
            'idShock' => $idShock,
            'limiteFrequence' => $limiteFrequence,
            'redondanceMsg' => $redondanceMsg,
            'seuil' => $seuil,
            'amplitude1' => $amplitude1,
            'time1' => $time1,
            'amplitude2' => $amplitude2,
            'time2' => $time2
        ];

        return json_encode($chocMsgDecoded, true);
    }

    /**
     * Decode a global message (battery data)
     *
     * @param string $payload_cleartext payload data
     * @return json  data decoded in json format which contain the battery raw data
     */
    private function decodeGlobalMsg($payload_cleartext)
    {
        #Take the preambule
        $preambule_hex = substr($payload_cleartext, 0, 2);
        $preambule_bin = Utilities::hexStr2bin($preambule_hex);

        $idglobal = substr($preambule_bin, 0, 2);
        $batteryState = substr($preambule_bin, 2, 1);
        $error = substr($preambule_bin, 3, 1);
        $state = substr($preambule_bin, 4, 1);
        $spectre = substr($preambule_bin, 5, 1);
        $inclinometre = substr($preambule_bin, 6, 1);
        $shock = substr($preambule_bin, 7, 1);

        #Extract data from the second part
        $batteryLevel = Utilities::hex2dec(substr($payload_cleartext, 2, 2));

        $globalMSGDecoded = (object) [
            'type' => 'global',
            'batteryLevel' => $batteryLevel,
            'idglobal' => $idglobal,
            'batteryState' => $batteryState,
            'error' => $error,
            'state' => $state,
            'spectre' => $spectre,
            'inclinometre' => $inclinometre,
            'shock' => $shock
        ];
        return json_encode($globalMSGDecoded);
    }

    /**
     * Decode a spectre message
     *
     * @param string $payload_cleartext payload data
     * @return json  data decoded in json format which contain the spectre raw data
     */
    private function decodeSpectreMsg($payload_cleartext)
    {
        #Take the preambule
        $spectre_msg_hex = $payload_cleartext;
        $preambule_hex = substr($payload_cleartext, 0, 2);
        $preambule_bin = Utilities::hexStr2bin($preambule_hex);
        $spectre_msg_dec = "";

        for ($i = 2; $i < intval(strlen(strval($spectre_msg_hex))); $i += 2) {
            $data_i_hex = substr($spectre_msg_hex, $i, 2);
            $data_i_dec = Utilities::hex2dec($data_i_hex);
            $spectre_msg_dec .= strval($data_i_dec);
        }

        #Extract data from prembule
        $idspectre = substr($preambule_bin, 0, 2);
        $occurence = substr($preambule_bin, 2, 2);
        $nc = substr($preambule_bin, 4, 1);
        $spectre_number = substr($preambule_bin, 5, 3);

        $resolution = 0;
        $min_freq = 0;
        $max_freq = 0;

        if (strval($spectre_number) == "000") {
            $resolution = 0;
            $min_freq = 0;
            $max_freq = 0;
        } else if (strval($spectre_number) == "001") {
            $resolution = 1;
            $min_freq = 20;
            $max_freq = 69;
        } else if (strval($spectre_number) == "010") {
            $resolution = 2;
            $min_freq = 70;
            $max_freq = 169;
        } else if (strval($spectre_number) == "011") {
            $resolution = 4;
            $min_freq = 170;
            $max_freq = 369;
        } else if (strval($spectre_number) == "100") {
            $resolution = 8;
            $min_freq = 370;
            $max_freq = 769;
        } else if (strval($spectre_number) == "101") {
            $resolution = 16;
            $min_freq = 770;
            $max_freq = 1569;
        }


        $spectreMSGDecoded = (object) [
            'type' => 'spectre',
            'spectre_number' => $spectre_number,
            'resolution' => $resolution,
            'min_freq' => $min_freq,
            'max_freq' => $max_freq,
            'spectre_msg_hex' => $spectre_msg_hex,
            'spectre_msg_dec' => $spectre_msg_dec
        ];

        return json_encode($spectreMSGDecoded, true);
    }

    /**
     * extract event data from objenious
     *
     * @param json $data json data received from Objenious
     * @return array 
     */
    private function extractEventData($data)
    {
        $date_time = $this->convertTimestampToDateTime($data['timestamp']);

        $device_id = $data['device_id'];
        $type = $data['type'];
        $device_properties = $data['device_properties'];

        $external_id = $device_properties['external_id'];
        $deveui = $device_properties['deveui'];
        $property = $device_properties['property'];

        $name_asset = $this->extractExternalId($external_id);

        $eventDataArr = array(
            "date_time"  => $date_time,
            "device_id"  => $device_id,
            "deveui"  => $deveui,
            "label"  => $type,
            "name_asset"  => $name_asset
        );

        return $eventDataArr;
    }
    

}