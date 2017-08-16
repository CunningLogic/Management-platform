<?php

return [
    'adminEmail' => isset($YII_GLOBAL['adminEmail']) ? $YII_GLOBAL['adminEmail'] : '',
    'GWServer' => isset($YII_GLOBAL['GWServer']) ? $YII_GLOBAL['GWServer'] : '',
    'AGENTGETPASSWORD' => isset($YII_GLOBAL['CDNCONFIG']) ? $YII_GLOBAL['AGENTGETPASSWORD'] : '',
    'CDNCONFIG' => isset($YII_GLOBAL['CDNCONFIG']) ? $YII_GLOBAL['CDNCONFIG'] : '',
    'DJISTOREAPI' => isset($YII_GLOBAL['DJISTOREAPI']) ? $YII_GLOBAL['DJISTOREAPI'] : '',
];
