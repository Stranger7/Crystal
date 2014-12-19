<?php
/**
 * @var string $title
 */
?>
<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
<?php
if (isset($css)) \core\App::view('common\css', ['list' => $css]);
if (isset($js)) \core\App::view('common\js', ['list' => $js]);
?>
</head>
