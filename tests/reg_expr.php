<?php
/**
 * Created by PhpStorm.
 * User: Сергей
 * Date: 16.01.2015
 * Time: 11:52
 */
$mask = '/orders/preview?[/%1][/%2][/%3][/%4]';
$pattern = '/^(.*)\?(.*)$/U';
$a = preg_split($pattern, $mask, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
var_dump($a);

$pattern = '/(\[\/\%\d+\])/U';
$b = preg_split($pattern, $a[1], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
var_dump($b);

$pattern = '/^(\[\/\%\d+\])$/';
var_dump(preg_match($pattern, $b[0]));

$a = [1];
array_shift($a);
var_dump($a);

$pattern = '/^(POST):\/orders\/preview(\/\w+){1,3}$/i';
$url = 'POST:/orders/preview/a/b/c/d';
var_dump(preg_match($pattern, $url));

$s = '?';
var_dump($s[0]);
$s = substr($s, 1);