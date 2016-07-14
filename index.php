<?php
ob_start();
define('API_KEY','XXXX');
$the_admin_id = 0;

file_put_contents("count",file_get_contents("count")+1);

$ad_btn='آموزش های رایگان برنامه نویسی';
$ad_url='http://telegram.me/cplearn';
$ad_text = "1⃣ در تلگـ ـلیست، کانال خودتون رو به رایگان در مرجع کانالهای تلگرام فارسی قرار بدید، به رايگان تبليغ و عضوگيرى كنيد.
<a href='http://www.teleglist.com/?bot'>به رایگان در تلگـ ـلیست کانال خود را ثبت کنید</a>
➖➖➖➖➖➖
ارایه انواع آموزش برنامه نویسی و طراحی وب...برای کسب اطالاعات بیشتر در کانال سایت عضو بشید.";

function getUserLocation($uid,$default){
    $cached = apc_fetch('location-'.$uid);
    return $cached?$cached:$default;
}

function getUserStep($uid,$default){
    $cached = apc_fetch('step-'.$uid);
    return $cached?$cached:$default;
}

function setUserStep($uid,$step){
    apc_store('step-'.$uid,$step,60*60*12);
}

function setUserLocation($uid,$location){
    apc_store('location-'.$uid,$location,60*60*12);
}

function check_has_string($what,$base){
    return str_replace($what,"",$base) != $base;
}

function is_valid_url($url){
    preg_match("'^https://telegram.me/joinchat/[A-Za-z-_0-9]+'si",$url,$m1);
    preg_match("'^http://telegram.me/joinchat/[A-Za-z-_0-9]+'si",$url,$m2);
    return (count($m1)>0 || count($m2) > 0);
}


function is_url($uri){
    if(preg_match( '/^(http|https):\\/\\/[a-z0-9]+([\\-\\.]{1}[a-z0-9]+)*\\.[a-zآ-ی]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' ,$uri)){
        return $uri;
    }
    else{
        return false;
    }
}



function is_valid_url_international($url){
    return is_url($url);
}

class ListNoVia{

    static function saveListCode($userid,$code){
        apc_store('novia_list_'.$userid,$code,60*60*12);
    }

    static function getListCode($userid){
        return apc_fetch('novia_list_'.$userid);
    }

    static function saveChannelID($userid,$code){
        apc_store('novia_chid_'.$userid,$code,60*60*12);
    }

    static function getChannelID($userid){
        return apc_fetch('novia_chid_'.$userid);
    }

}

class ListTab{
    static function makeMakeID(){
        return md5(time().rand(100000,999999));
    }

    static function setCurrentMakeID($uid,$mid){
        apc_store('make_list_id-'.$uid,$mid,60*60*12);
    }

    static  function getCurrentMakeID($uid){
        return apc_fetch('make_list_id-'.$uid);
    }

    static function updateDB($mid,$newUpdate){
        $file = __DIR__.'/tabs/list-'.$mid.'.json';
        file_put_contents($file,json_encode($newUpdate));
    }

    static function getMakeDB($mid,$o = true){
        $file = __DIR__.'/tabs/list-'.$mid.'.json';
        if(file_exists($file)){
            return json_decode(file_get_contents($file));
        }else{
            if($o)  file_put_contents($file,'{}');
            return json_decode('{}');
        }
    }
}

function makeMakeID(){
    return md5(time().rand(100000,9999999));
}

function setCurrentMakeID($uid,$mid){
    apc_store('make_id-'.$uid,$mid,60*60*12);
}

function getCurrentMakeID($uid){
    return apc_fetch('make_id-'.$uid);
}

function updateDB($mid,$newUpdate){
    $file = __DIR__.'/tabs/tab-'.$mid.'.json';
    file_put_contents($file,json_encode($newUpdate));
}

function getMakeDB($mid,$o = true){
    $file = __DIR__.'/tabs/tab-'.$mid.'.json';
    if(file_exists($file)){
        return json_decode(file_get_contents($file));
    }else{
        if($o)  file_put_contents($file,'{}');
        return json_decode('{}');
    }
}


function makeHTTPRequest($method,$datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($datas));
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}

function makeHTTPRequestAPI($method,$datas=[],$API){
    $url = "https://api.telegram.org/bot".$API."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($datas));
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}




// Fetching UPDATE
$update = json_decode(file_get_contents('php://input'));

var_dump($update);


