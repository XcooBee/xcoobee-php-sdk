<?php namespace XcooBee\Exceptions;

use GuzzleHttp\Exception\RequestException;
use XcooBee\util\JSON;

class ApiException extends \RuntimeException implements XcoobeeException
{
    /**
     * @param RequestException $e
     *
     * @return self
     */
    public static function fromRequestException(RequestException $e): self
    {
        $message = $e->getMessage();

        /* @noinspection NullPointerExceptionInspection */
        if ($e->getResponse() && JSON::isValid($responseBody = (string) $e->getResponse()->getBody())) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $errors = JSON::decode($responseBody, true);
            $message = $errors['error']['message'] ?? $message;
        }

        $candidates = array_filter(array_map(function ($key, $class) use ($message, $e) {
            return false !== stripos($message, $key)
                ? new $class($e->getCode(), $e)
                : null;
        }, array_keys(self::$errors), self::$errors));

        $fallback = new static($message, $e->getCode(), $e);

        return array_shift($candidates) ?? $fallback;
    }
}
