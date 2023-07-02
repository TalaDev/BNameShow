<?php
//مقادیر خواسته شده رو پر کنید
$config = [
    'token' => 'TOKEN', //توکن ربات
];

error_reporting(false);
function BINAM($method, $post = [])
{
    $cp = curl_init('https://api.telegram.org/bot' . $GLOBALS['config']['token'] . '/' . $method);
    curl_setopt_array($cp, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_RETURNTRANSFER => true
    ]);
    return json_decode(curl_exec($cp), true);
    curl_close($cp);
}
$update = json_decode(file_get_contents('php://input'), true);
if (!is_null($update['message'])) {
    $message = $update['message'];
    $reply = $message['reply_to_message'];
    $text = $message['text'];
    $chatid = $message['chat']['id'];
    $chattype = $message['chat']['type'];
    $messageid = $message['id'];
}
if ($chattype != 'private') BINAM('leaveChat', ['chat_id' => $chatid]);

if (preg_match('/^\/start/i', $text)) {
    BINAM('sendMessage', [
        'chat_id' => $chatid,
        'text' => '
👋🏻 به ربات بینام شو خوش اومدید

کاربردهای ربات :
- بینام کردن فوروارد
- تغییر کپشن با ریپلای
- باز ارسال محتوای محدودشده




'
    ]);
} else {
    if (isset($text)) {
        if (isset($reply) && is_null($reply['text'])) {
            $media = ['photo', 'video', 'document', 'audio', 'voice', 'video_note', 'sticker'];
            $mediatype = array_intersect(array_keys($reply), $media);
            BINAM('send' . str_replace('_', null, end($mediatype)), [
                'chat_id' => $chatid,
                end($mediatype) => $reply[end($mediatype)]['file_id'] ?: end($reply[end($mediatype)])['file_id'],
                'caption' => $text
            ]);
        } else {
            BINAM('sendMessage', [
                'chat_id' => $chatid,
                'text' => $text,
                'reply_to_message_id' => $messageid,
                'disable_web_page_preview' => true
            ]);
        }
    } else {
        $media = ['photo', 'video', 'document', 'audio', 'voice', 'video_note', 'sticker'];
        $mediatype = array_intersect(array_keys($message), $media);
        BINAM('send' . str_replace('_', null, end($mediatype)), [
            'chat_id' => $chatid,
            end($mediatype) => $message[end($mediatype)]['file_id'] ?: end($message[end($mediatype)])['file_id'],
            'caption' => $message['caption'],
            'reply_markup' => json_encode(['force_reply' => true, 'selective' => true])
        ]);
    }
}

