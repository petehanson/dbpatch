<?php

namespace uarsoftware\dbpatch\App;


class StatementParser implements StatementParserInterface {

    public function getStatements($contents) {
        $statements = $this->splitContents($contents);
        return $statements;
    }

    protected function splitContents($contents) {

        list($contents,$tokens) = $this->parseStrings($contents);

        $resultStatements = array();
        $initial_statements = explode(";",$contents);


        foreach ($initial_statements as $statement) {
            $statement = trim($statement);

            if (strlen($statement) > 0) {

                $statement = $this->replaceTokens($statement,$tokens);
                array_push($resultStatements,$statement);
            }
        }

        return $resultStatements;
    }

    protected function parseStrings($content) {

        $stringTokens = array();
        $i = 0;

        $pattern = "/'.*?(?<!\\\)'/";
        $stringTokens = array_merge($stringTokens,$this->processPattern($pattern,$content,$i));

        $pattern = '/".*?(?<!\\\)"/';
        $stringTokens = array_merge($stringTokens,$this->processPattern($pattern,$content,$i));


        return array($content,$stringTokens);
    }


    protected function processPattern($pattern,&$content,&$i) {

        $tokens = array();

        while (preg_match($pattern,$content,$matches) > 0) {

            $matchedText = $matches[0];
            $tokenID = "[[[" . $i . "]]]";

            $tokens[$tokenID] = $matchedText;

            $content = str_replace($matchedText,$tokenID,$content);
            $i++;
        }

        return $tokens;
    }


    protected function replaceTokens($content,$tokens) {
        return str_replace(array_keys($tokens),array_values($tokens),$content);
    }
}
