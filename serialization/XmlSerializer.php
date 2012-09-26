<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * XML serialization
 *
 * @author fxneo
 */
class Xml_Serializer {

    public static function generateValidXmlFromObj(stdClass $obj, $node_block = 'items') {
        $arr = get_object_vars($obj);
        return self::generateValidXmlFromArray($arr, $node_block);
    }

    public static function generateValidXmlFromArray($array, $node_block = 'items', $include_xml_declaration = false) {
        $xml = '';
        
        if ($include_xml_declaration)
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generateXmlFromArray($array);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }

    private static function generateXmlFromArray($array) {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key => $value) {
                $xml .= '<' . $key . '>' . self::generateXmlFromArray($value) . '</' . $key . '>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }

}

?>
