<?php

$oss_env_def = [
    'test' => [
        'access_key_id' => 'LTAIG93ftsj6FpPU',
        'access_key_secret' => 'kujSqLCwWePVS7OsbfHf2blnUogLGt',
        'endpoint' => 'oss-cn-hangzhou.aliyuncs.com',
        'bucket' => 'stg-agiuav-hz-t2b1',
        'bind_domain' => '//agcdn.aasky.net/'
    ],
    'pro' => [
        'access_key_id' => 'LTAIfktozgHIvc1t',
        'access_key_secret' => 'kjTDfXn33QwfRIW6ukfByWpuOXcWpp',
        'endpoint' => 'oss-cn-hangzhou.aliyuncs.com',
        'bucket' => 'pro-agiuav-hz-t2b0',
        'bind_domain' => '//agcdn.djiny.cn/'
    ],
    'other' => [
        'access_key_id' => 'AKIAI2S3YCLJ3DJ7CYYQ',
        'access_key_secret' => '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',
        'endpoint' => 's3-ap-northeast-2.amazonaws.com',
        'bucket' => 'iuav-seoul-prod',
        'bind_domain' => '//s3-ap-northeast-2.amazonaws.com/iuav-seoul-prod/'
    ]
];

$i18n_data = json_decode(file_get_contents(__DIR__.'/i18n.json'), true);

return [
    'adminEmail' => isset($YII_GLOBAL['adminEmail']) ? $YII_GLOBAL['adminEmail'] : '',
    'GWServer' => isset($YII_GLOBAL['GWServer']) ? $YII_GLOBAL['GWServer'] : '',
    'AGENTGETPASSWORD' => isset($YII_GLOBAL['CDNCONFIG']) ? $YII_GLOBAL['AGENTGETPASSWORD'] : '',
    'CDNCONFIG' => isset($YII_GLOBAL['CDNCONFIG']) ? $YII_GLOBAL['CDNCONFIG'] : '',
    'DJISTOREAPI' => isset($YII_GLOBAL['DJISTOREAPI']) ? $YII_GLOBAL['DJISTOREAPI'] : '',
    'ENV_COUNTRY' => 'CN',
    'OSS_ENV' => $oss_env_def[''],
    'I18N_DATA' => $i18n_data
];
