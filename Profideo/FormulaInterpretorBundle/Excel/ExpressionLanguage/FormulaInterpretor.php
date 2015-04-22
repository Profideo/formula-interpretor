<?php

namespace Profideo\FormulaInterpretorBundle\Excel\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\Node\BinaryNode;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use Symfony\Component\ExpressionLanguage\Node\FunctionNode;
use Symfony\Component\ExpressionLanguage\Node\NameNode;
use Symfony\Component\ExpressionLanguage\Node\Node;
use Symfony\Component\ExpressionLanguage\ParsedExpression;

class FormulaInterpretor
{
    private $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    public function parse($expression, array $names = array(), $constantTypes = array())
    {
        $parsedExpression = $this->expressionLanguage->parse($expression, $names);

        $this->validateTypes($this->getChildren($parsedExpression), $constantTypes);

        return $parsedExpression;
    }

    public function compile($expression, array $values = array())
    {
        if (!$expression instanceof ParsedExpression) {
            $expression = $this->parse($expression, array_keys($values));
        }

        return $this->expressionLanguage->compile($expression, array_keys($values));
    }

    public function evaluate($expression, array $values = array())
    {
        if (!$expression instanceof ParsedExpression) {
            $expression = $this->parse($expression, array_keys($values));
        }

        return $this->expressionLanguage->evaluate($expression, $values);
    }

    private function validateTypes($nodeChildren, $constantTypes = array())
    {
        if ($nodeChildren instanceof BinaryNode) {
            $this->validateBinaryNode($nodeChildren, $constantTypes);
        } elseif (is_array($this->getChildren($nodeChildren))) {
            $this->validateTypes($this->getChildren($nodeChildren), $constantTypes);
        } elseif (is_array($nodeChildren)) {
            foreach ($nodeChildren as $nodeChild) {
                $this->validateTypes($nodeChild, $constantTypes);
            }
        }
    }

    private function validateBinaryNode($node, $constantTypes)
    {
        $arguments = $this->getChildren($node);

        if (
            ($arguments['left'] instanceof ConstantNode || $arguments['left'] instanceof NameNode) &&
            ($arguments['right'] instanceof ConstantNode || $arguments['right'] instanceof NameNode)
        ) {
            $operandLeft = $arguments['left'];
            $operandRight = $arguments['right'];

            if ($arguments['left'] instanceof ConstantNode) {
                $arguments['left']->attributes['value'] = $this->fixType($arguments['left']->attributes['value']);
                $valueLeft = $arguments['left']->attributes['value'];
            } else {
                $valueLeft = $arguments['left']->attributes['name'];
            }

            if ($arguments['right'] instanceof ConstantNode) {
                $arguments['right']->attributes['value'] = $this->fixType($arguments['right']->attributes['value']);
                $valueRight = $arguments['right']->attributes['value'];
            } else {
                $valueRight = $arguments['right']->attributes['name'];
            }

            if (!$this->compatibleTypes($operandLeft, $operandRight, $constantTypes)) {
                throw new ExpressionError(sprintf('Compared values %s and %s are not the same type', $valueLeft, $valueRight));
            }
        }
    }

    private function compatibleTypes($operand1, $operand2, $constantTypes = array())
    {
        $type1 = 'unknown';
        $type2 = 'unknown';

        $value1 = $operand1 instanceof NameNode ? $operand1->attributes['name'] : ($operand1 instanceof ConstantNode ? $operand1->attributes['value'] : null);
        $value2 = $operand2 instanceof NameNode ? $operand2->attributes['name'] : ($operand2 instanceof ConstantNode ? $operand2->attributes['value'] : null);

        if ($operand1 instanceof NameNode && isset($constantTypes[$value1])) {
            $type1 = $constantTypes[$operand1->attributes['name']];
        } elseif ($operand1 instanceof ConstantNode && is_numeric($value1)) {
            $type1 = 'double';
        } elseif ($operand1 instanceof ConstantNode && is_string($value1)) {
            $type1 = 'string';
        }

        if ($operand2 instanceof NameNode && isset($constantTypes[$value2])) {
            $type2 = $constantTypes[$operand2->attributes['name']];
        } elseif ($operand2 instanceof ConstantNode && is_numeric($value2)) {
            $type2 = 'double';
        } elseif ($operand2 instanceof ConstantNode && is_string($value2)) {
            $type2 = 'string';
        }

        if ($type1 === 'unknown' || $type2 === 'unknown' || $type1 !== $type2) {
            return false;
        }

        return true;
    }

    private function getChildren($node)
    {
        if (!is_object($node)) {
            return false;
        }

        if ($node instanceof ParsedExpression) {
            return $node->getNodes();
        } elseif ($node instanceof Node) {
            return $node->nodes;
        } elseif ($node instanceof FunctionNode) {
            return $node->nodes['arguments'];
        } elseif ($node instanceof BinaryNode) {
            return [$node->nodes['left'], $node->nodes['right']];
        }

        return false;
    }

    private function fixType($value)
    {
        if (!($value instanceof NameNode) && is_numeric($value)) {
            return floatval($value);
        }

        return $value;
    }
}
