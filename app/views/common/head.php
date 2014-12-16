<?php
/**
 * @var array $css
 * @var array $js
 */
?>
<head>
    <meta charset="utf-8">
<?php
    \core\App::view('common\css', ['list' => $css]);
    \core\App::view('common\js', ['list' => $js]);
?>
</head>
