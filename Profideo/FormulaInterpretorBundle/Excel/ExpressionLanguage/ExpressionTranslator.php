<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage;

final class ExpressionTranslator
{
    /**
     * @var array
     */
    protected static $messages = [
//        '/Unexpected "(.*)"/' => 'translateUnexpected',
//        '/Unclosed "(.*)"/' => 'translateUnclosed',
//        '/Unexpected character "(.*)"/' => 'translateUnexpectedCharacter',
//        '/Unexpected token "(.*)" of value "(.*)"/' => 'translateUnexpectedTokenValue',
//        '/The function "(.*)" does not exist"/' => 'translateFunctionDoesNotExist',
//        '/Variable "(.*)" is not valid"/' => 'translateVariableNotValid',
//        '/Expected name/' => 'Nom attendu',
//        '/Unexpected end of expression' => 'Fin d\'expression non attendue',
//
//        '/An expression must start with an equal sign/' => 'Une formule doit commencer par un égal "="',
//        '/An expression must contains at least (.*) functions/' => 'translateMinimumFunction',
    ];

    /**
     * @param $message
     *
     * @return string
     */
    public static function translate($message)
    {
        foreach (self::$messages as $pattern => $messageTranslation) {
            if (preg_match($pattern, $message, $matches)) {
                if (method_exists(__CLASS__, $messageTranslation)) {
                    return call_user_func([__CLASS__, $messageTranslation], $matches);
                }

                return $messageTranslation;
            }
        }

        return $message;
    }
}
