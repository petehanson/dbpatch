<?php

namespace uarsoftware\dbpatch\App;

class PatchApplierSql extends PatchApplierAbstract {

    protected $statementParser = null;

    public function setStatementParser(StatementParserInterface $parser) {
        $this->statementParser = $parser;
    }


    public function apply(PatchInterface $patch,DatabaseInterface $db) {

        if ($this->statementParser == null) {
            throw new \exception("PatchApplierSql requires a StatementParserInterface to be set before it can run");
        }

        $sqlStatements = $this->statementParser->getStatements($patch->getPatchContents());

        $this->statementCount = count($sqlStatements);

        foreach ($sqlStatements as $sql) {
            $result = $db->executeQuery($sql);

            if ($result->status == false) {
                $this->status = false;
                $this->errorCode = $result->errorCode;
                $this->errorMessage = $result->errorMessage . ", executed query: " . $sql;

                return $this->status;
            }
        }


        $this->status = true;
        return $this->status;
    }

}