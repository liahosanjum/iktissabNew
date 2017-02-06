<?php if (!class_exists('CaptchaConfiguration')) { return; }

// BotDetect PHP Captcha configuration options

return [
    'FormCaptcha' => [
        'UserInputID' => 'captchaCode',
        'ImageWidth' => 150,
        'ImageHeight' => 35,
        'CharacterSet' => 'Latin',
        'CustomDarkColor' => '#505050',
        'CodeLength' => 4,
        'SoundEnabled' => false,
        'ReloadEnabled' =>false,
        'ImageStyle' => [
            ImageStyle::BlackOverlap
            ]
        ]

];

