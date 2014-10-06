<?php

namespace uarsoftware\dbpatch\App;

class Patch implements PatchInterface{

    protected $patchName;
    protected $isAppliedPatch;

    protected $patchSuccess;
    protected $errorCode;
    protected $errorMessage;



    public function __construct($patchName) {
        $this->patchName = $patchName;
        $this->setAsUnpppliedPatch();

        $this->patchSuccess = false;
        $this->errorCode = "";
        $this->errorMessage = "";
    }

    public function getBaseName() {
        return basename($this->patchName);
    }

    public function __toString() {
        return $this->getBaseName();
    }

    public function setAsAppliedPatch() {
        $this->isAppliedPatch = true;
    }

    public function setAsUnpppliedPatch() {
        $this->isAppliedPatch = false;
    }

    public function hasBeenApplied() {
        return $this->isAppliedPatch;
    }

    public function isRealFile() {
        if (file_exists($this->patchName)) {
            return true;
        } else {
            return false;
        }
    }

    public function isSuccessful() {
        return $this->patchSuccess;
    }

    public function setSuccessful() {
        $this->patchSuccess = true;
    }

    public function setFailed($code,$message) {
        $this->patchSuccess = false;
        $this->errorCode = $code;
        $this->errorMessage = $message;
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }

    public function getPatchStatements() {
        $statements = array();

        if ($this->isRealFile()) {
            $fileContents = file_get_contents($this->patchName);
            $statements = $this->splitContents($fileContents);
        }

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