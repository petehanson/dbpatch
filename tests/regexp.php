<?php

$text = 'insert into foo values ("adslk\"jfaslkjf","fkkk",\'foo\',\'bar\');';

echo $text . "\n";

list ($content,$stringTokens) = parseStrings($text);

echo $content . "\n";

echo replaceTokens($content,$stringTokens);


function parseStrings($content) {
    $pattern = "/'.*?(?<!\\\)'/";

    $stringTokens = array();
    $i = 0;

    while (preg_match($pattern,$content,$matches) > 0) {
        var_dump($matches);

        $matchedText = $matches[0];
        $tokenID = "[[[" . $i . "]]]";

        $stringTokens[$tokenID] = $matchedText;

        $content = str_replace($matchedText,$tokenID,$content);
        $i++;
    }

    $pattern = '/".*?(?<!\\\)"/';
    while (preg_match($pattern,$content,$matches) > 0) {
        var_dump($matches);

        $matchedText = $matches[0];
        $tokenID = "[[[" . $i . "]]]";

        $stringTokens[$tokenID] = $matchedText;

        $content = str_replace($matchedText,$tokenID,$content);
        $i++;
    }


    return array($content,$stringTokens);
}

function replaceTokens($content,$tokens) {
    return str_replace(array_keys($tokens),array_values($tokens),$content);
}
