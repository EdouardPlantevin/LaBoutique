<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private $api_key = "6db5c270f1af9abae3e8de2764a27e6b";
    private $api_key_secret = "69c00093d8c77e04aaeda6d7f6815a70";

    public function send($to_email, $to_name, $subject, $content)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "plantevin.contact@gmail.com",
                        'Name' => "Edouard"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => "$to_name"
                        ]
                    ],
                    'TemplateID' => 2070578,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}