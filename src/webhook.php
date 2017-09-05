<?php
//hook-урл вашего бота
TelegramBotServer::setWebhookUrl('https://bot.site.ru/webhook.php');
//токен бота
TelegramBotServer::setToken('221242699:AA******************');
//включение режима отладки, когда все сообщения боту пишутся в лог
TelegramBotServer::setDebug(true);

class TelegramBotServer {

    static $admins      = [];
    static $token       = null;
    static $webhook_url = null;
    static $debug       = true;
    static $bot_id      = null;

    static function setAdmins($ids = null) {
        static::$admins = (array) $ids;
    }

    static function setToken($val) {
        static::$token  = $val;
        $data           = explode(':', $val);
        static::$bot_id = $data[0];
    }

    static function setDebug($val) {
        static::$debug = (bool) $val;
    }

    static function setWebhookUrl($val) {
        static::$webhook_url = $val;
    }

    static function getApiUrl() {
        return 'https://api.telegram.org/bot' . static::$token . '/';
    }

    static function curlRequest($handle) {
        $response = curl_exec($handle);

        if($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl returned error $errno: $error\n");
            curl_close($handle);

            return false;
        }

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);

        if($http_code >= 500) {
            // do not wat to DDOS server if something goes wrong
            sleep(10);

            return false;
        } else {
            if($http_code != 200) {
                $response = json_decode($response, true);
                error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
                if($http_code == 401) {
                    throw new Exception('Invalid access token provided');
                }

                return false;
            } else {
                $response = json_decode($response, true);
                if(isset($response['description'])) {
                    error_log("Request was successfull: {$response['description']}\n");
                }
                $response = $response['result'];
            }
        }

        return $response;
    }

    static function apiRequest($method, $parameters) {
        if(!is_string($method)) {
            error_log("Method name must be a string\n");

            return false;
        }

        if(!$parameters) {
            $parameters = [];
        } else {
            if(!is_array($parameters)) {
                error_log("Parameters must be an array\n");

                return false;
            }
        }

        foreach($parameters as $key => &$val) {
            // encoding to JSON array parameters, for example reply_markup
            if(!is_numeric($val) && !is_string($val)) {
                $val = json_encode($val);
            }
        }
        $url = static::getApiUrl() . $method . '?' . http_build_query($parameters);

        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);

        return self::curlRequest($handle);
    }

    static function apiRequestJson($method, $parameters) {
        if(!is_string($method)) {
            error_log("Method name must be a string\n");

            return false;
        }

        if(!$parameters) {
            $parameters = [];
        } else {
            if(!is_array($parameters)) {
                error_log("Parameters must be an array\n");

                return false;
            }
        }

        $parameters["method"] = $method;
        $handle               = curl_init(self::getApiUrl());
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        return self::curlRequest($handle);
    }

    static function listen() {
        $content = file_get_contents("php://input");
        // принимаем JSON строку и преобразуем ее в переменную PHP
        $update = json_decode($content, true);
        //если требуется отладка, запишем сообщения отправленные боту в лог
        if(static::$debug) {
            $myFile = "data.txt";
            // opens the file for appending (file must already exist)
            $fh = fopen($myFile, 'w+');
            // Makes a CSV list of your post data
            //$comma_delmited_list = implode(",", $update) . "\n";
            // Write to the file
            fwrite($fh, var_export($update, true));
            // You're done
            fclose($fh);
        }

        if(!$update) {
            // receive wrong update, must not happen
            exit;
        }

        if(isset($update["message"])) {
            $message = $update["message"];
            if(isset($message['new_chat_participant'])) {
                $id = $message['new_chat_participant']['id'];
                if(static::$bot_id == $id) {
                    $group_id = $message['chat']['id'];
                    $text     = sprintf('
Всем чмоки в этом чЯтике (с) 
ID группы: %s!', $group_id);
                    self::apiRequest("sendMessage", ['chat_id' => $group_id, "text" => $text, 'parse_mode' => "Markdown"]);
                }
            }
        }

    }
}

if(php_sapi_name() == 'cli') {
    TelegramBotServer::apiRequest('setWebhook', ['url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : TelegramBotServer::$webhook_url]);
    exit;
}

TelegramBotServer::listen();
echo 'OK';
