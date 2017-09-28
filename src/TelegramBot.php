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
        //        $str              = explode(PHP_EOL, $str);
        //        $str              = array_map(function ($item) {
        //            return mb_substr($item, 0, 1000);
        //        }, $str);
        static::$messages[] = $str;
    }
    
    const MAX = 1024;
    
    //    const MAX = 100;
    
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
            $current  = '';
            foreach(static::$messages as $message) {
                //если еще есть куда складывать
                if(mb_strlen($current) + mb_strlen($message) < self::MAX) {
                    $current .= PHP_EOL . $message;
                } else {
                    //если складывать некуда, то сбросим текущий слот в очередь
                    $partials[] = $current;
                    //очистим его
                    $current = '';
                    if(mb_strlen($message) > self::MAX) {
                        $strlen = mb_strlen($message);
                        while($strlen) {
                            $partials[] = mb_substr($message, 0, self::MAX, "UTF-8");
                            $message    = mb_substr($message, self::MAX, mb_strlen($message), "UTF-8");
                            $strlen     = mb_strlen($message);
                        }
                    } else {
                        $current = $message;
                    }
                }
                
            }
            $partials[] = $current;
            
            foreach($partials as $partial) {
                $params = http_build_query([
                    'disable_web_page_preview' => 'true',
                    'parse_mode'               => 'HTML',
                    'chat_id'                  => $channel_id,
                    'text'                     => $partial,
                ]);
                $url    = 'https://api.telegram.org/bot' . env('telegram.key') . '/sendMessage?';
                curl_setopt($ch, CURLOPT_URL, $url . $params);
                curl_exec($ch);
            }
            curl_close($ch);
        } catch(\Exception $e) {
            echo $e->getMessage();
        }
    }
    
    static function emoticon($code) {
        $emoticons = $code;
        
        return json_decode('"' . $emoticons . '"') . ' ';
    }
    
    static function headerIcon($code, $text, $cnt = 1) {
        $icon = self::emoticon($code) . ' ';
        
        return TelegramBot::add(str_repeat($icon,$cnt) . $text);
    }
    
    static function headerIconKey($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x94\x91", $text, $cnt);
    }
    
    static function headerIconHour1($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x90", $text, $cnt);
    }
    
    static function headerIconHour2($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x90", $text, $cnt);
    }
    
    static function headerIconHour3($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x92", $text, $cnt);
    }
    
    static function headerIconHour4($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x93", $text, $cnt);
    }
    
    static function headerIconHour5($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x94", $text, $cnt);
    }
    
    static function headerIconHour6($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x95", $text, $cnt);
    }
    
    static function headerIconHour7($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x96", $text, $cnt);
    }
    
    static function headerIconHour8($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x97", $text, $cnt);
    }
    
    static function headerIconHour9($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x98", $text, $cnt);
    }
    
    static function headerIconHour10($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x99", $text, $cnt);
    }
    
    static function headerIconHour11($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x9A", $text, $cnt);
    }
    
    static function headerIconHour12($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x95\x9B", $text, $cnt);
    }
    
    static function headerIconFire($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x94\xA5", $text, $cnt);
    }
    
    static function headerIconKeyOpen($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x94\x93", $text, $cnt);
    }
    
    static function headerIconEdit($text, $cnt = 1) {
        return self::headerIcon("\xE2\x9C\x8F", $text, $cnt);
    }
    
    static function headerIconAdd($text, $cnt = 1) {
        return self::headerIcon("\xE2\x9E\x95", $text, $cnt);
    }
    
    static function headerIconRestore($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x8D\x83", $text, $cnt);
    }
    
    static function headerIconDelete($text, $cnt = 1) {
        return self::headerIcon("\xE2\x9D\x8C", $text, $cnt);
    }
    
    static function headerIconBug($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x90\x9E", $text, $cnt);
    }
    
    static function headerIconThumbsUp($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x91\x8D", $text, $cnt);
    }
    
    static function headerIconThumbsDown($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x91\x8E", $text, $cnt);
    }
    
    static function headerIconMoney($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x92\xB5", $text, $cnt);
    }
    
    static function headerIconGraphUp($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x93\x88", $text, $cnt);
    }
    
    static function headerIconGraphDown($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x93\x89", $text, $cnt);
    }
    
    static function headerIconStick($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x93\x8C", $text, $cnt);
    }
    
    static function headerIconSpeaker($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x93\xA2", $text, $cnt);
    }
    
    static function headerIconLetter($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x93\xA8", $text, $cnt);
    }
    
    static function headerIconGlobe($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x8C\x8D", $text, $cnt);
    }
    
    static function headerIconPray($text, $cnt = 1) {
        return self::headerIcon("\xF0\x9F\x99\x8F", $text, $cnt);
    }
    
    static function headerIconClips($text, $cnt = 1) {
        return self::headerIcon("\xE2\x9C\x82", $text, $cnt);
    }
    
    static function headerIconSnowflake($text, $cnt = 1) {
        return self::headerIcon("\xE2\x9D\x84", $text, $cnt);
    }
    
    static function headerIconHeart($text, $cnt = 1) {
        return self::headerIcon("\xE2\x9D\xA4", $text, $cnt);
    }
    
}