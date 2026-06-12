<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * TRGM_MATCH(query, text)
 *
 * Compiles to: (word_similarity(query, text) > 0.25)
 * Requires pg_trgm extension.
 */
class TrgmMatchFunction extends FunctionNode
{
    private Node $queryExpr;
    private Node $textExpr;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->queryExpr = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->textExpr = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            '(word_similarity(%s, %s) > 0.25)',
            $this->queryExpr->dispatch($sqlWalker),
            $this->textExpr->dispatch($sqlWalker)
        );
    }
}
