<html>
<body>
<table border=0 cellpadding="4px" style="background-color: red; color: yellow; font-size: 12px; font-family: Lucida Grande, Verdana, Geneva, Sans-serif">
    <tr>
        <td style="vertical-align: top">Error:</td>
        <td><pre><?= /** @var string $message */
                $message ?></pre></td>
    </tr>
    <tr>
        <td>File:</td>
        <td><?= /** @var string $file */
            $file ?></td>
    </tr>
    <tr>
        <td>Line:</td>
        <td><?= /** @var int $line */
            $line ?></td>
    </tr>
</table>
</body>
</html>
