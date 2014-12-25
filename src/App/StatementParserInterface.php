<?php

namespace uarsoftware\dbpatch\App;

interface StatementParserInterface {
    public function getStatements($content);
}