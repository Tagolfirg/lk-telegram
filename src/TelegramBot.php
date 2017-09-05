<?php
/**
 * Created by Larakit.
 * Link: http://github.com/larakit
 * User: Alexey Berdnikov
 * Date: 01.08.16
 * Time: 12:05
 */

namespace Larakit;

class TelegramBot {
    
    static protected $messages = [];
    
    static function sendError(\Exception $e, $channel_code = null) {
        TelegramBot::add(str_repeat('=', 50));
        TelegramBot::add('#error: ' . ($e->getMessage() ? : '[Страница не найдена]'));
        TelegramBot::add(str_repeat('=', 50));
        TelegramBot::add('САЙТ: ' . \Config::get('app.url'));
        TelegramBot::add('ГДЕ: ' . larasafepath($e->getFile()) . ':' . $e->getLine());
        if(\Auth::user()) {
            TelegramBot::add('КТО: ' . \Auth::user()->name);
        } else {
            TelegramBot::add('КТО: гость ' . \Request::ip());
        }
        TelegramBot::add('URL: ' . \Request::fullUrl());
        TelegramBot::add(str_repeat('-', 80));
        static::send($channel_code ? : env('error'));
    }
    
    static function add($str) {
        if(!is_scalar($str)) {
            $str = var_export($str, true);
        }
        static::$messages[] = $str;
    }
    
    static function send($channel_code = null) {
        try {
            if(is_numeric($channel_code)) {
                $channel_id = $channel_code;
            } else {
                $channel_id = env('telegram.' . $channel_code);
                $channel_id = $channel_id ? : env('telegram');
            }
            if(!$channel_id) {
                echo 'No channel';
                
                return;
            }
            if(is_array($channel_code)) {
                foreach($channel_code as $_code) {
                    self::send($_code);
                }
                
                return;
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $partials = [];
            foreach(static::$messages as $message) {
                while(mb_strlen($message) > 0) {
                    $partials[] = mb_substr($message, 0, 1014);
                    if(mb_strlen($message) > 0) {
                        $message = mb_substr($message, 1014);
                    }
                }
            }
            foreach($partials as $partial) {
                $params = http_build_query([
                    'disable_web_page_preview' => 'true',
                    'parse_mode'               => 'HTML',
                    'chat_id'                  => $channel_id,
                    'text'                     => $partial,
                ]);
                $url    = 'https://api.telegram.org/bot' . env('telegram.key') . '/sendMessage?';
                curl_setopt($ch, CURLOPT_URL, $url . $params);
                $ret = curl_exec($ch);
            }
            curl_close($ch);
        } catch(\Exception $e) {
            echo $e->getMessage();
        }
    }
}
