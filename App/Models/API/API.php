<?php


namespace App\Models\API;

use App\Utilities;


/**
 * 
 *
 * PHP version 7.0
 */
class API
{


    /**
     * init a request for calling API
     * @return void
     */
    public static function CallAPI($method, $url, $provider = "OBJENIOUS", $json_encode = true, $data = false)
    {
        $curl = curl_init();

        //Set url
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($provider == "SENTIVE") {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        } else {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        }
        //Encoding
        curl_setopt($curl, CURLOPT_ENCODING, '');
        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                ));
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            case "GET":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }


        if ($provider == "OBJENIOUS") {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'apikey: ' . \App\Config::OBJENIOUS_API_KEY
            ));
        }

        $result = curl_exec($curl);

        curl_close($curl);

        if ($json_encode) {
            $result = json_decode($result, true);
            return $result;
        }

        return $result;
    }

    /**
     * init a request for calling API using guzzle
     * @return void
     */
    public static function callApi2($method, $url, $data = null)
    {

        $client = new \GuzzleHttp\Client();
        switch ($method) {
            case "POST":
                $res = $client->request('POST', $url, [
                    'body' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Content-Length' => strlen($data),
                    ]
                ]);
                break;
            case "PUT":
                $res = $client->put($url, []);
                break;
            case "GET":
                $res = $client->get($url);
                break;
            default:
                break;
        }
        if ($res->getStatusCode() == 200) {
            return $res->getBody()->getContents();
        }
    }
}
