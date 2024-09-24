<?php

namespace WappoVendor\Egulias\EmailValidator\Parser;

use WappoVendor\Egulias\EmailValidator\EmailLexer;
use WappoVendor\Egulias\EmailValidator\Result\Result;
use WappoVendor\Egulias\EmailValidator\Warning\QuotedPart;
use WappoVendor\Egulias\EmailValidator\Result\InvalidEmail;
use WappoVendor\Egulias\EmailValidator\Parser\CommentStrategy\CommentStrategy;
use WappoVendor\Egulias\EmailValidator\Result\Reason\UnclosedComment;
use WappoVendor\Egulias\EmailValidator\Result\Reason\UnOpenedComment;
use WappoVendor\Egulias\EmailValidator\Warning\Comment as WarningComment;
class Comment extends PartParser
{
    /**
     * @var int
     */
    private $openedParenthesis = 0;
    /**
     * @var CommentStrategy
     */
    private $commentStrategy;
    public function __construct(EmailLexer $lexer, CommentStrategy $commentStrategy)
    {
        $this->lexer = $lexer;
        $this->commentStrategy = $commentStrategy;
    }
    public function parse() : Result
    {
        if ($this->lexer->token['type'] === EmailLexer::S_OPENPARENTHESIS) {
            $this->openedParenthesis++;
            if ($this->noClosingParenthesis()) {
                return new InvalidEmail(new UnclosedComment(), $this->lexer->token['value']);
            }
        }
        if ($this->lexer->token['type'] === EmailLexer::S_CLOSEPARENTHESIS) {
            return new InvalidEmail(new UnOpenedComment(), $this->lexer->token['value']);
        }
        $this->warnings[WarningComment::CODE] = new WarningComment();
        $moreTokens = \true;
        while ($this->commentStrategy->exitCondition($this->lexer, $this->openedParenthesis) && $moreTokens) {
            if ($this->lexer->isNextToken(EmailLexer::S_OPENPARENTHESIS)) {
                $this->openedParenthesis++;
            }
            $this->warnEscaping();
            if ($this->lexer->isNextToken(EmailLexer::S_CLOSEPARENTHESIS)) {
                $this->openedParenthesis--;
            }
            $moreTokens = $this->lexer->moveNext();
        }
        if ($this->openedParenthesis >= 1) {
            return new InvalidEmail(new UnclosedComment(), $this->lexer->token['value']);
        }
        if ($this->openedParenthesis < 0) {
            return new InvalidEmail(new UnOpenedComment(), $this->lexer->token['value']);
        }
        $finalValidations = $this->commentStrategy->endOfLoopValidations($this->lexer);
        $this->warnings = \array_merge($this->warnings, $this->commentStrategy->getWarnings());
        return $finalValidations;
    }
    /**
     * @return bool
     */
    private function warnEscaping() : bool
    {
        //Backslash found
        if ($this->lexer->token['type'] !== EmailLexer::S_BACKSLASH) {
            return \false;
        }
        if (!$this->lexer->isNextTokenAny(array(EmailLexer::S_SP, EmailLexer::S_HTAB, EmailLexer::C_DEL))) {
            return \false;
        }
        $this->warnings[QuotedPart::CODE] = new QuotedPart($this->lexer->getPrevious()['type'], $this->lexer->token['type']);
        return \true;
    }
    private function noClosingParenthesis() : bool
    {
        try {
            $this->lexer->find(EmailLexer::S_CLOSEPARENTHESIS);
            return \false;
        } catch (\RuntimeException $e) {
            return \true;
        }
    }
}
