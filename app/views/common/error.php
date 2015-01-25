<?php
/**
 * @var int    $code
 * @var string $message
 * @var string $file
 * @var int    $line
 * @var string $trace
 */
?>
<html lang="ru">
<head>
    <meta charset="utf-8">
</head>
<body>
<?php if (\core\App::mode() != 'production'): ?>
<table border=0 cellpadding="4px" style="background-color: red; color: yellow; font-size: 12px; font-family: Lucida Grande, Verdana, Geneva, Sans-serif">
    <tr>
        <td style="vertical-align: top">Error:</td>
        <td><pre><?= $message ?></pre></td>
    </tr>
    <tr>
        <td>File:</td>
        <td><?= $file ?></td>
    </tr>
    <tr>
        <td>Line:</td>
        <td><?= $line ?></td>
    </tr>
    <tr>
        <td style="vertical-align: top">Trace:</td>
        <td><pre><?= $trace ?></pre></td>
    </tr>
</table>
<?php else: ?>
    <p style="font-size: 32px; font-family: Arial, Verdana, sans-serif"><?= \core\Http::getStatusText($code) ?></p>
<?php endif; ?>
</body>
</html>
