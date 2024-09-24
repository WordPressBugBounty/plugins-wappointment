<?php

namespace WappoVendor\Egulias\EmailValidator\Validation;

use WappoVendor\Egulias\EmailValidator\EmailLexer;
use WappoVendor\Egulias\EmailValidator\EmailParser;
use WappoVendor\Egulias\EmailValidator\Result\InvalidEmail;
use WappoVendor\Egulias\EmailValidator\Result\Reason\ExceptionFound;
class RFCValidation implements EmailValidation
{
    /**
     * @var EmailParser|null
     */
    private $parser;
    /**
     * @var array
     */
    private $warnings = [];
    /**
     * @var ?InvalidEmail
     */
    private $error;
    public function isValid(string $email, EmailLexer $emailLexer) : bool
    {
        $this->parser = new EmailParser($emailLexer);
        try {
            $result = $this->parser->parse($email);
            $this->warnings = $this->parser->getWarnings();
            if ($result->isInvalid()) {
                /** @psalm-suppress PropertyTypeCoercion */
                $this->error = $result;
                return \false;
            }
        } catch (\Exception $invalid) {
            $this->error = new InvalidEmail(new ExceptionFound($invalid), '');
            return \false;
        }
        return \true;
    }
    public function getError() : ?InvalidEmail
    {
        return $this->error;
    }
    public function getWarnings() : array
    {
        return $this->warnings;
    }
}
