<?php

declare(strict_types=1);

/**
 * IXR_Base64
 *
 * @package IXR
 * @since 1.5.0
 */
class IXR_Base64
{
    public $data;

    /**
     * PHP5 constructor.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * PHP4 constructor.
     */
    public function IXR_Base64($data): void
    {
        self::__construct($data);
    }

    public function getXml(): string
    {
        return '<base64>'.base64_encode($this->data).'</base64>';
    }
}
