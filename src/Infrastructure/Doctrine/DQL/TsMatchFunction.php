<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * TSMATCH(text, query)
 * 
 * Compiles to:
 * (to_tsvector('simple', text) @@ websearch_to_tsquery('simple', query))
 */
class TsMatchFunction extends FunctionNode
{
    private $vectorExpr;
    private $queryExpr;

    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        
        $this->vectorExpr = $parser->StringPrimary();
        
        $parser->match(Lexer::T_COMMA);
        
        $this->queryExpr = $parser->StringPrimary();
        
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            "(to_tsvector('simple', %s) @@ websearch_to_tsquery('simple', %s))",
            $this->vectorExpr->dispatch($sqlWalker),
            $this->queryExpr->dispatch($sqlWalker)
        );
    }
}
