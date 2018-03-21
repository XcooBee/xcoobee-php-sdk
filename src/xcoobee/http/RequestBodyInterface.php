<?php
namespace xcoobee\http;

/**
 * RequestBodyInterface
 */
interface RequestBodyInterface
{
    /**
     * Get the Body of the Request
     *
     * @return string|resource|Psr\Http\Message\StreamInterface
     */
    public function getBody();
}