if(isset($update->callback_query)){

}elseif(isset($update->inline_query)){
$userID = $update->inline_query->from->id;    
echo 'QUERY ...';
    $theQuery = $update->inline_query->query;
    if(str_replace('getlist-','',$theQuery) != $theQuery ){
        $theListId = trim(str_replace('getlist-','',$theQuery));
        $DB = ListTab::getMakeDB($theListId,false);
        if(isset($DB->count) && $DB->count > 0){
            $btns = [];
            foreach($DB->list as $button){
                $button->url =
                    str_replace("\n","",trim($button->url," \t\n\r\0\x0B."));
                array_push($btns,[(array)$button]);
            }
            var_dump( $DB->list);
            var_dump($btns);
            var_dump(makeHTTPRequest('answerInlineQuery', [
                'inline_query_id' => $update->inline_query->id,
                'cache_time'=>1,
                'results' => json_encode([[
                    'type' => 'article',
                    'id' => base64_encode(1),
                    'title' => 'برای نمایش لیست کلیک کنید',
                    'input_message_content' => ['parse_mode' => 'HTML', 'message_text' => $DB->text],
                    'reply_markup' => [
                        'inline_keyboard' => $btns                   ]
                ],
                    [
                        'type'=>'article',
                        'id'=>base64_encode(rand(5,555)),
                        'title'=>'برای ارسال کد کلیک کنید',
                        'input_message_content'=>[
                            'message_text'=>'@sscapachebot getlist-'.$theListId
                        ]
                    ]])
            ]));
        }else{
            var_dump(makeHTTPRequest('answerInlineQuery', [
                'inline_query_id' => $update->inline_query->id,
                'results' => json_encode([]),
                'switch_pm_text'=>'لیست یافت نشد',
                'switch_pm_parameter'=>'newlist'
            ]));
        }

    }elseif(str_replace('getbanner-','',$theQuery) != $theQuery ){
        $theTabId = trim(str_replace('getbanner-','',$theQuery));
        $DB = getMakeDB($theTabId,false);
        if(isset($DB->list) || isset($DB->count)){
            $btns = [];
            foreach($DB->list as $button) {
		$button->text=$button->text;
                $button->url =
                    str_replace(["\n","‌"," "],["", "",""], trim($button->url));
                array_push($btns, [(array)$button]);
            }


            var_dump(makeHTTPRequest('answerInlineQuery', [
                'inline_query_id' => $update->inline_query->id,
                'cache_time' => 1,
                'results' => json_encode([[
                    'type' => 'photo',
                    'id' => base64_encode(rand(300, 400)),
                    'photo_file_id' => $DB->f->image_id,
                    'caption' =>  $DB->f->text,
                    'reply_markup' => [
                        'inline_keyboard' => $btns
                    ]
                ]
//
                ])
            ]));
            if($update->inline_query->from->id == $the_admin_id){
                var_dump($DB);
                file_put_contents('ola2',ob_get_clean());
            }
        }else {
            file_put_contents('me',ob_get_clean());
            if (isset($DB->done)) {
                if (str_replace('developer', '', $DB->f->image_id) != $DB->f->image_id) {
                    var_dump(makeHTTPRequest('answerInlineQuery', [
                        'inline_query_id' => $update->inline_query->id,
                        'cache_time' => 1,
                        'results' => json_encode([
                            [
                                'type' => 'photo',
                                'id' => base64_encode(3),
                                'photo_url' => $DB->f->image_id,
                                'thumb_url' => $DB->f->image_id
                                , 'reply_markup' => ['inline_keyboard' => [
                                [
                                    ['text' => 'برای مشاهده کلیک کنید', 'url' => $DB->f->join]
                                ]]
                            ]
                            ]
                        ]),
                    ]));

                    file_put_contents('me', ob_get_clean());
                } else {
                    var_dump(makeHTTPRequest('answerInlineQuery', [
                        'inline_query_id' => $update->inline_query->id,
                        'cache_time' => 1,
                        'results' => json_encode([[
                            'type' => 'photo',
                            'id' => base64_encode(rand(300, 400)),
                            'photo_file_id' => $DB->f->image_id,
                            'caption' => $DB->f->text,
                            'reply_markup' => ['inline_keyboard' => [
                                [
                                    ['text' => 'برای مشاهده کلیک کنید', 'url' => $DB->f->join]
                                ]]
                            ]
                        ],
//                    [
//                        'type' => 'photo',
//                        'id' => base64_encode(rand(100,500)),
//                        'title' => $DB->s->chid,
//                        'photo_url' => $DB->s->image_id,
//                        'thumb_url' => $DB->s->image_id,
//                        'caption'=>$DB->s->text,
//                        'reply_markup'=>[ 'inline_keyboard'=>[
//                            [
//                                ['text'=>'برای عضویت کلیک کنید','url'=>$DB->s->join]
//                            ]]
//                        ]
//                    ]
                        ])
                    ]));
                }

            } else {

                var_dump(makeHTTPRequest('answerInlineQuery', [
                    'inline_query_id' => $update->inline_query->id,
                    'results' => json_encode([])
                ]));
            }
        }
    }elseif($theQuery == 'تبادلات') {

    }else{
        var_dump(makeHTTPRequest('answerInlineQuery', [
            'inline_query_id' => $update->inline_query->id,
            'results' => json_encode([]),
		'switch_pm_text'=>'ستخن جدید',
'switch_pm_parameter'=>'new'
        ]));
    }

if( $update->inline_query->from->id  == $the_admin_id){
file_put_contents('ola',ob_get_clean());
}

}else{
    var_dump([
        getUserStep($userID,false),getUserLocation($usgerID,false)
    ]);
    $userID = $update->message->from->id;

    $file_o = __DIR__.'/users/'.$userID.'.json';
    file_put_contents($file_o,json_encode($update->message->from));

    $userTEXT = isset($update->message->text)?$update->message->text:'';
    $currentLocation = getUserLocation($userID,'home');
    $currentStep = getUserStep($userID,1);
    var_dump([
        'Location'=>$currentLocation,
        'Step'=>$currentStep
    ]);
    //analysing the message

    if($userTEXT == '/newtab'){
        setUserLocation($userID,'make');
        setUserStep($userID,1);
    }

    if($userTEXT == '/convert'){
        setUserLocation($userID,'convert');
        setUserStep($userID,1);
    }

    if($userTEXT == '/submit'){

        if(getUserLocation($userID,'home') == 'make'){
            if(getUserStep($userID,'1') == '4'){
                setUserStep($userID,'6');
            }else{
                makeHTTPRequest('sendMessage',[
                    'chat_id'=>$userID,
                    'text'=>"❗️ عه !‌ شما که هنوز لیستتون رو کامل نکردید ! \nبرای انصراف روی /cancel کلیک کن"
                ]);
                die;
            }
        }
        elseif(
        in_array(getUserLocation($userID,'home'),['list','convert'])){
            if(getUserStep($userID,'1') == '3'){
                setUserStep($userID,'5');
            }else{
                makeHTTPRequest('sendMessage',[
                    'chat_id'=>$userID,
                    'text'=>"❗️ عه !‌ شما که هنوز لیستتون رو کامل نکردید ! \nبرای انصراف روی /cancel کلیک کن"
                ]);
                die;
            }
        }

    }

    if($userTEXT == '/newlist'){
        setUserLocation($userID,'list');
        setUserStep($userID,'1');
    }

    if($userTEXT == '/cancel'){
        setUserLocation($userID,'home');
        setUserStep($userID,1);
    }

    if($userTEXT == "/help"){
        $helpTXT = "🌐      به راهنمای ربات خوش آمدید  👍
➖➖➖➖➖➖➖

آموزش کامل ویدیویی :
https://telegram.me/mhrdev/834

این ربات به شما این امکان را می دهد که برای کانال های خود یک بنر با عکس و دکمه شیشه ایی پیاده کنید.

🌐🌐‼️ آپدیت جدید هم پیاده شد ، می توانید لیست تبادل با دکمه شیشه ایی پیاده کنید
توضیحات آخر آموزش

برای استفاده از ربات شما باید ابتدا بر روی /newtab کلیک کنید .
سپس ربات از شما یک سری سوال می پرسد و شما باید اطلاعات کانالتان را وارد کنید.

پس از اتمام کار ربات به شما یک کد مانند کد زیر می دهد .👇👇

<pre>@sscapachebot d6cdbea45b238632bdd6d11dcf7fe98f </pre>

این کد را کپی کند و هر وقت خواستید بنر را نمایش دهید ( یا در تبادل ها به ادمین کانال دیگر بدهید ) از این کد استفاده کنید .

استفاده از کد هم به این شکل هست که در فیلد مربوط به چت آن را پیست می کنید ( ربات اینلاین هست و مانند همه ی ربات های اینلاین‌)‌و منتظر می مانید تا بنر شما لود شود. سپس روی آن کلیک کنید

تصویر زیر یک مثال است :‌👇👇👇👇

»» آموزش پیاده کردن لیست تبادل

ابتدا کامند /newlist را بزنید .

ربات از شما متن لیست را می خواهید ‌، زیر این متن دکمه ها قرار خواهند گرفت برای مثال ( جدید ترین کانال های فناوری اطلاعات ... )

خوب حالا به ترتیب عنوان و لینک کانال ها را به ربات بدهید .

هر وقت لینک آخرین دکمه را وارد کردید بر روی /submit کلیک کنید تا لیست شما آماده شود .

» آموزش تبدیل لیست تبادل به لیست شیشه ایی

در مرحله ی اول متن تبادل ( متنی که لیست زیر آن می آید ) را به ربات میدهد . حالا باید لیست دکمه ها را بدهیم.

برای این کار ابتدا باید خودتان یک لیست به شکل زیر بسازید :


متن دکمه اول
https://telegram.me/joinchat/linke_aval
متن دکمه دوم
https://telegram.me/joinchat/linke_dovom
...

سپس ربات به شما کد می دهد .

کد ایی که ربات به شما می دهد را مانند تصویر زیر استفاده کنید .";
        $imageID = 'AgADBAADQ6oxG_sS0QWIeBEDRy1gg9sVQxkABPxnx4JtVvzC6j4CAAEC';
        makeHTTPRequest('sendMessage',[
            'chat_id'=>$userID,
            'text'=>$helpTXT,
            'parse_mode'=>'HTML'
        ]);
        makeHTTPRequest('sendPhoto',[
            'chat_id'=>$userID,
            'photo'=>$imageID,
            'caption'=>'ابتدا صبر کنید تا لیست لود شود.'
        ]);
        die;
    }

    switch($currentLocation){
        case 'home':{

        }
    }

    if($userTEXT == '/novia'){
        setUserLocation($userID,'novia');
        setUserStep($userID,'1');
    }

    $currentLocation = getUserLocation($userID,'home');
    $currentStep = getUserStep($userID,'1');

    $cancel_Text = "\n➖➖➖➖➖➖➖➖➖
برای انصراف روی /cancel کلیک کنید";
    //action
    switch($currentLocation){

        case 'novia':{

            switch($currentStep){

                case '1':{
                    makeHTTPRequest('sendMessage',[
                        'text'=>"🌐 به بخش No Via خوش آمدید .
➖➖➖➖➖➖

قبل از هر کاری اول باید بدانید این via sscapachebot چیست !
ربات ما نیازی ندارد ادمین کانال ، گروه یا هر چیزی باشد تا بنر را ارسال کنید . ما ار طریق Inline این کار ار انحام می دهیم.

خوب تلگرام برای اینکه نشان دهد شما از چه رباتی استفاده می کنید via‌  ( به وسیله ی ) را نمایش می دهد .

شما باید یک ربات در @botfather ثبت نمایید و بات فادر یک توکن به شما می دهد .

این توکن را  ‌ذخیره کنید . سپس ربات را ادمین کانالتان کنید ( رباتی که خودتان ساختید )

کد های اینلاین مانند :
<code>@sscapachebot getlist-XXXXX </code>

👈  حالا برای من کد inline تان را بفرستید : ( فعلا فقط لیست ها قبول هستند )".$cancel_Text,
                        'parse_mode'=>"HTML",
                        'chat_id'=>$userID
                    ]);
                    setUserStep($userID,'2');
                }break;

                case '2':{
                    $code = $update->message->text;
                    if(str_replace('@sscapachebot getlist-','',$code) != $code){
                        $code_list = trim(str_replace('@sscapachebot getlist-','',$code));
                        $DB = getMakeDB($code_list,false);
                        if(isset($DB->done)){
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>"‼️ ساختار کد شما بنظر درست می آمد ولی در دیتابیس ثبت نشده بود .

لطفا یک لیست جدید با /newlist بسازید .".$cancel_Text,
                                'parse_mode'=>'HTML'
                            ]);
                        }else{
                            ListNoVia::saveListCode($userID,$code_list);
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>"✅ کد شما درست تشخیص داده شد .

حالا باید شناسه کانالتان را بدهید .

توجه کنید که حتما ربات را ادمین کانالتان کرده باشید و شناسه را با @ ارسال کنید .

برای مثال @mhrdev .

این بخش اجباری هست .".$cancel_Text,
                                'parse_mode'=>'HTML'
                            ]);
                            setUserStep($userID,'3');
                        }
                    }else{
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"❗️ کد شما غیر قابل قبول است .

کد شما باید به شکل زیر باشد :
<code>@sscapachebot getlist-XXXXX </code>

توجه فرمایید فقط کد های List ها قبول هستند .".$cancel_Text,
                            'parse_mode'=>'HTML'
                        ]);
                    }
                }break;

                case '3':{
                    $m = '✅ شناسه

حالا توکن رباتتان را در @botfather بدهید .

توکن ها به شکل
<code>1111111:AAAAA...</code>
هستند.';
                    $channel_id = $update->message->text;
                    if(str_replace('@','',$channel_id) != $channel_id){
                        ListNoVia::saveChannelID($userID,strtolower(trim($channel_id)));
                        setUserStep($userID,'4');
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>$m.$cancel_Text,
                            'parse_mode'=>'HTML'
                        ]);
                    }else{
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"❗️شناسه کانال اشتباه است .

شناسه باید @ داشته باشد.".$cancel_Text
                        ]);
                    }
                }break;

                case '4':{
                    $botToken = $update->message->text;
                    if(str_replace(':','',$botToken) != $botToken){
                        $list_id = ListNoVia::getListCode($userID);
                        $DB = ListTab::getMakeDB($list_id,false);
                        var_dump($DB);
                        var_dump($ListID);
                        if(isset($DB->count) && $DB->count > 0) {
                            echo "YEA/\n";
                            $btns = [];
                            foreach ($DB->list as $button) {
                                $button->url =
                                    str_replace("\n", "", trim($button->url));
                                array_push($btns, [(array)$button]);
                            }
                            $result = makeHTTPRequestAPI('sendMessage',[
                                'text'=>$DB->text,
                                'chat_id'=>ListNoVia::getChannelID($userID),
                                'parse_mode'=>'HTML',
                                'reply_markup'=>json_encode([
                                    'inline_keyboard'=>$btns
                                ])
                            ],$botToken);
                            var_dump($result);
                            if($result->ok == true){
                                makeHTTPRequest('sendMessage',[
                                    'text'=>"✅ پیام شما با موفقیت در $channel_id ارسال شد .",
                                    'chat_id'=>$userID
                                ]);
                                makeHTTPRequest('sendMessage',[
                                    'chat_id'=>$update->message->from->id,
                                    'text'=>$ad_text."\n<a href='{$ad_url}'>{$ad_btn}</a>",
                                    'parse_mode'=>"HTML",
                                    'disable_web_page_preview'=>true
                                ]);
                            }else{
                                makeHTTPRequest('sendMessage',[
                                    'text'=>"❗️متاسفانه پیام ارسال نشد .

می تواند یکی از دلایل زیر موثر باشند .

۱. شناسه ی کانال اشتباه باشد
۲. شما در ارسال api اشتباهی کرده باشید.
۳. ربات ادمین کانال نمی باشد.
".$cancel_Text,
                                    'chat_id'=>$userID
                                ]);
                            }
                        }else{
                            echo "NOA\n";
                            var_dump(makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>"لیست شما مشکل دارد \nلطفا با /newlist لیست جدیدی پیاده کنید."
                            ]));
                            setUserLocation($userID,'home');
                        }

                    }else{
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"❗️ توکن ربات شما بنظر اشتباه می آید .
توکن ها به شکل
<code>1111111:AAAAA...</code>
هستند.",
                            'parse_mode'=>'HTML'
                        ]);
                    }

                    setUserStep($userID,'1');
                    setUserLocation($userID,'home');
                }break;


            }


        }break;


        case 'convert':{

            switch($currentStep){

                case '1':{
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$userID,
                        'text'=>"به بخش تبدیل لیست خوش آمدید .\nاول ما از شما متن را می خواهیم . این همان متنی هست که لیست زیر آن قرار می گیرد.".$cancel_Text
                    ]);
                    setUserStep($userID,'2');
                    ListTab::setCurrentMakeID($userID,ListTab::makeMakeID());
                    $ListID = ListTab::getCurrentMakeID($userID);
                    $DB = ListTab::getMakeDB($listID);
                    $DB->list = json_decode('[]');
                    $DB->count=0;
                    ListTab::updateDB($listID,$DB);
                }break;

                case '2':{
                    $text =$update->message->text;
                    if(mb_strlen($text) < 5){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'متن باید بیش از ۳ کاراکتر باشد'.$cancel_Text
                        ]);
                    }else {
                        $ListID = ListTab::getCurrentMakeID($userID);
                        $DB = ListTab::getMakeDB($ListID);

                        $DB->text = $text;
                        $DB->list = 'n';
                        ListTab::updateDB($ListID, $DB);
                        var_dump('The DATABASE');
                        var_dump($DB);

                        makeHTTPRequest('sendMessage', [
                            'chat_id' => $userID,
                            'text' => '✅ متن لیست ثبت شد.

حالا باید یک لیست به شکل زیر برای ما ارسال کنید :

متن دکمه اول
https://telegram.me/joinchat/linke_aval
متن دکمه دوم
https://telegram.me/joinchat/linke_dovom


به ترتیب در هر خط جداگانه متن دکمه و لینک دکمه رو ارسال کنید :
' . $cancel_Text
                        ]);
                        setUserStep($userID, '3');
                    }

                }break;


                case '3':{
                    $list = $update->message->text;
                    $list = preg_replace("/(\n)+/","\n",trim($list));
                    $explode = explode("\n",$list);
                    $nList = "متاسفانه لیست ارسالی شما قابل قبول نیست.

لطفا توجه بفرمایید که لیست را به ترتیب ، اول عنوان و بعد لینک بچینید .
هر کدام باید در یک خط باشد.

 یعنی خط اول متن دکمه اول ، خط دوم لینک دکمه اول . خط سوم متن دکمه دوم و خط چهارم لینک دکمه دوم باشد ...

برای شما یک لیست نمونه ارسال کردیم.

                            ".$cancel_Text;
                    if(count($explode) % 2 != 0){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>$nList
                        ]);
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"آموزش های رایگان برنامه نویسی
https://telegram.me/mhrdev
آموزش توسعه ربات تلگرام
https://telegram.me/tbdchannel
به ربات ما سر بزنید
https://telegram.me/sscapachebot
                            "
                        ]);
                    }else{
                        $newList = [];
                        $valid_url = true;
                        foreach($explode as $key=>$title){
                            if($key % 2 == 0){
                                $newList[($key/2)]=["text"=>trim($title)];
                            }else{
                                echo $key."\n";
                            }
                        }
                        $i = 0;
                        foreach($explode as $key=>$url){
                            if($key % 2 == 1){
                                $valid_url = is_valid_url_international(str_replace(' ','',trim($url)));
                                $newList[$i]['url'] = str_replace(' ','',trim($url));
                                $i++;
                            }
                        }

                        if($valid_url){
                            var_dump($newList);
                            $ListID = ListTab::getCurrentMakeID($userID);
                            $DB = ListTab::getMakeDB($ListID);
                            var_dump($ListID);
                            $DB->list = $newList;
                            $DB->count = count($newList);
                            ListTab::updateDB($ListID, $DB);
                            $userID = $update->message->from->id;
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$update->message->from->id,
                                'text'=>'تبریک  👍 لیست شما آماده شد

خوب من یک کد بهت دادم ، اون کد بهش میگن کد inline که ربات های inline ( مثل من !‌) ساپورت میکنن.

اصلا لازم نیست من رو ادمین کانالت کنی .

این کد پیست کن ( ولی ارسال نکن ) منتظر باش یک لیست اون بالا برات باز میشه .
وقتی لود شد روش کلیک کن.

اگر بد توضیح دادم روی /help کلیک کن راهنمایی مفصل داره !'
                            ]);

                            $theCommand = '@sscapachebot getlist-'.ListTab::getCurrentMakeID($update->message->from->id);
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$update->message->from->id,
                                'text'=>$theCommand,
                                'reply_markup'=>json_encode([
                                    'inline_keyboard'=>[
                                        [
                                            ['text'=>'ارسال به چت مد نظر','url'=>'https://telegram.me/share/url?url='.str_replace(' ','%20',$theCommand)]
                                        ]
                                    ]
                                ])
                            ]);
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$update->message->from->id,
                                'text'=>$ad_text."\n<a href='{$ad_url}'>{$ad_btn}</a>",
                                'parse_mode'=>"HTML",
                                'disable_web_page_preview'=>true
                            ]);

                            setUserLocation($update->message->from->id,'home');
                            setUserStep($update->message->from->id,'3');








                        }else{
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>$nList
                            ]);
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>"آموزش های رایگان برنامه نویسی\nhttps://telegram.me/mhrdev\nآموزش توسعه ربات تلگرام\nhttps://telegram.me/tbdchannel\nبه ربات ما سر بزنید\nhttps://telegram.me/sscapachebot"
                            ]);
                        }

                    }
                }break;


            }


        }break;

        case 'list':{
            switch($currentStep){
                case '1':{
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$userID,
                        'text'=>"🌐 بخش ساخت لیست خوش آمدید 👍

لطفا ابتدا متن اصلی را وارد کنید .
لیست دکمه ها زیر این متن قرار می گیرند .".$cancel_Text
                    ]);
                    setUserStep($userID,'2');
                    ListTab::setCurrentMakeID($userID,ListTab::makeMakeID());
                    $ListID = ListTab::getCurrentMakeID($userID);
                    $DB = ListTab::getMakeDB($listID);
                    $DB->list = json_decode('[]');
                    $DB->count=0;
                    ListTab::updateDB($listID,$DB);
                }break;

                case '2':{
                    $text = $update->message->text;
                    if(mb_strlen($text) < 5){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'متن باید بیش از ۳ کاراکتر باشد'.$cancel_Text
                        ]);
                    }else{
                        $ListID = ListTab::getCurrentMakeID($userID);
                        $DB = ListTab::getMakeDB($ListID);

                        $DB->text = $text;
                        $DB->list='n';
                        ListTab::updateDB($ListID,$DB);
                        var_dump('The DATABASE');
                        var_dump($DB);

                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'✅ متن لیست ثبت شد.

حالا من از تو به ترتیب متن و لینک دکمه ها رو میگیرم .
وقتی لیست مد نظرت رو وارد کردی روی /submit کلیک کن.

اگر مشکلی پیش اومد توی لیستت باید /cancel کنی و دوباره بسازی .
❗️ دقت کن !

وقتشه که شروع کنید ، متن اولین دکمه رو بفرست :'.$cancel_Text
                        ]);
                        setUserStep($userID,'3');
                    }
                }break;

                case '3':{
                    $text = $update->message->text;
                    if(mb_strlen($text) > 100){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'❗️ متنت خیلی زیاده !‌ باید کمتر از 100 کاراکتر باشه 😊'.$cancel_Text
                        ]);
                    }elseif(mb_strlen(trim($text)) == 0){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'❗️‼️ عه !‌خوب یک چیزی بفرست 😁 اسپیس یا خالی قبول نیست '.$cancel_Text
                        ]);
                    }else{
                        var_dump('The DATABASE');
                        var_dump($DB);
                        $DB = ListTab::getMakeDB(ListTab::getCurrentMakeID($userID));
                        if($DB->list == 'n') $DB->list = [];
                        $newObject = json_decode('{}');
                        $newObject->text = $text;
                        array_push($DB->list,$newObject);
                        $DB->count++;
                        $index = count(((array) $DB->list));
                        ListTab::updateDB(ListTab::getCurrentMakeID($userID),$DB);
                        var_dump('The DATABASE');
                        var_dump($DB);
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"✅ آفرین ! متن دکمه شماره {$index} رو ثبت کردی .

حالا باید یک لینک معتبر براش ارسال کنی .

لینک می تونه هر لینکی باشه ( لینک جوین ، لینک کانال یا حتی لینک سایتت ) اما حواست باشه حتما اولش http:// یا https:// رو بذاری
از درست بودن آدرس اطمینان حاصل فرمایید.
😜".$cancel_Text
                        ]);
                        setUserStep($userID,'4');
                    }
                }break;


                case '4':{
                    $link = $update->message->text;
                    if(is_valid_url_international($link)){
                        $DB = ListTab::getMakeDB(ListTab::getCurrentMakeID($userID));
                        $list = (array) $DB->list;
                        echo "The LIST \n";
                        var_dump($list);
                        $lastID = max(array_keys($list));
                        $lastObject = end($list);
                        $lastObject->url = trim($link);
                        $list[$lastID] = $lastObject;
                        $DB->list = $list;
                        $DB->count++;
//                        var_dump($DB);
//                        $index = $DB->count;
//                        $object = end($DB->list);
//                        var_dump($object);
//                        $object->url = $link;
//                        var_dump("new link ... \n");
//                        var_dump($object);
//                        $DB->list[($index-1)] = $object;
                        $index = count($list);
                        ListTab::updateDB(ListTab::getCurrentMakeID($userID),$DB);
                        echo "THE DATABASE \n";
                        var_dump($DB);
                        setUserStep($userID,'3');
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"✅ ایول ! لینک دکمه {$index} ثبت شد .

نگا کن الان دکمه های قبلیت کامل شدن . اگر میخوای دکمه جدید بسازی متنش رو بفرست .

اما اگه دکمه هات کامل شدن روی /submit کلیک کن 😊".$cancel_Text,
                            'reply_markup'=>[
                                'keyboard'=>[
                                    [
                                        ['text'=>'/submit'],['text'=>'/cancel']
                                    ]
                                ]
                            ]
                        ]);
                    }else{
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"‼️ ربات تشخیص داده که لینکت اشتباهه !

حدس میزنم https:// یا http:// اولش نذاشتی 😜".$cancel_Text
                        ]);
                    }
                }break;

                case '5':{
                    $userID = $update->message->from->id;
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>'تبریک  👍 لیست شما آماده شد

خوب من یک کد بهت دادم ، اون کد بهش میگن کد inline که ربات های inline ( مثل من !‌) ساپورت میکنن.

اصلا لازم نیست من رو ادمین کانالت کنی .

این کد پیست کن ( ولی ارسال نکن ) منتظر باش یک لیست اون بالا برات باز میشه .
وقتی لود شد روش کلیک کن.

اگر بد توضیح دادم روی /help کلیک کن راهنمایی مفصل داره !'
                    ]);

                    $theCommand = '@sscapachebot getlist-'.ListTab::getCurrentMakeID($update->message->from->id);
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>$theCommand,
                        'reply_markup'=>json_encode([
                            'inline_keyboard'=>[
                                [
                                    ['text'=>'ارسال به چت مد نظر','url'=>'https://telegram.me/share/url?url='.str_replace(' ','%20',$theCommand)]
                                ]
                            ]
                        ])
                    ]);
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>$ad_text."\n<a href='{$ad_url}'>{$ad_btn}</a>",
                        'parse_mode'=>"HTML",
                        'disable_web_page_preview'=>true
                    ]);

                    setUserLocation($update->message->from->id,'home');
                    setUserStep($update->message->from->id,'3');
                }break;
            }
        }break;
        case 'make':{
            switch($currentStep){
                case '1':{
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$userID,
                        'text'=>"🖼  لطفا تصویر کانالتان را وارد نمایید\nجهت انصراف ، کامند /cancel را وارد کنید"
                    ]);
                    setCurrentMakeID($userID,makeMakeID());
                    setUserStep($userID,'2');
                }break;

                case '2':{
                    var_dump($update->message);
                    $DB = getMakeDB(getCurrentMakeID($userID));
                    $image_id = isset(end($update->message->photo)->file_id)?end($update->message->photo)->file_id:false;
                    if($image_id === false){
                        makeHTTPRequest('sendMessage',[
                            'text'=>"لطفا فایل jpg ارسال نمایید\nفایل اشتباه است\nجهت انصراف ، کامند /cancel را وارد کنید",
                            'chat_id'=>$userID
                        ]);
                    }else{
                        if(true) {
                            if (!isset($DB->f)) $DB->f = json_decode("{}");
                            $DB->f->image_id = $image_id;
                            updateDB(getCurrentMakeID($userID), $DB);
                            makeHTTPRequest('sendMessage', [
                                'chat_id' => $userID,
                                'text' => "⌨   لطفا متن ایی که زیر تصویر قرار میگیرد را ارسال کنید\nمتن باید کمتر از 300 کاراکتر باشد \nجهت انصراف ، کامند /cancel را وارد کنید"
                            ]);
                            setUserStep($userID, '3');
                        }else{
                            makeHTTPRequest('sendMessage', [
                                'chat_id' => $userID,
                                'text' => "لطفا فایل jpg ارسال نمایید\nفایل ارسالی شما $file_ext بوده است.\n<a href='http://image.online-convert.com/convert-to-jpg'>برای تبدیل آنلاین کلیک کنید</a>\nجهت انصراف ، کامند /cancel را وارد کنید",
                                'parse_mode'=>"HTML"
                            ]);
                        }
                    }
                }break;

                case '3':{
                    echo 'Len is '.mb_strlen($update->message->text);
                    if(mb_strlen($update->message->text) > 300){
                        makeHTTPRequest('sendMessage', [
                            'text' => "متن ارسالی شما ".mb_strlen($update->message->text)." کاراکتر است . لطفا کمتر از 300 کاراکتر بفرستید .\nجهت انصراف ، کامند /cancel را وارد کنید",
                            'chat_id' => $userID
                        ]);
                    }else {
                        $DB = getMakeDB(getCurrentMakeID($userID));
                        $DB->f->text = $userTEXT;
                        $DB->list='n';
                        updateDB(getCurrentMakeID($userID), $DB);
                        setUserStep($userID, '4');
                        makeHTTPRequest('sendMessage', [
                            'text' => "🔀   متن دکمه اول رو وارد کن\nجهت انصراف ، کامند /cancel را وارد کنید",
                            'chat_id' => $userID
                        ]);
                    }
                }break;




                case '4':{
                    $text = $update->message->text;
                    if(mb_strlen($text) > 100){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'❗️ متنت خیلی زیاده !‌ باید کمتر از 100 کاراکتر باشه 😊'.$cancel_Text
                        ]);
                    }elseif(mb_strlen(trim($text)) == 0){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'❗️‼️ عه !‌خوب یک چیزی بفرست 😁 اسپیس یا خالی قبول نیست '.$cancel_Text
                        ]);
                    }else{
                        var_dump('The DATABASE');
                        var_dump($DB);
                        $DB = getMakeDB(getCurrentMakeID($userID));
                        if($DB->list == 'n') $DB->list = [];
                        $newObject = json_decode('{}');
                        $newObject->text = $text;
                        array_push($DB->list,$newObject);
                        $DB->count++;
                        $index = count(((array) $DB->list));
                        updateDB(getCurrentMakeID($userID),$DB);
                        var_dump('The DATABASE');
                        var_dump($DB);
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"✅ آفرین ! متن دکمه شماره {$index} رو ثبت کردی .

حالا باید یک لینک معتبر براش ارسال کنی .

لینک می تونه هر لینکی باشه ( لینک جوین ، لینک کانال یا حتی لینک سایتت ) اما حواست باشه حتما اولش http:// یا https:// رو بذاری
از درست بودن آدرس اطمینان حاصل فرمایید.
😜".$cancel_Text
                        ]);
                        setUserStep($userID,'5');
                    }
                }break;


                case '5':{
                    $link = $update->message->text;
                    if(is_valid_url_international($link)){
                        $DB = getMakeDB(getCurrentMakeID($userID));
                        $list = (array) $DB->list;
                        echo "The LIST \n";
                        var_dump($list);
                        $lastID = max(array_keys($list));
                        $lastObject = end($list);
                        $lastObject->url = trim($link);
                        $list[$lastID] = $lastObject;
                        $DB->list = $list;
                        $DB->count++;
//                        var_dump($DB);
//                        $index = $DB->count;
//                        $object = end($DB->list);
//                        var_dump($object);
//                        $object->url = $link;
//                        var_dump("new link ... \n");
//                        var_dump($object);
//                        $DB->list[($index-1)] = $object;
                        $index = count($list);
                        updateDB(getCurrentMakeID($userID),$DB);
                        echo "THE DATABASE \n";
                        var_dump($DB);
                        setUserStep($userID,'4');
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"✅ ایول ! لینک دکمه {$index} ثبت شد .

نگا کن الان دکمه های قبلیت کامل شدن . اگر میخوای دکمه جدید بسازی متنش رو بفرست .

اما اگه دکمه هات کامل شدن روی /submit کلیک کن 😊".$cancel_Text,
                            'reply_markup'=>[
                                'keyboard'=>[
                                    [
                                        ['text'=>'/submit'],['text'=>'/cancel']
                                    ]
                                ]
                            ]
                        ]);
                    }else{
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"‼️ ربات تشخیص داده که لینکت اشتباهه !

حدس میزنم https:// یا http:// اولش نذاشتی 😜".$cancel_Text
                        ]);
                    }
                }break;



                case '6':{
                    $userID = $update->message->from->id;
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>'تبریک  👍 لیست شما آماده شد

خوب من یک کد بهت دادم ، اون کد بهش میگن کد inline که ربات های inline ( مثل من !‌) ساپورت میکنن.

اصلا لازم نیست من رو ادمین کانالت کنی .

این کد پیست کن ( ولی ارسال نکن ) منتظر باش یک لیست اون بالا برات باز میشه .
وقتی لود شد روش کلیک کن.

اگر بد توضیح دادم روی /help کلیک کن راهنمایی مفصل داره !'
                    ]);

                    $theCommand = '@sscapachebot getbanner-'.getCurrentMakeID($update->message->from->id);
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>$theCommand,
                        'reply_markup'=>json_encode([
                            'inline_keyboard'=>[
                                [
                                    ['text'=>'ارسال به چت مد نظر','url'=>'https://telegram.me/share/url?url='.str_replace(' ','%20',$theCommand)]
                                ]
                            ]
                        ])
                    ]);
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>$ad_text."\n<a href='{$ad_url}'>{$ad_btn}</a>",
                        'parse_mode'=>"HTML",
                        'disable_web_page_preview'=>true
                    ]);
                    setUserLocation($update->message->from->id,'home');
                    setUserStep($update->message->from->id,'3');
                }break;



























