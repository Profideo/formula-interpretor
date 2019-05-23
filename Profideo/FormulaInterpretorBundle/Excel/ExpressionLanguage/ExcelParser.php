<?php
namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage;

use Profideo\Component\ExpressionLanguage\Parser;
use Profideo\Component\ExpressionLanguage\Token;
use Profideo\Component\ExpressionLanguage\Node;

class ExcelParser extends Parser
{
    public function parseArrayExpression()
    {
        $this->stream->expect(Token::PUNCTUATION_TYPE, '[', 'An array element was expected');

        $node = new Node\ArrayNode();
        $first = true;
        while (!$this->stream->current->test(Token::PUNCTUATION_TYPE, ']')) {
            if (!$first) {
                $this->stream->expect(Token::PUNCTUATION_TYPE, ';', 'An array element must be followed by a semi-colon');

                // trailing ,?
                if ($this->stream->current->test(Token::PUNCTUATION_TYPE, ']')) {
                    break;
                }
            }
            $first = false;

            $node->addElement($this->parseExpression());
        }
        $this->stream->expect(Token::PUNCTUATION_TYPE, ']', 'An opened array is not properly closed');

        return $node;
    }

    public function parseHashExpression()
    {
        $this->stream->expect(Token::PUNCTUATION_TYPE, '{', 'A hash element was expected');

        $node = new Node\ArrayNode();
        $first = true;
        while (!$this->stream->current->test(Token::PUNCTUATION_TYPE, '}')) {
            if (!$first) {
                $this->stream->expect(Token::PUNCTUATION_TYPE, ';', 'A hash value must be followed by a semi-colon');

                // trailing ,?
                if ($this->stream->current->test(Token::PUNCTUATION_TYPE, '}')) {
                    break;
                }
            }
            $first = false;

            // a hash key can be:
            //
            //  * a number -- 12
            //  * a string -- 'a'
            //  * a name, which is equivalent to a string -- a
            //  * an expression, which must be enclosed in parentheses -- (1 + 2)
            if ($this->stream->current->test(Token::STRING_TYPE) || $this->stream->current->test(Token::NAME_TYPE) || $this->stream->current->test(Token::NUMBER_TYPE)) {
                $key = new Node\ConstantNode($this->stream->current->value);
                $this->stream->next();
            } elseif ($this->stream->current->test(Token::PUNCTUATION_TYPE, '(')) {
                $key = $this->parseExpression();
            } else {
                $current = $this->stream->current;

                throw new SyntaxError(sprintf('A hash key must be a quoted string, a number, a name, or an expression enclosed in parentheses (unexpected token "%s" of value "%s"', $current->type, $current->value), $current->cursor);
            }

            $this->stream->expect(Token::PUNCTUATION_TYPE, ':', 'A hash key must be followed by a colon (:)');
            $value = $this->parseExpression();

            $node->addElement($value, $key);
        }
        $this->stream->expect(Token::PUNCTUATION_TYPE, '}', 'An opened hash is not properly closed');

        return $node;
    }

    /**
     * Parses arguments.
     */
    public function parseArguments()
    {
        $args = array();
        $this->stream->expect(Token::PUNCTUATION_TYPE, '(', 'A list of arguments must begin with an opening parenthesis');
        while (!$this->stream->current->test(Token::PUNCTUATION_TYPE, ')')) {
            if (!empty($args)) {
                $this->stream->expect(Token::PUNCTUATION_TYPE, ';', 'Arguments must be separated by a semi-colon');
            }

            $args[] = $this->parseExpression();
        }
        $this->stream->expect(Token::PUNCTUATION_TYPE, ')', 'A list of arguments must be closed by a parenthesis');

        return new Node\Node($args);
    }
}