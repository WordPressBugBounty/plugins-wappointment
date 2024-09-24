<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Wappointment\ClassConnect\EmailValidator;
use Wappointment\ClassConnect\RFCValidation;

/**
 * An ID MIME Header for something like Message-ID or Content-ID.
 *
 * @author Chris Corbyn
 */
// @codingStandardsIgnoreFile
class WappoSwift_Mime_Headers_IdentificationHeader extends WappoSwift_Mime_Headers_AbstractHeader
{
    /**
     * The IDs used in the value of this Header.
     *
     * This may hold multiple IDs or just a single ID.
     *
     * @var string[]
     */
    private $ids = [];

    /**
     * The strict EmailValidator.
     *
     * @var EmailValidator
     */
    private $emailValidator;

    private $addressEncoder;

    /**
     * Creates a new IdentificationHeader with the given $name and $id.
     *
     * @param string $name
     */
    public function __construct($name, EmailValidator $emailValidator, WappoSwift_AddressEncoder $addressEncoder = null)
    {
        $this->setFieldName($name);
        $this->emailValidator = $emailValidator;
        $this->addressEncoder = $addressEncoder ?? new WappoSwift_AddressEncoder_IdnAddressEncoder();
    }

    /**
     * Get the type of Header that this instance represents.
     *
     * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
     * @see TYPE_DATE, TYPE_ID, TYPE_PATH
     *
     * @return int
     */
    public function getFieldType()
    {
        return self::TYPE_ID;
    }

    /**
     * Set the model for the field body.
     *
     * This method takes a string ID, or an array of IDs.
     *
     * @param mixed $model
     *
     * @throws WappoSwift_RfcComplianceException
     */
    public function setFieldBodyModel($model)
    {
        $this->setId($model);
    }

    /**
     * Get the model for the field body.
     *
     * This method returns an array of IDs
     *
     * @return array
     */
    public function getFieldBodyModel()
    {
        return $this->getIds();
    }

    /**
     * Set the ID used in the value of this header.
     *
     * @param string|array $id
     *
     * @throws WappoSwift_RfcComplianceException
     */
    public function setId($id)
    {
        $this->setIds(is_array($id) ? $id : [$id]);
    }

    /**
     * Get the ID used in the value of this Header.
     *
     * If multiple IDs are set only the first is returned.
     *
     * @return string
     */
    public function getId()
    {
        if (count($this->ids) > 0) {
            return $this->ids[0];
        }
    }

    /**
     * Set a collection of IDs to use in the value of this Header.
     *
     * @param string[] $ids
     *
     * @throws WappoSwift_RfcComplianceException
     */
    public function setIds(array $ids)
    {
        $actualIds = [];

        foreach ($ids as $id) {
            $this->assertValidId($id);
            $actualIds[] = $id;
        }

        $this->clearCachedValueIf($this->ids != $actualIds);
        $this->ids = $actualIds;
    }

    /**
     * Get the list of IDs used in this Header.
     *
     * @return string[]
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * Get the string value of the body in this Header.
     *
     * This is not necessarily RFC 2822 compliant since folding white space will
     * not be added at this stage (see {@see toString()} for that).
     *
     * @see toString()
     *
     * @throws WappoSwift_RfcComplianceException
     *
     * @return string
     */
    public function getFieldBody()
    {
        if (!$this->getCachedValue()) {
            $angleAddrs = [];

            foreach ($this->ids as $id) {
                $angleAddrs[] = '<'.$this->addressEncoder->encodeString($id).'>';
            }

            $this->setCachedValue(implode(' ', $angleAddrs));
        }

        return $this->getCachedValue();
    }

    /**
     * Throws an Exception if the id passed does not comply with RFC 2822.
     *
     * @param string $id
     *
     * @throws WappoSwift_RfcComplianceException
     */
    private function assertValidId($id)
    {
        if (!$this->emailValidator->isValid($id, new RFCValidation())) {
            throw new WappoSwift_RfcComplianceException('Invalid ID given <'.$id.'>');
        }
    }
}
