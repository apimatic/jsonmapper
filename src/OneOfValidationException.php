<?php

/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Apimatic
 * @package  JsonMapper
 * @author   Asad Ali <asad.ali@apimatic.io>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://www.apimatic.io/
 */
namespace apimatic\jsonmapper;

/**
 * OneOf Validation Exception.
 *
 * @category Apimatic
 * @package  JsonMapper
 * @author   Asad Ali <asad.ali@apimatic.io>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://www.apimatic.io/
 */
class OneOfValidationException extends JsonMapperException
{
    /**
     * Exception raised when a json object maps to more 
     * than one type within the types specified within OneOf.
     * 
     * @param string $matchedType First type.
     * @param string $mappedWith  Second type.
     * @param string $json        JSON string.
     * 
     * @return OneOfValidationException
     */
    static function moreThanOneOfException($matchedType, $mappedWith, $json)
    {
        return new self(
            "Cannot map more than OneOf { $matchedType and $mappedWith } on: $json"
        );
    }
}
