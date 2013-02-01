<?php

setcookie('fbs_'.$facebook->getAppId(), '', time()-100, '/', 'domain.com');
session_destroy();
header('Location: /');