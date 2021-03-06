<?php

namespace extpoint\yii2\gii\helpers;

use extpoint\yii2\gii\models\ValueExpression;
use yii\db\Schema;
use yii\helpers\Json;

class GiiHelper
{
    public static function getDbTypes()
    {
        $classInfo = new \ReflectionClass(Schema::className());
        return array_values($classInfo->getConstants());
    }

    public static function getTableNames()
    {
        return \Yii::$app->db->schema->tableNames;
    }

    public static function varExport($var, $indent = '', $arrayLine = false)
    {
        $type = gettype($var);
        if (in_array($var, ['true', 'false'])) {
            $type = 'boolean';
        }
        if (is_int($var)) {
            $type = 'int';
        }
        if ($var instanceof ValueExpression) {
            return (string)$var;
        }
        switch ($type) {
            case 'string':
                return "'" . addcslashes($var, "\\\$\'\r\n\t\v\f") . "'";
            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                $arrayIndent = !$arrayLine ? "\n" : '';
                foreach ($var as $key => $value) {
                    $r[] = $indent . (!$arrayLine ? '    ' : '')
                        . ($indexed ? '' : static::varExport($key) . ' => ')
                        . static::varExport($value, !$arrayLine ? $indent . '    ' : '', $arrayLine);
                }
                return "[$arrayIndent" . implode(",$arrayIndent", $r) . "$arrayIndent" . $indent . ']';
            case 'boolean':
                return $var ? 'true' : 'false';
            default:
                return var_export($var, TRUE);
        }
    }

    public static function varJsExport($var, $indent = '')
    {
        $code = Json::encode($var, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $code = str_replace("\n", "\n$indent", $code);
        $code = str_replace("'", '\u0027', $code);
        $code = str_replace('"', "'", $code);
        $code = str_replace('\u0027', "'", $code);
        $code = str_replace("'true'", "true", $code);
        $code = str_replace("'false'", "false", $code);
        return $code;
    }
}