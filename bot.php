<?php
/*
کانال شهر سورس مرجع انواع سورس کد های مختلف
بانک انواع سورس کد های مختلف به صورت کاملا تست شده
هر روز کلی سورس کد و اسکریپت منتظر شماست !

@ShahreSource
https://t.me/ShahreSource
*/
error_reporting(0);
$telegram_ip_ranges = [
['lower' => '149.154.160.0', 'upper' => '149.154.175.255'], 
['lower' => '91.108.4.0',    'upper' => '91.108.7.255'],    
];
$ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
$ok=false;
foreach ($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
$lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
$upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
if($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok=true;
}
if(!$ok) die("kill signal");
ob_start();
include('config.php');
function bot($method, $datas = [])
{
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        var_dump(curl_error($ch));
    } else {
        return json_decode($res,true);
    }
}
function ahangify($url,$data=[]){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
    $headers = array();
    $headers[] = 'Host: ahangify.com';
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:75.0) Gecko/20100101 Firefox/75.0';
    $headers[] = 'Accept: application/json, text/plain, */*';
    $headers[] = 'Accept-Language: en-US,en;q=0.5';
    $headers[] = 'Referer: https://ahangify.com/';
    $headers[] = 'X-JS-APP-VERSION: 2.9.20';
    $headers[] = 'Te: Trailers';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($result,true);
}
$up = json_decode(file_get_contents('php://input'),true);
!is_file('sudo.txt') ? file_put_contents('sudo.txt','empty'):0;
$message = $up['message'];
$callback=$up['callback_query'];
$date=time();
$cl_id=$callback['id'];
$inline=$up['inline_query'];
$in_id=$inline['id'];
$in_from_id=$inline['from']['id'];
$data=$callback['data'];
$query=$inline['query'];
$cl_from_id=$callback['from']['id'];
$msg_id=$message['message_id'];
$chat_id = $message['chat']['id'];
$from_id = $message['from']['id'];
$text = $message['text'];
$button=json_encode(['inline_keyboard'=>[
    [
        ['text'=>'🎵آهنگ جدید🎵','switch_inline_query_current_chat'=>'newtrack'],
        ['text'=>'🔥آهنگ داغ هفته🔥','switch_inline_query_current_chat'=>'topweek']
    ],
    [
        ['text'=>'🔥هنرمندان برتر هفته🔥','switch_inline_query_current_chat'=>'topartist']
    ],
    [
        ['text'=>'❤️مورد علاقه ها','switch_inline_query_current_chat'=>'mylist'],
        ['text'=>'🧹پاکسازی مورد علاقه ها','callback_data'=>'clmylist']
    ]
]]);
if(!empty($from_id) && $from_id!=$sudo) {
    $info = $conn->query("SELECT * FROM users_music WHERE id=$from_id");
    $info->num_rows<1 ? $conn->query("INSERT INTO users_music (id,block,spam,timee) VALUES ($from_id,'no',0,$date)"):$res=$info->fetch_assoc();
}elseif(!empty($cl_from_id) && $cl_from_id!=$sudo){
    $info = $conn->query("SELECT * FROM users_music WHERE id=$cl_from_id");
    $info->num_rows<1 ? $conn->query("INSERT INTO users_music (id,block,spam,timee) VALUES ($cl_from_id,'no',0,$date)"):$res=$info->fetch_assoc();
}
if(!empty($res)){
    if($res['block']=='yes'){
        $conn->close();
        die();
    }else{
        $timer = $res['timee'] + 5;
        $sp=++$res['spam'];
        if ($date <= $timer) {
            if($res['spam']>=7){
                $conn->query("UPDATE users_music SET block='yes',spam=0 WHERE id=".$res['id']);
                bot('sendmessage',[
                    'chat_id'=>$res['id'],
                    'text'=>'کاربر گرامی به دلیل ارسال اسپم شما مسدود شدید و نمی توانید از ربات استفاده نمایید.'
                ]);
                $conn->close();
                die();
            }else{
                $conn->query("UPDATE users_music SET spam=$sp WHERE id=".$res['id']);
            }
        } else {
            $conn->query("UPDATE users_music SET spam=1,timee=$date WHERE id=".$res['id']);
        }
        foreach ($channel as $value){
            $r=bot('getchatmember',['chat_id'=>$value,'user_id'=>$res['id']]);
            if(!$r['ok'] || in_array($r['result']['status'],['kicked','left'])){
                bot('sendMessage',[
                    'chat_id'=>$res['id'],
                    'text'=>'برای استفاده از ربات ابتدا در کانال های زیر عضو شوید:'."\n".implode("\n",$channel)
                ]);
                $conn->close();
                die();
            }
        }
    }
}else{
    $id=!empty($from_id) ? $from_id:$cl_from_id;
    empty($id) ? $id=$in_from_id:0;
    foreach ($channel as $value){
        $r=bot('getchatmember',['chat_id'=>$value,'user_id'=>$id]);
        if(!$r['ok'] || in_array($r['result']['status'],['kicked','left'])){
            bot('sendMessage',[
                'chat_id'=>$id,
                'text'=>'برای استفاده از ربات ابتدا در کانال های زیر عضو شوید:'."\n".implode("\n",$channel)
            ]);
            $conn->close();
            die();
        }
    }
}
if($message['chat']['type']=='private'){
    if($from_id==$sudo){
        $step=file_get_contents('sudo.txt');
        if($step=='empty') {
            if ($text == '/panel') {
                bot('sendMessage', [
                    'chat_id' => $sudo,
                    'text' => '<i>سلام ادمین خوش اومدی.</i>',
                    'parse_mode' => 'html',
                    'reply_markup' => json_encode(['inline_keyboard' => [
                        [
                            ['text' => '🗣فروارد همگانی', 'callback_data' => 'fwd'],
                            ['text' => '👤آمار', 'callback_data' => 'amar']
                        ],
                        [
                            ['text' => '🚫 مسدود و حذف مسدودیت', 'callback_data' => 'block']
                        ],
                        [
                            ['text'=>'❌دریافت لیست مسدودین❌','callback_data'=>'blocklist']
                        ]
                    ]])
                ]);
                $conn->close();
                die();
            }elseif(preg_match('/^\/block ([0-9]+)$/',$text,$m)){
                $in=$conn->query('SELECT block FROM users_music WHERE id='.$m[1]);
                if($in->num_rows<1){
                    bot('sendMessage',[
                        'chat_id'=>$sudo,
                        'text'=>"<i>چنین کاربری در ربات وجود ندارد.</i>",
                        'parse_mode'=>'html',
                    ]);
                }else{
                    $r=$in->fetch_assoc();
                    if($r['block']=='yes'){
                        bot('sendMessage',[
                            'chat_id'=>$sudo,
                            'text'=>"<i>کاربر مورد نظر از قبل مسدود است.</i>",
                            'parse_mode'=>'html',
                        ]);
                    }else{
                        bot('sendMessage',[
                            'chat_id'=>$sudo,
                            'text'=>"<i>کاربر مورد نظر مسدود شد.</i>",
                            'parse_mode'=>'html',
                        ]);
                        $conn->query("UPDATE users_music SET block='yes' WHERE id=$m[1]");
                        bot('sendMessage',[
                            'chat_id'=>$m[1],
                            'text'=>"<i>شما توسط مدیریت از ربات مسدود شدید و نمی توانید از آن استفاده کنید.</i>",
                            'parse_mode'=>'html',
                        ]);
                    }
                }
                $conn->close();
                die();
            }elseif(preg_match('/^\/unblock ([0-9]+)$/',$text,$m)){
                $in=$conn->query('SELECT block FROM users_music WHERE id='.$m[1]);
                if($in->num_rows<1){
                    bot('sendMessage',[
                        'chat_id'=>$sudo,
                        'text'=>"<i>چنین کاربری در ربات وجود ندارد.</i>",
                        'parse_mode'=>'html',
                    ]);
                }else{
                    $r=$in->fetch_assoc();
                    if($r['block']=='no'){
                        bot('sendMessage',[
                            'chat_id'=>$sudo,
                            'text'=>"<i>کاربر مورد نظر از قبل آزاد است.</i>",
                            'parse_mode'=>'html',
                        ]);
                    }else{
                        bot('sendMessage',[
                            'chat_id'=>$sudo,
                            'text'=>"<i>کاربر مورد نظر آزاد شد.</i>",
                            'parse_mode'=>'html',
                        ]);
                        $conn->query("UPDATE users_music SET block='no' WHERE id=$m[1]");
                        bot('sendMessage',[
                            'chat_id'=>$m[1],
                            'text'=>"<i>شما توسط مدیریت از لیست مسدودین خارج شدید.</i>",
                            'parse_mode'=>'html',
                        ]);
                    }
                }
                $conn->close();
                die();
            }
        }else{
            if($text=='لغو'){
                bot('sendMessage',[
                    'chat_id'=>$sudo,
                    'text'=>"<i>عملیات لغو گردید.</i>",
                    'parse_mode'=>'html',
                    'reply_markup'=>json_encode(['remove_keyboard'=>true])
                ]);
                file_put_contents('sudo.txt','empty');
            }elseif($step=='fwd'){
                bot('sendMessage',[
                    'chat_id'=>$sudo,
                    'text'=>"<i>ارسال پیام آغاز شد.</i>",
                    'parse_mode'=>'html',
                    'reply_markup'=>json_encode(['remove_keyboard'=>true])
                ]);
                file_put_contents('sudo.txt','empty');
                $in=$conn->query('SELECT id FROM users_music');
                $num=0;
                while ($r=$in->fetch_assoc()){
                    $r2=bot('forwardMessage',[
                        'chat_id'=>$r['id'],
                        'from_chat_id'=>$sudo,
                        'message_id'=>$message['message_id']
                    ]);
                    $r2['ok'] ? $num++:0;
                }
                bot('sendMessage',[
                    'chat_id'=>$sudo,
                    'text'=>"<i>پیام شما با موفقیت به $num نفر ارسال شد.</i>",
                    'parse_mode'=>'html',
                    'reply_markup'=>json_encode(['remove_keyboard'=>true])
                ]);
            }
            $conn->close();
            die();
        }
    }
    if(strstr($text,'#ignore')){die();}
    if($text=='/start'){
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"<i>با سلام خوش آمدید.\nبرای جستجوی آهنگ نام خواننده,نام آهنگ یا قسمتی از متن آهنگ را برای من ارسال کنید.</i>",
            'parse_mode'=>'html',
            'reply_markup'=>$button
        ]);
        $conn->close();
        die();
    }elseif(preg_match('/^\/d([a-zA-Z0-9]+)$/',$text,$m) || preg_match('/^\/[Ss][Tt][Aa][Rr][Tt] ([a-zA-Z0-9]+)$/',$text,$m)){
        $r=bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"<i>در حال دریافت اطلاعات...</i>",
            'parse_mode'=>'html'
        ]);
        $in=ahangify("https://ahangify.com/app-api/tracks/$m[1]");
        if(isset($in['track']['id'])){
            $cn=3;
            $cn2=['M','K',''];
            $like=$in['track']['like_count'];
            while($cn-- && $like>=1000){
                $like/=1000;
            }
            $like=round($like,1).$cn2[$cn];
            $comment=!empty($in['comment_count']) ? $in['comment_count']:0;
            $url=$in['track']['url'];
            bot('editMessageText',[
                'chat_id'=>$chat_id,
                'text'=>'<i>در حال دریافت لینک...</i>',
                'message_id'=>$r['result']['message_id'],
                'parse_mode'=>'html'
            ]);
            $in=ahangify("https://ahangify.com/app-api/tracks/$m[1]/file",[
                'url'=>$url
            ]);
            if(!empty($in['file'])) {
                bot('editMessageText', [
                    'chat_id' => $chat_id,
                    'text' => "<i>درحال ارسال آهنگ...\nدر صورت ارسال نشدن از طریق دکمه زیر اقدام به دانلود آن نمایید.</i>",
                    'message_id' => $r['result']['message_id'],
                    'parse_mode' => 'html',
                    'reply_markup' => json_encode(['inline_keyboard' => [[['text' => '📥', 'url' => $in['file']]]]])
                ]);
                $r2 = bot('sendAudio', [
                    'chat_id' => $chat_id,
                    'audio' => $in['file'],
                    'caption'=>'<a href="https://t.me/'.$userbot.'">جستجوی موزیک 🎧</a>',
                    'parse_mode'=>'html',
                    'reply_markup' => json_encode(['inline_keyboard' => [
                        [
                            ['text' => "❤️$like", 'callback_data' => 'like'],
                            ['text' => "💬($comment)", 'switch_inline_query_current_chat' => "comment;$m[1]"],
                            ['text' => '➕', 'switch_inline_query_current_chat' =>"op;$m[1]"]
                        ],
                        [
                            ['text' => 'Demo', 'switch_inline_query_current_chat' => "dem;$m[1]"],
                            ['text' => '📃', 'switch_inline_query_current_chat' => "tx;$m[1]"],
                            ['text' => 'share', 'callback_data' => 'share']
                        ]
                    ]])
                ]);
                $r2['ok'] ? bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $r['result']['message_id']]) : 0;
            }else{
                bot('editMessageText', [
                    'chat_id' => $chat_id,
                    'text' => "<i>متاسفانه لینک دانلود در سرور یافت نشد.</i>",
                    'message_id' => $r['result']['message_id'],
                    'parse_mode' => 'html'
                ]);
            }
        }else{
            bot('editMessageText',[
                'chat_id'=>$chat_id,
                'text'=>'<i>نتیجه ای یافت نشد.</i>',
                'message_id'=>$r['result']['message_id'],
                'parse_mode'=>'html'
            ]);
        }
        $conn->close();
        die();
    }elseif(preg_match('/^\/al([a-zA-Z0-9]+)$/',$text,$m)){
        $r=bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"<i>لطفا صبر کنید...</i>",
            'parse_mode'=>'html'
        ]);
        $in=ahangify('https://ahangify.com/app-api/albums/'.$m[1]);
        foreach ($in['tracks'] as $k=>$v){
            if($k>25){break;}
            $title=htmlspecialchars($v['title']);
            $performer=htmlspecialchars($v['performer']);
            $size=['گیگابایت','مگابایت','کیلوبایت','بایت'];
            $csize=4;
            while ($csize-- && $v['size']>=1024){
                $v['size']/=1024;
            }
            $duration=gmdate('i:s',$v['duration']);
            $size2=round($v['size'],1).' '.$size[$csize];
            $txt.="نام : "."<code>$title</code>\n"."خواننده :‌ "."<code>$performer</code>\n<code>"."🕒 $duration - 💾 $size2"."</code>\n"."📥 دانلود:"."/d".$v['id']."\n‏----------------------------------\n";
        }
        bot('editMessageText',[
            'chat_id'=>$chat_id,
            'text'=>$txt,
            'message_id'=>$r['result']['message_id'],
            'parse_mode'=>'html'
        ]);
    }elseif(preg_match('/^\/ar([a-zA-Z0-9]+)$/',$text,$m)){
        $r=bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"<i>لطفا صبر کنید...</i>",
            'parse_mode'=>'html'
        ]);
        $txt="🎶 ترانه‌های محبوب:\n";
        $in=ahangify('https://ahangify.com/app-api/artists/'.$m[1].'/popularTracks');
        foreach ($in['data'] as $k=>$v){
            if($k>25){break;}
            if($v['size']>20971520){continue;}
            $cover=!empty($v['cover']) ? $v['cover']:$music_picture;
            $title=htmlspecialchars($v['title']);
            $performer=htmlspecialchars($v['performer']);
            $size=['گیگابایت','مگابایت','کیلوبایت','بایت'];
            $csize=4;
            while ($csize-- && $v['size']>=1024){
                $v['size']/=1024;
            }
            $duration=gmdate('i:s',$v['duration']);
            $size2=round($v['size'],1).' '.$size[$csize];
            $txt.="نام : "."<code>$title</code>\n"."خواننده :‌ "."<code>$performer</code>\n<code>"."🕒 $duration - 💾 $size2"."</code>\n"."📥 دانلود:"."/d".$v['id']."\n‏----------------------------------\n";
        }
        bot('editMessageText',[
            'chat_id'=>$chat_id,
            'text'=>$txt,
            'message_id'=>$r['result']['message_id'],
            'parse_mode'=>'html',
            'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>'🎧آهنگ ها','switch_inline_query_current_chat'=>"ah;$m[1]"]]]])
        ]);
        $conn->close();
        die();
    }elseif(!empty($text)){
        $r=bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"<i>لطفا صبر کنید...</i>",
            'parse_mode'=>'html'
        ]);
        $text=str_replace(' ','+',$text);
        $search=ahangify('https://ahangify.com/app-api/search',[
            'value'=>$text
        ]);
        $artist=count($search['artists']);
        $album=count($search['albums']);
        $track=count($search['tracks']);
        if($track<1){
            bot('editMessageText',[
                'chat_id'=>$chat_id,
                'text'=>'<i>نتیجه ای یافت نشد.</i>',
                'message_id'=>$r['result']['message_id'],
                'parse_mode'=>'html'
            ]);
        }else{
            $txt='';
            $button2=[];
            $button2['inline_keyboard'][0][]=['text'=>'🎵آهنگ ها','switch_inline_query_current_chat'=>'as;'.mb_substr($text,0,50)];
            $album>0 ? $button2['inline_keyboard'][0][]=['text'=>'📀 آلبوم','switch_inline_query_current_chat'=>'al;'.mb_substr($text,0,50)]:0;
            $artist>0 ? $button2['inline_keyboard'][0][]=['text'=>'🗣 هنرمند','switch_inline_query_current_chat'=>'ar;'.mb_substr($text,0,50)]:0;
            foreach ($search['tracks'] as $k=>$v){
                if($k>25){break;}
                if($v['size']>20971520){continue;}
                $title=!empty($v['title']) ? htmlspecialchars($v['title']):'بدون عنوان';
                $performer=htmlspecialchars($v['performer']);
                $size=['گیگابایت','مگابایت','کیلوبایت','بایت'];
                $csize=4;
                while ($csize-- && $v['size']>=1024){
                    $v['size']/=1024;
                }
                $duration=gmdate('i:s',$v['duration']);
                $size2=round($v['size'],1).' '.$size[$csize];
                $txt.="نام : "."<code>$title</code>\n"."خواننده :‌ "."<code>$performer</code>\n<code>"."🕒 $duration - 💾 $size2"."</code>\n"."📥 دانلود:"."/d".$v['id']."\n‏----------------------------------\n";
            }
            bot('editMessageText',[
                'chat_id'=>$chat_id,
                'text'=>$txt,
                'message_id'=>$r['result']['message_id'],
                'parse_mode'=>'html',
                'reply_markup'=>json_encode($button2)
            ]);
        }
    }
    $conn->close();
    die();
}
//===========================CallbackData===========================
if(!empty($data)){
    if($cl_from_id==$sudo){
        if($data=='amar'){
            $in=$conn->query('SELECT id FROM users_music');
            $cn=$in->num_rows;
            bot('answerCallbackQuery', [
                'callback_query_id' => $cl_id,
                'text' => "آمار ربات $cn نفر است.",
                'show_alert'=>true
            ]);
            $conn->close();
            die();
        }elseif($data=='block'){
            bot('answerCallbackQuery', [
                'callback_query_id' => $cl_id,
                'text' => "برای این کار از دستورات زیر استفاده کنید:"."\n/block (id)\n/unblock (id)",
                'show_alert'=>true
            ]);
            $conn->close();
            die();
        }elseif($data=='fwd'){
            bot('sendMessage',[
                'chat_id'=>$sudo,
                'text'=>'<i>پیام خود را ارسال کنید.</i>',
                'parse_mode'=>'html',
                'reply_markup'=>json_encode(['resize_keyboard'=>true,'keyboard'=>[[['text'=>'لغو']]]])
            ]);
            file_put_contents('sudo.txt','fwd');
            $conn->close();
            die();
        }elseif($data=='blocklist'){
            $in=$conn->query('SELECT id FROM users_music WHERE block="yes"');
            if($in->num_rows<1){
                bot('answerCallbackQuery', [
                    'callback_query_id' => $cl_id,
                    'text'=>'لیست مسدودین خالی است.',
                    'show_alert'=>true
                ]);
            }else{
                $r=bot('sendMessage',[
                    'chat_id'=>$sudo,
                    'text'=>'<i>در حال استخراج...</i>',
                    'parse_mode'=>'html'
                ]);
                $file=fopen('blocks.txt','w');
                while($r2=$in->fetch_assoc()){
                    fwrite($file,$r2['id']."\n");
                }
                bot('editMessageText',[
                    'chat_id'=>$sudo,
                    'text'=>'<i>در حال ارسال...</i>',
                    'message_id'=>$r['result']['message_id'],
                    'parse_mode'=>'html'
                ]);
                fclose($file);
                bot('sendDocument',[
                    'chat_id'=>$sudo,
                    'document'=>new CURLFile('blocks.txt')
                ]);
                unlink('blocks.txt');
            }
        }
    }
    if($data=='share'){
        $file_id=$callback['message']['audio']['file_id'];
        $title=$callback['message']['audio']['title'];
        if(empty($title)){
            bot('answerCallbackQuery', [
                'callback_query_id' => $cl_id,
                'text' => "این آهنگ به دلیل نداشتن عنوان در سرور تلگرام امکان اشتراک ندارد.",
                'show_alert'=>true
            ]);
        }else{
            bot('answerCallbackQuery', [
                'callback_query_id' => $cl_id,
                'text' => "حالا دوباره بر روی دکمه share برای اشتراک کلیک کنید.",
                'show_alert' => true
            ]);
            $keyboard = $callback['message']['reply_markup'];
            $keyboard['inline_keyboard'][1][2] = ['text' => 'share', 'switch_inline_query' => "share;$file_id"];
            bot('editMessageReplyMarkup', [
                'message_id' => $callback['message']['message_id'],
                'chat_id' => $cl_from_id,
                'reply_markup' => json_encode($keyboard)
            ]);
        }
    }elseif($data=='like'){
        $file_id=$callback['message']['audio']['file_id'];
        $title=$callback['message']['audio']['title'];
        if(!empty($title)) {
            if (is_file("$cl_from_id.txt")) {
                $ids=file_get_contents("$cl_from_id.txt");
                if(strstr($ids,$file_id)){
                    bot('answerCallbackQuery', [
                        'callback_query_id' => $cl_id,
                        'text' => "از قبل در مورد علاقه ها موجود است.",
                        'show_alert' => true
                    ]);
                }else{
                    $cn=count(explode("\n",$ids));
                    if($cn<50){
                        bot('answerCallbackQuery', [
                            'callback_query_id' => $cl_id,
                            'text' => "به مورد علاقه ها اضافه شد.",
                            'show_alert' => true
                        ]);
                        file_put_contents("$cl_from_id.txt","\n$file_id",FILE_APPEND);
                    }else{
                        bot('answerCallbackQuery', [
                            'callback_query_id' => $cl_id,
                            'text' => "لیست شما به حداکثر تعداد رسیده است.",
                            'show_alert' => true
                        ]);
                    }
                }
            } else {
                bot('answerCallbackQuery', [
                    'callback_query_id' => $cl_id,
                    'text' => "به مورد علاقه ها اضافه شد.",
                    'show_alert' => true
                ]);
                file_put_contents("$cl_from_id.txt", $file_id);
            }
        }else{
            bot('answerCallbackQuery', [
                'callback_query_id' => $cl_id,
                'text' => "این آهنگ به دلیل نداشتن عنوان در سرور تلگرام نمی تواند ثبت شود.",
                'show_alert' => true
            ]);
        }
    }elseif($data=='clmylist'){
        if(is_file("$cl_from_id.txt")){
            bot('answerCallbackQuery', [
                'callback_query_id' => $cl_id,
                'text'=>'پاکسازی انجام شد.',
                'show_alert' => true
            ]);
            unlink("$cl_from_id.txt");
        }else{
            bot('answerCallbackQuery', [
                'callback_query_id' => $cl_id,
                'text'=>'شما لیستی ندارید.',
                'show_alert' => true
            ]);
        }
    }
    $conn->close();
    die();
}
//============================InlineQuery=======================
if(!empty($query)){
    if(preg_match('/^dem;([a-zA-Z0-9]+)$/',$query,$m)){
        $in=ahangify('https://ahangify.com/app-api/tracks/'.$m[1]);
        if(!empty($in['track']['preview'])) {
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'switch_pm_text'=>'🎵'.$in['track']['title'],
                'switch_pm_parameter'=>$in['track']['id'],
                'results' => json_encode([
                    [
                        'id' => $m[1],
                        'type' => 'voice',
                        'title' => $in['track']['title'],
                        'voice_url' => $in['track']['preview'],
                        'voice_duration' => 30
                    ]
                ])
            ]);
        }else{
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'results' => json_encode([
                    [
                        'id' => $m[1],
                        'type' => 'article',
                        'title' => 'نتیجه ای یافت نشد.',
                        'input_message_content'=>['message_text'=>'نتیجه ای یافت نشد.']
                    ]
                ])
            ]);
        }
    }elseif(preg_match('/^al;([\s\S]+)$/',$query,$m)){
        $in=ahangify('https://ahangify.com/app-api/search',[
            'value'=>$m[1]
        ]);
        $results=[];
        foreach($in['albums'] as $k=>$v){
            if($k>49){break;}
            $cover=!empty($v['cover']) ? $v['cover']:$music_picture;
            $results[]=[
                'id'=>$v['id'],
                'type'=>'article',
                'title'=>$v['name'],
                'description'=>$v['performer'],
                'input_message_content'=>['message_text'=>'/al'.$v['id']],
                'thumb_url'=>$cover
            ];
        }
        bot('answerInlineQuery',[
            'inline_query_id'=>$in_id,
            'results'=>json_encode($results)
        ]);
    }elseif(preg_match('/^ar;([\s\S]+)$/',$query,$m)){
        $in=ahangify('https://ahangify.com/app-api/search',[
            'value'=>$m[1]
        ]);
        $results=[];
        foreach($in['artists'] as $k=>$v){
            if($k>49){break;}
            $cover=!empty($v['cover']) ? $v['cover']:$music_picture;
            $results[]=[
                'id'=>$v['id'],
                'type'=>'article',
                'title'=>$v['name'],
                'input_message_content'=>['message_text'=>'/ar'.$v['id']],
                'thumb_url'=>$cover
            ];
        }
        bot('answerInlineQuery',[
            'inline_query_id'=>$in_id,
            'results'=>json_encode($results)
        ]);
    }elseif(preg_match('/^ah;([a-zA-Z0-9]+)$/',$query,$m)){
        $in=ahangify("https://ahangify.com/app-api/artists/$m[1]/tracks");
        $results=[];
        foreach($in['data'] as $k=>$v){
            if($k>49){break;}
            if($v['size']>20971520){continue;}
            $cover=!empty($v['cover']) ? $v['cover']:$music_picture;
            $size=['گیگابایت','مگابایت','کیلوبایت','بایت'];
            $csize=4;
            while ($csize-- && $v['size']>=1024){
                $v['size']/=1024;
            }
            $duration=gmdate('i:s',$v['duration']);
            $likes=$v['like_count'];
            $like=['M','K',''];
            $clike=3;
            while ($clike-- && $likes>=1000){
                $likes/=1000;
            }
            $size2=round($v['size'],1).' '.$size[$csize];
            $likes=round($likes,1).$like[$clike];
            $results[]=[
                'id'=>"$k",
                'type'=>'article',
                'title'=>$v['title'],
                'description'=>$v['performer']."\n🕒 $duration   💾 $size2 ❤️ $likes",
                'input_message_content'=>['message_text'=>'/d'.$v['id']],
                'thumb_url'=>$cover
            ];
        }
        bot('answerInlineQuery',[
            'inline_query_id'=>$in_id,
            'results'=>json_encode($results)
        ]);
    }elseif(preg_match('/^tx;([a-zA-Z0-9]+)$/',$query,$m)){
        $in=ahangify('https://ahangify.com/app-api/tracks/'.$m[1]);
        $title=!empty($in['track']['title']) ? $in['track']['title']:'بدون عنوان';
        $tx=mb_substr($in['track']['lyric'],0,116).'...';
        $tx2=!empty($in['track']['lyric']) ? mb_substr($in['track']['lyric'],0,4080)."...\n#ignore":"/start";
        if(!empty($in['track']['lyric'])) {
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'switch_pm_text' => '🎵' . $in['track']['title'],
                'switch_pm_parameter' => $in['track']['id'],
                'results' => json_encode([
                    [
                        'id' => '1',
                        'type' => 'article',
                        'title' => $title,
                        'description' => $tx,
                        'input_message_content' => ['message_text' => $tx2]
                    ]
                ])
            ]);
        }else{
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'results' => json_encode([
                    [
                        'id' => '1',
                        'type' => 'article',
                        'title' => 'نتیجه ای یافت نشد.',
                        'input_message_content' => ['message_text' => $tx2]
                    ]
                ])
            ]);
        }
    }elseif($query=='newtrack'){
        $in=ahangify('https://ahangify.com/app-api/tracks');
        $results=[];
        for($i=0;$i<=49;$i++){
            if($in[$i]['size']>20971520){continue;}
            $size=['گیگابایت','مگابایت','کیلوبایت','بایت'];
            $csize=4;
            while ($csize-- && $in[$i]['size']>=1024){
                $in[$i]['size']/=1024;
            }
            $duration=gmdate('i:s',$in[$i]['duration']);
            $likes=$in[$i]['like_count'];
            $like=['M','K',''];
            $clike=3;
            while ($clike-- && $likes>=1000){
                $likes/=1000;
            }
            $cover=!empty($in[$i]['cover']) ? $in[$i]['cover']:$music_picture;
            $size2=round($in[$i]['size'],1).' '.$size[$csize];
            $likes=round($likes,1).$like[$clike];
            $results[]=[
                'id'=>"$i",
                'title'=>$in[$i]['title'],
                'type'=>'article',
                'input_message_content'=>['message_text'=>'/d'.$in[$i]['id']],
                'description'=>$in[$i]['performer']."\n🕒 $duration   💾 $size2 ❤️ $likes",
                'thumb_url'=>$cover
            ];
        }
        bot('answerInlineQuery',[
            'inline_query_id'=>$in_id,
            'results'=>json_encode($results)
        ]);
    }elseif($query=='topweek'){
        isset($inline['offset']) ? $week=$inline['offset']:$week='';
        $in=ahangify('https://ahangify.com/app-api/charts/tracks',[
            'week'=>$week
        ]);
        $bweek=$in['prev'];
        $in=$in['chart'];
        $results=[];
        for($i=0;$i<=49;$i++){
            if($in[$i]['art']['size']>20971520){continue;}
            $size=['گیگابایت','مگابایت','کیلوبایت','بایت'];
            $csize=4;
            while ($csize-- && $in[$i]['art']['size']>=1024){
                $in[$i]['art']['size']/=1024;
            }
            $duration=gmdate('i:s',$in[$i]['art']['duration']);
            $likes=$in[$i]['art']['like_count'];
            $like=['M','K',''];
            $clike=3;
            while ($clike-- && $likes>=1000){
                $likes/=1000;
            }
            $cover=!empty($in[$i]['art']['cover']) ? $in[$i]['art']['cover']:$music_picture;
            $size2=round($in[$i]['art']['size'],1).' '.$size[$csize];
            $likes=round($likes,1).$like[$clike];
            $results[]=[
                'id'=>"$i",
                'title'=>$in[$i]['art']['title'],
                'type'=>'article',
                'input_message_content'=>['message_text'=>'/d'.$in[$i]['art']['id']],
                'description'=>$in[$i]['art']['performer']."\n🕒 $duration   💾 $size2 ❤️ $likes",
                'thumb_url'=>$cover
            ];
        }
        if($inline['offset']<=1 && !empty($inline['offset'])){
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'results' => json_encode($results)
            ]);
        }else {
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'next_offset' =>"$bweek",
                'results' => json_encode($results)
            ]);
        }
    }elseif($query=='topartist'){
        isset($inline['offset']) ? $week=$inline['offset']:$week='';
        $in=ahangify('https://ahangify.com/app-api/charts/artists',[
            'week'=>$week
        ]);
        $bweek=$in['prev'];
        $in=$in['chart'];
        $results=[];
        for($i=0;$i<=49;$i++){
            $cn=['M','K',''];
            $clisten=3;
            while ($clisten-- && $in[$i]['art']['track_listener_count']>=1000){
                $in[$i]['art']['track_listener_count']/=1000;
            }
            $likes=$in[$i]['art']['track_like_count'];
            $clike=3;
            while ($clike-- && $likes>=1000){
                $likes/=1000;
            }
            $cover=!empty($in[$i]['art']['thumbnail']) ? $in[$i]['art']['thumbnail']:$music_picture;
            $listen2=round($in[$i]['art']['track_listener_count'],1).' '.$cn[$clisten];
            $likes=round($likes,1).$cn[$clike];
            $results[]=[
                'id'=>"$i",
                'title'=>$in[$i]['art']['name'],
                'type'=>'article',
                'input_message_content'=>['message_text'=>'/ar'.$in[$i]['art']['id']],
                'description'=>"🎵".$in[$i]['art']['track_count']." 🎧$listen2 ❤️$likes",
                'thumb_url'=>$cover
            ];
        }
        if($inline['offset']<=1 && !empty($inline['offset'])){
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'results' => json_encode($results)
            ]);
        }else {
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'next_offset' =>"$bweek",
                'results' => json_encode($results)
            ]);
        }
    }elseif(preg_match('/^share;([\S]+)$/',$query,$m)){
        bot('answerInlineQuery',[
            'inline_query_id'=>$in_id,
            'results'=>json_encode([
                [
                    'id'=>'1',
                    'type'=>'audio',
                    'audio_file_id'=>$m[1],
                    'caption'=>"<a href=\"https://t.me/$userbot\">جستجوی موزیک 🎧 </a>",
                    'parse_mode'=>'html'
                ]
            ])
        ]);
    }elseif(preg_match('/^comment;([\S]+)$/',$query,$m)){
        if(preg_match('/^([0-9]+);([0-9]+)$/',$inline['offset'],$m2)){
            $search=ahangify("https://ahangify.com/app-api/comments/$m[1]",[
                'from'=>$m2[1],
                'from_like'=>$m2[2]
            ]);
        }else {
            $search = ahangify("https://ahangify.com/app-api/comments/$m[1]");
        }
        if($search['data']==[]){
            bot('answerInlineQuery',[
                'inline_query_id'=>$in_id,
                'results'=>json_encode([
                    [
                        'id'=>'1',
                        'type'=>'article',
                        'title'=>'نتیجه ای یافت نشد.',
                        'input_message_content'=>['message_text'=>'/start']
                    ]
                ])
            ]);
        }else{
            $results=[];
            foreach ($search['data'] as $k=>$v){
                if($k>14){break;}
                $pic=!empty($v['user']['thumbnail']) ? $v['user']['thumbnail']:$music_picture;
                $cn=['M','K',''];
                $cn2=3;
                while ($cn2-- && $v['like_count']>=1000){
                    $v['like_count']/=1000;
                }
                $like=round($v['like_count'],1).$cn[$cn2];
                $results[]=[
                  'id'=>"$k",
                  'type'=>'article',
                  'title'=>$v['user']['first_name'],
                  'input_message_content'=>['message_text'=>mb_substr($v['text'],0,4080)."...\n#ignore"],
                  'thumb_url'=>$pic,
                  'description'=>mb_substr($v['text'],0,116).'...'
                ];
            }
        }
        if($search['hasMore']){
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'next_offset' => $search['data'][14]['id'].';'.$search['data'][14]['like_count'],
                'results' => json_encode($results)
            ]);
        }else{
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'switch_pm_text' => 'کامنت های اخیر',
                'switch_pm_parameter' => $m[1],
                'results' => json_encode($results)
            ]);
        }
    }elseif(preg_match('/^as;([\S]+)$/',$query,$m)){
        $search=ahangify('https://ahangify.com/app-api/search',[
            'value'=>$query
        ]);
        $results=[];
        foreach ($search['tracks'] as $k=>$v){
            if($k>49){break;}
            if($v['size']>20971520){continue;}
            $cover=!empty($v['cover']) ? $v['cover']:$music_picture;
            $title=!empty($v['title']) ? htmlspecialchars($v['title']):'بدون عنوان';
            $performer=htmlspecialchars($v['performer']);
            $size=['گیگابایت','مگابایت','کیلوبایت','بایت'];
            $csize=4;
            while ($csize-- && $v['size']>=1024){
                $v['size']/=1024;
            }
            $duration=gmdate('i:s',$v['duration']);
            $size2=round($v['size'],1).' '.$size[$csize];
            $results[]=[
                'id'=>"$k",
                'title'=>$title,
                'type'=>'article',
                'input_message_content'=>['message_text'=>'/d'.$v['id']],
                'description'=>$v['performer']."\n🕒 $duration   💾 $size2",
                'thumb_url'=>$cover,
            ];
        }
        bot('answerInlineQuery',[
            'inline_query_id'=>$in_id,
            'results'=>json_encode($results)
        ]);
    }elseif(preg_match('/^op;([a-zA-Z0-9]+)$/',$query,$m)){
        $cn=3;
        $cn2=['M','K',''];
        $in=ahangify('https://ahangify.com/app-api/tracks/'.$m[1]);
        $artist=$in['track']['artist_roles'][0]['artist']['name'];
        $album_id=$in['album']['id'];
        $album=$in['album']['name'];
        $a_lcount=$in['album']['track_like_count'];
        while($cn-- && $a_lcount>=1000){
            $a_lcount/=1000;
        }
        $a_lcount=round($a_lcount,1).$cn2[$cn];
        $al_listen=$in['album']['track_listener_count'];
        $cn=3;
        while($cn-- && $al_listen>=1000){
            $al_listen/=1000;
        }
        $al_listen=round($al_listen,1).$cn2[$cn];
        $al_pic=!empty($in['album']['thumbnail']) ? $in['album']['thumbnail']:$music_picture;
        $artist_id=$in['track']['artist_roles'][0]['artist']['id'];
        $tcount=$in['track']['artist_roles'][0]['artist']['track_count'];
        $cn=3;
        while($cn-- && $tcount>=1000){
            $tcount/=1000;
        }
        $tcount=round($tcount,1).$cn2[$cn];
        $cn=3;
        $tlisten=$in['track']['artist_roles'][0]['artist']['track_listener_count'];
        while($cn-- && $tlisten>=1000){
            $tlisten/=1000;
        }
        $tlisten=round($tlisten,1).$cn2[$cn];
        $pic=!empty($in['track']['artist_roles'][0]['artist']['thumbnail']) ? $in['track']['artist_roles'][0]['artist']['thumbnail']:$music_picture;
        $cover=!empty($in['track']['cover']) ? $in['track']['cover']:$music_picture;
        if(!empty($artist_id)) {
            $results = [
                [
                    'id' => '1',
                    'type' => 'article',
                    'title' => 'هنرمند:',
                    'description' => "$artist\n🎵$tcount 🎧 $tlisten",
                    'input_message_content' => ['message_text' => "/ar$artist_id"],
                    'thumb_url' => $pic
                ],
                [
                    'id' => '2',
                    'type' => 'photo',
                    'title' => 'کاور:',
                    'photo_url' => $cover,
                    'thumb_url' => $cover
                ]
            ];
        }
        if(!empty($album_id)){
            $results[]=[
                'id'=>'3',
                'type'=>'article',
                'title'=>'آلبوم:',
                'description'=>"$artist\n❤️$a_lcount 🎧 $al_listen",
                'input_message_content'=>['message_text'=>"/al$album_id"],
                'thumb_url'=>$al_pic
            ];
        }
        empty($results) ? $results=[['id'=>'1','type'=>'article','title'=>'نتیجه ای یافت نشد.','input_message_content'=>['message_text'=>'/start']]]:0;
        bot('answerInlineQuery',[
            'inline_query_id'=>$in_id,
            'switch_pm_text'=>'🎵'.$in['track']['title'],
            'switch_pm_parameter'=>$in['track']['id'],
            'results'=>json_encode($results)
        ]);
    }elseif($query=='mylist'){
        if(is_file("$in_from_id.txt")) {
            $ids = file("$in_from_id.txt");
            $results = [];
            foreach ($ids as $k => $v) {
                $results[] = [
                    'id' => "$k",
                    'type' => 'audio',
                    'audio_file_id' => str_replace("\n", '', $v)
                ];
            }
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'results' => json_encode($results)
            ]);
        }else{
            bot('answerInlineQuery', [
                'inline_query_id' => $in_id,
                'results' => json_encode([
                    [
                        'id'=>'1',
                        'type'=>'article',
                        'title'=>'نتیجه ای یافت نشد.',
                        'input_message_content'=>['message_text'=>'/start']
                    ]
                ])
            ]);
        }
    }else{
        $in=ahangify('https://ahangify.com/app-api/search',[
            'value'=>str_replace(' ','+',$query)
        ]);
        if(!empty($in['tracks'])){
            $results=[];
            foreach($in['tracks'] as $k=>$v) {
                if($k>49){break;}
                if($v['size']>20971520){continue;}
                $cover=!empty($v['cover']) ? $v['cover']:$music_picture;
                $duration=gmdate('i:s',$v['duration']);
                $title=!empty($v['title']) ? $v['title']:'بدون عنوان';
                $size=['گیگابایت','مگابایت','کیلوبایت','بایت'];
                $csize=4;
                while ($csize-- && $v['size']>=1024){
                    $v['size']/=1024;
                }
                $size2=round($v['size'],1).' '.$size[$csize];
                $results[]=[
                    'id'=>"$k",
                    'title'=>$title,
                    'type'=>'article',
                    'input_message_content'=>['message_text'=>'🎵'.$v['title']."\n🗣".$v['performer']."\n\n🕒 $duration   💾 $size2"],
                    'description'=>$v['performer']."\n🕒 $duration   💾 $size2",
                    'thumb_url'=>$cover,
                    'reply_markup'=>['inline_keyboard'=>[[['text'=>'📥 دانلود آهنگ','url'=>"https://t.me/$userbot?start=".$v['id']]]]]
                ];
                
            }
            $r=bot('answerInlineQuery',[
                'inline_query_id'=>$in_id,
                'results'=>json_encode($results)
            ]);
        }else{
            bot('answerInlineQuery',[
                'inline_query_id'=>$in_id,
                'results'=>json_encode([
                    [
                        'id'=>'1',
                        'type'=>'article',
                        'title'=>'نتیجه ای یافت نشد.',
                        'input_message_content'=>['message_text'=>'/start']
                    ]
                ])
            ]);
        }
    }
    $conn->close();
}
/*
کانال شهر سورس مرجع انواع سورس کد های مختلف
بانک انواع سورس کد های مختلف به صورت کاملا تست شده
هر روز کلی سورس کد و اسکریپت منتظر شماست !

@ShahreSource
https://t.me/ShahreSource
*/