//                case '4':{
//                    if(is_valid_url_international($userTEXT)) {
//                        $DB = getMakeDB(getCurrentMakeID($userID));
//                        $DB->f->join = $userTEXT;
//                        updateDB(getCurrentMakeID($userID), $DB);
//                        setUserStep($userID, '5');
//                        makeHTTPRequest('sendMessage', [
//                            'text' => "🌐   شناسه کانال را بدهید \nشناسه ها مانند @mhrdev می باشند\n\nاگر کانال شناسه ندارد کامند /skipstep را بزنید\nجهت انصراف ، کامند /cancel را وارد کنید",
//                            'chat_id' => $userID
//                        ]);
//                    }else{
//                        makeHTTPRequest('sendMessage', [
//                            'text' => "لینک ارسالی معتبر نیست . باید http:// یا https:// داشته باشد\nجهت انصراف ، کامند /cancel را وارد کنید",
//                            'chat_id' => $userID
//                        ]);
//                    }
//                }break;
//
//                case '5':{
//                    $DB = getMakeDB(getCurrentMakeID($userID));
//                    $DB->f->chid = "@mhrdev";
//                    $DB->done = true;
//                    updateDB(getCurrentMakeID($userID),$DB);
//                    setUserStep($userID,'1');
//                    setUserLocation($userID,'home');
//                    makeHTTPRequest('sendMessage',[
//                        'text'=>"✅  بنر شما با موفقیت ثبت شد\nکد مربوط به بنر خود را کپی کنید و هر وقت خواستید استفاده کنید\n\nاگر نمی دانید با این کد چی کار کنید کامند /help را وارد کنید",
//                        'chat_id'=>$userID
//                    ]);
//                    makeHTTPRequest('sendMessage',[
//                        'text'=>'@sscapachebot getbanner-'.getCurrentMakeID($userID),
//                        'chat_id'=>$userID
//                    ]);
//                }break;














                case '7':{
                    $DB = getMakeDB(getCurrentMakeID($userID));
                    $image_id = isset(end($update->message->photo)->file_id)?end($update->message->photo)->file_id:false;
                    if($image_id === false){
                        makeHTTPRequest('sendMessage',[
                            'text'=>"فایل اشتباه است\nجهت انصراف ، کامند /cancel را وارد کنید",
                            'chat_id'=>$userID
                        ]);
                    }else{
                        $imag = makeHTTPRequest('getFile',[
                            'file_id'=>$image_id
                        ]);
                        $t = time().'.jpg';
                        $furl = 'http://bots.mhr-developer.com/images/'.$t;
                        $fule = 'https://api.telegram.org/file/bot188540245:AAGGTpBT2UIDeuvRMfxq54KsrJsi05URSHI/'.trim($imag->result->file_path,"\\/");
                        $file_ext = end(explode(".",$fule));
                        if($file_ext == "jpg" || $file_ext == "jpeg") {
                            file_put_contents("/var/www/html/images/".$t, fopen($fule, 'r'));
                            if(!isset($DB->s)) $DB->s = json_decode("{}");
                            $DB->s->image_id = $furl;
                            updateDB(getCurrentMakeID($userID),$DB);
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>"لطفا متن را ارسال کنید\nجهت انصراف ، کامند /cancel را وارد کنید"
                            ]);
                            setUserStep($userID,'8');
                        }else{
                            makeHTTPRequest('sendMessage', [
                                'chat_id' => $userID,
                                'text' => "لطفا فایل jpg ارسال نمایید\nفایل ارسالی شما $file_ext بوده است.\n<a href='http://image.online-convert.com/convert-to-jpg'>برای تبدیل آنلاین کلیک کنید</a>\nجهت انصراف ، کامند /cancel را وارد کنید",
                                'parse_mode'=>"HTML"
                            ]);
                        }
                    }
                }break;

                case '8':{
                    if(mb_strlen($update->message->text) > 300){
                        makeHTTPRequest('sendMessage', [
                            'text' => "متن ارسالی شما '.mb_strlen($update->message->text).' کاراکتر است . لطفا کمتر از 300 کاراکتر بفرستید .\nجهت انصراف ، کامند /cancel را وارد کنید",
                            'chat_id' => $userID
                        ]);
                    }else {
                        $DB = getMakeDB(getCurrentMakeID($userID));
                        $DB->s->text = $userTEXT;
                        updateDB(getCurrentMakeID($userID), $DB);
                        setUserStep($userID, '9');
                        makeHTTPRequest('sendMessage', [
                            'text' => "لینک جوین دوم را بدهید\nجهت انصراف ، کامند /cancel را وارد کنید",
                            'chat_id' => $userID
                        ]);
                    }
                }break;

                case '9':{

                    if(is_valid_url($userTEXT)) {
                        $DB = getMakeDB(getCurrentMakeID($userID));
                        $DB->s->join = $userTEXT;
                        updateDB(getCurrentMakeID($userID),$DB);
                        setUserStep($userID,'10');
                        makeHTTPRequest('sendMessage',[
                            'text'=>"شناسه کانال را بدهید. مانند @mhrdev\n\nاگر کانال شناسه ندارد کامند /skipstep  را بزنید\nجهت انصراف ، کامند /cancel را وارد کنید",
                            'chat_id'=>$userID
                        ]);
                    }else{
                        makeHTTPRequest('sendMessage', [
                            'text' => "لطفا یک لینک درست ارسال کنید .\nلینک ها با https://telegram.me/joinchat آغاز می شوند\nجهت انصراف ، کامند /cancel را وارد کنید",
                            'chat_id' => $userID
                        ]);
                    }

                }break;

                case '10':{
                    $DB = getMakeDB(getCurrentMakeID($userID));
                    $DB->s->chid = "@mhrdev";
                    $DB->done = true;
                    updateDB(getCurrentMakeID($userID),$DB);
                    setUserStep($userID,'1');
                    setUserLocation($userID,'home');
                    makeHTTPRequest('sendMessage',[
                        'text'=>"کانال دوم با موفقیت ثبت شد\nعبارت زیر را در کانال وارد کنید سپس کانال مربوط به خود را انتخاب کنید\n\nاگر نمی دانید با این کد چی کار کنید کامند /help را وارد کنید",
                        'chat_id'=>$userID
                    ]);
                    makeHTTPRequest('sendMessage',[
                        'text'=>'@sscapachebot getbanner-'.getCurrentMakeID($userID),
                        'chat_id'=>$userID
                    ]);
                }break;

            };
        }break;

        default:{
            $links = [
                'tbd'=>'https://telegram.me/joinchat/BdES-z-VdLwCVkbsFxggvg',
                'tbd_c'=>'https://telegram.me/joinchat/BdES-z-8OoIwyyLePo-_aw',
                'mhrdev'=>'https://telegram.me/joinchat/BdES-zwBMnQM1W88YwjSpg',
                'mhrdev_c'=>'https://telegram.me/joinchat/BdES-zwJKKGeFT8434LVsQ'
            ];
            var_dump(makeHTTPRequest('sendMessage',[
                'chat_id'=>$userID,
                'text'=>"🌐به ربات دکمه شیشه ایی خوب آمدید
➖➖➖➖➖➖➖
اگر می خواهید با ربات و روش کار آن آشنا شوید کامند /help را وارد کنید .

این ربات به شما این امکان می دهد که برای کانال خود یک بنر با عکس و دکمه شیشه ایی پیاده کنید .

⌨ قابلیت جدید ربات ساخت لیست تبادلات است

⌨ قابلیت جدید ربات تبدیل لیست تبادل به لیست شیشه ایی !

⌨قابلیت ارسال به کانال بدون Via .

حتما راهنما را مطالعه کنید ( برای مطالعه روی کامند روبرو کلیک کنید :  /help‌‌)

برای شروع پر کردن و ساخت بنر ،روی  عبارت /newtab  کلیک کنید
/newtab

برای ساخت لیست تبادل روی /newlist کلیک کنید
/newlist

برای تبدیل لیست تبادل به لیست شیشه ایی /convert را بدهید .
/convert

برای ارسال به کانال بدون via بر روی /novia کلیک کنید .
/novia

آموزش ویدیویی:
https://telegram.me/mhrdev/834
.",
                'parse_mode'=>'HTML',
                'reply_markup'=>json_encode([
                    'inline_keyboard'=>[
                        [['text'=>$ad_btn,'url'=>$ad_url]], 
                      [['text'=>'به رایگان در تلگـ ـلیست کانال خود را ثبت کنید','url'=>'http://teleglist.com/?bot']],

                        [['text'=>'کانال اطلاع رسانی ربات','url'=>$links['mhrdev_c']]],

                        [['text'=>'تماس با توسعه دهنده','url'=>'https://telegram.me/pp2007ws']],
                        [['text'=>'ربات نظر سنجی در کانال','url'=>'https://telegram.me/chvotebot']]
                    ]
                ])
            ]));
        }

    }

    var_dump([
        'Location'=>$currentLocation,
        'Step'=>$currentStep
    ]);

}

$clean = ob_get_clean();
//file_put_contents('log',$clean);

$userID = isset($update->message)?$update->message->from->id:$update->inline_query->from->id;

if( $userID == $the_admin_id){
    file_put_contents('ola',$clean);
}


