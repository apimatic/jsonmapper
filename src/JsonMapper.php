<?php

/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */

namespace apimatic\jsonmapper;

use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Automatically map JSON structures into objects.
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */
class JsonMapper
{
    /**
     * PSR-3 compatible logger object
     *
     * @link http://www.php-fig.org/psr/psr-3/
     * @var  object
     * @see  setLogger()
     */
    protected $logger;

    /**
     * Throw an exception when JSON data contain a property
     * that is not defined in the PHP class
     *
     * @var boolean
     */
    public $bExceptionOnUndefinedProperty = false;

    /**
     * Calls this method on the PHP class when an undefined property
     * is found. This method should receive two arguments, $key
     * and $value for the property key and value. Only works if
     * $bExceptionOnUndefinedProperty is set to false.
     *
     * @var string
     */
    public $sAdditionalPropertiesCollectionMethod = null;

    /**
     * Throw an exception if the JSON data miss a property
     * that is marked with @required in the PHP class
     *
     * @var boolean
     */
    public $bExceptionOnMissingData = false;

    /**
     * If the types of map() parameters shall be checked.
     * You have to disable it if you're using the json_decode "assoc" parameter.
     *
     *     `json_decode($str, false)`
     *
     * @var boolean
     */
    public $bEnforceMapType = true;

    /**
     * Contains user provided map of class names vs their child classes.
     * This is only needed if discriminators are to be used. PHP reflection is not
     * used to get child classes because most code bases use autoloaders where
     * classes are lazily loaded.
     *
     * @var array
     */
    public $arChildClasses = array();

    /**
     * Runtime cache for inspected classes. This is particularly effective if
     * mapArray() is called with a large number of objects
     *
     * @var array property inspection result cache
     */
    protected $arInspectedClasses = array();

    /**
     * Map data all data in $json into the given $object instance.
     *
     * @param object $json             JSON object structure from json_decode()
     * @param object $object           Object to map $json data into
     * @param bool   $forMultipleTypes True if looking to map for multiple types, Default: false
     *
     * @return object Mapped object is returned.
     * @see    mapArray()
     */
    public function map($json, $object, $forMultipleTypes = false)
    {
        if ($this->bEnforceMapType && !is_object($json)) {
            throw new \InvalidArgumentException(
                'JsonMapper::map() requires first argument to be an object'
                . ', ' . gettype($json) . ' given.'
            );
        }
        if (!is_object($object)) {
            throw new \InvalidArgumentException(
                'JsonMapper::map() requires second argument to be an object'
                . ', ' . gettype($object) . ' given.'
            );
        }

        $strClassName = get_class($object);
        $rc = new ReflectionClass($object);
        $strNs = $rc->getNamespaceName();
        $providedProperties = array();
        $additionalPropertiesMethod = $this->getAdditionalPropertiesMethod($rc);

        foreach ($json as $key => $jvalue) {
            // $providedProperties[$key] = true;
            $isAdditional = false;

            // Store the property inspection results so we don't have to do it
            // again for subsequent objects of the same type
            if (!isset($this->arInspectedClasses[$strClassName][$key])) {
                $this->arInspectedClasses[$strClassName][$key]
                    = $this->inspectProperty($rc, $key);
            }

            list($hasProperty, $accessor, $type, $factoryMethod, $typeOfs)
                = $this->arInspectedClasses[$strClassName][$key];

            if ($accessor !== null) {
                $providedProperties[$accessor->getName()] = true;
            }

            if (!$hasProperty) {
                if ($this->bExceptionOnUndefinedProperty) {
                    throw new JsonMapperException(
                        'JSON property "' . $key . '" does not exist'
                        . ' in object of type ' . $strClassName
                    );
                }
                $isAdditional = true;
                $this->log(
                    'info',
                    'Property {property} does not exist in {class}',
                    array('property' => $key, 'class' => $strClassName)
                );
            }

            if ($accessor === null) {
                if ($this->bExceptionOnUndefinedProperty) {
                    throw new JsonMapperException(
                        'JSON property "' . $key . '" has no public setter method'
                        . ' in object of type ' . $strClassName
                    );
                }
                $isAdditional = true;
                $this->log(
                    'info',
                    'Property {property} has no public setter method in {class}',
                    array('property' => $key, 'class' => $strClassName)
                );
            }

            //FIXME: check if type exists, give detailled error message if not
            if ($type === '') {
                throw new JsonMapperException(
                    'Empty type at property "'
                    . $strClassName . '::$' . $key . '"'
                );
            }

            if ($isAdditional) {
                if ($additionalPropertiesMethod !== null) {
                    $additionalPropertiesMethod->invoke($object, $key, $jvalue);
                }
                continue;
            }
            $value = $this->getMappedValue(
                $jvalue,
                $type,
                $typeOfs,
                $factoryMethod,
                $rc->getNamespaceName(),
                $rc->getName(),
                $forMultipleTypes
            );
            $this->setProperty($object, $accessor, $value, $strNs);
        }

        if ($this->bExceptionOnMissingData) {
            $this->checkMissingData($providedProperties, $rc);
        }

        return $object;
    }

    /**
     * Try calling the factory method if exists, otherwise throw JsonMapperException
     *
     * @param $factoryMethod string  factory method in the format "type method()"
     * @param $value         mixed   value to be passed in as param into factory method
     * @param $className     string  className referencing this factory method
     *
     * @return mixed|false
     * @throws JsonMapperException
     */
    private function _callFactoryMethod($factoryMethod, $value, $className)
    {
        $factoryMethod = explode(' ', $factoryMethod)[0];
        if (!is_callable($factoryMethod)) {
            throw new JsonMapperException(//Factory method "NonExistentMethod" referenced by "FactoryMethodWithError" is not callable
                'Factory method "' . $factoryMethod . '" referenced by "' . $className . '" is not callable'
            );
        }
        return call_user_func($factoryMethod, $value);
    }

    /**
     * Get mapped value for a property in an object.
     *
     * @param $jvalue           mixed          Raw normalized data for the property
     * @param $type             string         Type of the data found by inspectProperty()
     * @param $typeOfs          string|null    OneOf/AnyOf types hint found by inspectProperty in maps annotation
     * @param $factoryMethods   string[]|null  Callable factory methods for property
     * @param $namespace        string         Namespace of the class
     * @param $className        string         Name of the class
     * @param $forMultipleTypes bool           True if looking to map for multiple types
     *
     * @return array|false|mixed|object|null
     * @throws JsonMapperException|ReflectionException
     */
    protected function getMappedValue(
        $jvalue,
        $type,
        $typeOfs,
        $factoryMethods,
        $namespace,
        $className,
        $forMultipleTypes
    ) {
        if ($typeOfs) {
            return $this->mapFor($jvalue, $typeOfs, $namespace, $factoryMethods, $className);
        }
        //use factory method generated value if factory provided
        if ($factoryMethods !== null && isset($factoryMethods[0])) {
            return $this->_callFactoryMethod($factoryMethods[0], $jvalue, $className);
        }

        if ($this->isNullable($type)) {
            if ($jvalue === null) {
                return null;
            }
            $type = $this->removeNullable($type);
        }

        if ($type === null || $type === 'mixed' || $type === '') {
            //no given type - simply return the json data
            return $jvalue;
        } else if ($this->isObjectOfSameType($type, $jvalue)) {
            return $jvalue;
        } else if ($this->isSimpleType($type)) {
            settype($jvalue, $type);
            return $jvalue;
        }

        $array = null;
        $subtype = null;
        if (substr($type, -2) == '[]') {
            //array
            $array = array();
            $subtype = substr($type, 0, -2);
        } else if (substr($type, -1) == ']') {
            list($proptype, $subtype) = explode('[', substr($type, 0, -1));
            if (!$this->isSimpleType($proptype)) {
                $proptype = $this->getFullNamespace($proptype, $namespace);
            }
            $array = $this->createInstance($proptype);
        } else if ($type == 'ArrayObject'
            || is_subclass_of($type, 'ArrayObject')
        ) {
            $array = $this->createInstance($type);
        }

        if ($array !== null) {
            if (!$this->isSimpleType($subtype)) {
                $subtype = $this->getFullNamespace($subtype, $namespace);
            }
            if ($jvalue === null) {
                $child = null;
            } else if ($this->isRegisteredType(
                $this->getFullNamespace($subtype, $namespace)
            )
            ) {
                $child = $this->mapClassArray($jvalue, $subtype, $forMultipleTypes);
            } else {
                $child = $this->mapArray($jvalue, $array, $subtype, $forMultipleTypes);
            }
        } else if ($this->isFlatType(gettype($jvalue))) {
            //use constructor parameter if we have a class
            // but only a flat type (i.e. string, int)
            if ($jvalue === null) {
                $child = null;
            } else {
                $type = $this->getFullNamespace($type, $namespace);
                $child = new $type($jvalue);
            }
        } else {
            $type = $this->getFullNamespace($type, $namespace);
            $child = $this->mapClass($jvalue, $type, $forMultipleTypes);
        }

        return $child;
    }

    /**
     * Checks mappings for all types with mappedObject, provided by mappedObjectCallback.
     *
     * @param bool          $oneOf                true if outer array represent OneOf, false if AnyOf
     * @param array         $types                Nested string arrays to hold information for types with oneOf and
     *                                            anyOf mappings, types in the outer array will follow anyOf
     *                                            mapping, while elements in the next inner array will follow oneOf
     *                                            mappings, and the next inner array will again follow anyOf
     *                                            mappings and so on.
     * @param mixed         $json                 Json value for which we have to check for mappings of each of the types.
     * @param string[]|null $factoryMethods       Callable factory methods for property
     * @param string|null   $className            Name of the class
     * @param string        $namespace            Namespace of the class
     * @param callable      $mappedObjectCallback Callback function to be called with each type in provided types, and the
     *                                            inverse of oneOf, this function must return the mappedObject, for which
     *                                            the mapping will be checked, and to ignore any type, it can throw
     *                                            JsonMapperException
     *
     * @return false|mixed|null     Returns the final mapped object after checking for oneOf and anyOf cases
     * @throws JsonMapperException|ReflectionException
     */
    private function _checkMappingsFor(
        $oneOf,
        $types,
        $json,
        $factoryMethods,
        $className,
        $namespace,
        $mappedObjectCallback
    ) {
        $mappedObject = null;
        $mappedWith = '';
        // check json value for each type in types array
        foreach ($types as $type) {
            try {
                if (!is_array($type)) {
                    list($m, $meth) = $this->_isValueOfType($json, $type, $factoryMethods, $namespace, $className);
                    if (!$m) {
                        // skip this type as it can't be mapped on the given json value.
                        continue;
                    }
                    $factoryMethods = isset($meth) ? [$meth] : null;
                }
                $mappedObject = call_user_func($mappedObjectCallback, $type, !$oneOf, $json, $factoryMethods);
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Cannot map more then OneOf') != false) {
                    throw $e; // throw exception only its caused by mapping of more then one types
                }
                continue; // ignore the type if it can't be mapped for given value
            }
            $matchedType = $type;
            if ($oneOf && $mappedWith) {
                // if its oneOf and we have a value that is already mapped, then throw jsonMapperException
                throw new JsonMapperException(
                    'Cannot map more then OneOf { ' . $this->_flattenJoin($matchedType) . ' and ' .
                    $this->_flattenJoin($mappedWith) . ' } on: ' . json_encode($json)
                );
            }
            $mappedWith = $matchedType;
            if (!$oneOf) {
                break; // break if its anyOf, and we got a type matched with the given json
            }
        }
        if (!$mappedWith) {
            throw new JsonMapperException(
                'Unable to map AnyOf ' . $this->_flattenJoin($types) . ' on: ' . json_encode($json)
            );
        }
        return $mappedObject;
    }

    /**
     * Returns joined types in $subject array and its nested arrays with ','.
     *
     * @param array|string $subject Array of types or a single type.
     *
     * @return string
     */
    private function _flattenJoin($subject)
    {
        if (is_array($subject)) {
            $flatten = [];
            array_walk_recursive(
                $subject, function ($a) use (&$flatten) {
                    $flatten[] = $a; 
                }
            );
            return '(' . join(', ', $flatten) . ')';
        }
        return $subject;
    }

    /**
     * Map the data in $json into the specified $types.
     *
     * @param mixed         $json           Raw normalized data for the property
     * @param string|array  $types          Nested string arrays to hold information for types with oneOf and
     *                                      anyOf mappings, types in the outer array will follow anyOf
     *                                      mapping, while elements in the next inner array will follow oneOf
     *                                      mappings, and the next inner array will again follow anyOf
     *                                      mappings and so on. Or $types should be some typeHint string like
     *                                      OneOf(types..) or AnyOf(types..)
     * @param string        $namespace      Namespace of the class
     * @param string[]|null $factoryMethods Callable factory methods for property
     * @param string|null   $className      Name of the class
     * @param bool          $oneOf          If True, then check $typeOfs for oneOf, otherwise check for anyOf.
     *
     * @return array|mixed|object
     * @throws JsonMapperException|ReflectionException
     */
    public function mapFor(
        $json,
        $types,
        $namespace = '',
        $factoryMethods = null,
        $className = null,
        $oneOf = false
    ) {
        if (is_string($types)) {
            if (strpos($types, 'Of(') != false) {
                list($types, $oneOf) = $this->_extractTypeOfs($types);
            } else {
                $types = [$types];
            }
        }
        return $this->_checkMappingsFor(
            $oneOf,
            $types,
            $json,
            $factoryMethods,
            $className,
            $namespace,
            function ($type, $oneOf, $json, $factoryMethods) use ($namespace, $className) {
                return is_array($type) ?
                    $this->mapFor($json, $type, $namespace, $factoryMethods, $className, $oneOf) :
                    $this->getMappedValue($json, $type, null, $factoryMethods, $namespace, $className, true);
            }
        );
    }

    /**
     * Checks types against the value.
     *
     * @param mixed         $value          param's value
     * @param string        $type           type defined in param's typehint
     * @param string[]|null $factoryMethods Callable factory methods for property
     * @param string        $namespace      Namespace of the class
     * @param string        $className      Class refrencing the factory methods
     *
     * @return array array(bool $matched, ?string $method) $matched represents if Type matched with value,
     *               $method represents the selected factory method (if any)
     * @throws ReflectionException
     * @throws JsonMapperException
     */
    private function _isValueOfType($value, $type, $factoryMethods, $namespace, $className)
    {
        if (isset($factoryMethods)) {
            $methodFound = false;
            foreach ($factoryMethods as $method) {
                if (isset($method) && explode(' ', $method)[1] == $type) {
                    $methodFound = true;
                    if (version_compare(phpversion(), '7.0', '<')) {
                        // if php version is less than 7.0
                        $this->_callFactoryMethod($method, $value, $className);
                    } else {
                        try {
                            $this->_callFactoryMethod($method, $value, $className);
                        } catch (\Throwable $e){
                            continue; // continue if method not accessible
                        }
                    }
                    return array(true, $method); // return true immediatly if method found, is accessible.
                }
            }
            if ($methodFound) {
                // if method with type found but not accessible
                return array(false, null);
            }
        }
        if (substr($type, -2) == '[]') {
            // if type is array like string[] or int[]
            if (is_array($value)) {
                // if value is also of array type
                $type = substr($type, 0, -2);
                foreach ($value as $element) {
                    if (!$this->_isValueOfType($element, $type, null, $namespace, '')[0]) {
                        return array(false, null); // false if any element is not of same type
                    }
                }
                return array(true, null); // true only if all elements in the array are of same type
            }
            return array(false, null); // false if type was array but value is not
        }
        // Check for simple types
        $matched = $type == 'mixed'
            || ($type == 'string' && is_string($value))
            || ($type == 'bool' && is_bool($value))
            || ($type == 'int' && is_int($value))
            || ($type == 'float' && is_float($value))
            || ($type == 'array' && (is_array($value) || is_object($value)))
            || ($type == 'null' && is_null($value));

        // Check for complex types if not matched with simple types
        if (!$matched && $type != 'null' && !$this->isSimpleType($type) && is_object($value)) {
            $matched = true;
            $rc = new ReflectionClass($this->getFullNamespace($type, $namespace));
            if ($this->getDiscriminator($rc)) {
                // if there is a discriminator?
                if (!$this->getDiscriminatorMatch($value, $rc)) {
                    // check if discriminator didn't match
                    $matched = false;
                }
            } // keep ($matched: true) if there is no discriminator
        }
        return array($matched, null);
    }

    /**
     * Returns an array of (types nested array) and (isOneOf/isAnyOf) type.
     *
     * @param string $typeOfHint param's types hint from maps annotation
     *
     * @return array
     */
    private function _extractTypeOfs($typeOfHint)
    {
        $oneOf = strpos($typeOfHint, 'OneOf(') === 0;
        $types = str_replace(['OneOf(','AnyOf('], '[', $typeOfHint);
        $types = str_replace(')', ']', $types);
        return array(json_decode($types), $oneOf);
    }

    /**
     * Map all data in $json into a new instance of $type class.
     *
     * @param object|null $json             JSON object structure from json_decode()
     * @param string      $type             The type of class instance to map into.
     * @param bool        $forMultipleTypes True if looking to map for multiple types, Default: false
     *
     * @return object|null      Mapped object is returned.
     * @throws ReflectionException|JsonMapperException
     * @see    mapClassArray()
     */
    public function mapClass($json, $type, $forMultipleTypes = false)
    {
        if ($json === null) {
            return null;
        }

        if (!is_object($json)) {
            throw new \InvalidArgumentException(
                'JsonMapper::mapClass() requires first argument to be an object'
                . ', ' . gettype($json) . ' given.'
            );
        }

        $ttype = ltrim($type, "\\");

        if (!class_exists($type)) {
            throw new \InvalidArgumentException(
                'JsonMapper::mapClass() requires second argument to be a class name'
                . ', ' . $type . ' given.'
            );
        }

        $rc = new ReflectionClass($ttype);
        //try and find a class with matching discriminator
        $matchedRc = $this->getDiscriminatorMatch($json, $rc);
        //otherwise fallback to an instance of $type class
        if ($matchedRc === null) {
            $instance = $this->createInstance($ttype, $json, $forMultipleTypes);
        } else {
            $instance = $this->createInstance($matchedRc->getName(), $json, $forMultipleTypes);
        }


        return $this->map($json, $instance, $forMultipleTypes);
    }

    /**
     * Get class instance that best matches the class
     *
     * @param object|null     $json JSON object structure from json_decode()
     * @param ReflectionClass $rc   Class to get instance of. This method
     *                              will try to first match the
     *                              discriminator field with the
     *                              discriminator value of the current
     *                              class or its child class. If no
     *                              matches is found, then the current
     *                              class's instance is returned.
     *
     * @return ReflectionClass|null Object instance if match is found.
     * @throws ReflectionException
     */
    protected function getDiscriminatorMatch($json, $rc)
    {
        $discriminator = $this->getDiscriminator($rc);
        if ($discriminator) {
            list($fieldName, $fieldValue) = $discriminator;
            if (isset($json->{$fieldName}) && $json->{$fieldName} === $fieldValue) {
                return $rc;
            }
            if (!$this->isRegisteredType($rc->name)) {
                return null;
            }
            foreach ($this->getChildClasses($rc) as $clazz) {
                $childRc = $this->getDiscriminatorMatch($json, $clazz);
                if ($childRc) {
                    return $childRc;
                }
            }
        }
        return null;
    }

    /**
     * Get discriminator info
     *
     * @param ReflectionClass $rc ReflectionClass of class to inspect
     *
     * @return array|null          An array with discriminator arguments
     *                             Element 1 is discriminator field name
     *                             and element 2 is discriminator value.
     */
    protected function getDiscriminator($rc)
    {
        $annotations = $this->parseAnnotations($rc->getDocComment());
        $annotationInfo = array();
        if (isset($annotations['discriminator'])) {
            $annotationInfo[0] = trim($annotations['discriminator'][0]);
            if (isset($annotations['discriminatorType'])) {
                $annotationInfo[1] = trim($annotations['discriminatorType'][0]);
            } else {
                $annotationInfo[1] = $rc->getShortName();
            }
            return $annotationInfo;
        }
        return null;
    }

    /**
     * Get child classes from a ReflectionClass
     *
     * @param ReflectionClass $rc ReflectionClass of class to inspect
     *
     * @return ReflectionClass[]  ReflectionClass instances for child classes
     * @throws ReflectionException
     */
    protected function getChildClasses($rc)
    {
        $children  = array();
        foreach ($this->arChildClasses[$rc->name] as $class) {
            $child = new ReflectionClass($class);
            if ($child->isSubclassOf($rc)) {
                $children[] = $child;
            }
        }
        return $children;
    }

    /**
     * Convert a type name to a fully namespaced type name.
     *
     * @param string $type  Type name (simple type or class name)
     * @param string $strNs Base namespace that gets prepended to the type name
     *
     * @return string Fully-qualified type name with namespace
     */
    protected function getFullNamespace($type, $strNs)
    {
        if (\is_string($type) && $type !== '' && $type[0] != '\\') {
            //create a full qualified namespace
            if ($strNs != '') {
                $type = '\\' . $strNs . '\\' . $type;
            }
        }
        return $type;
    }

    /**
     * Check required properties exist in json
     *
     * @param array           $providedProperties array with json properties
     * @param ReflectionClass $rc                 Reflection class to check
     *
     * @return void
     * @throws JsonMapperException
     */
    protected function checkMissingData($providedProperties, ReflectionClass $rc)
    {
        foreach ($rc->getProperties() as $property) {
            $rprop = $rc->getProperty($property->name);
            $docblock = $rprop->getDocComment();
            $annotations = $this->parseAnnotations($docblock);
            if (isset($annotations['required'])
                && !isset($providedProperties[$property->name])
            ) {
                throw new JsonMapperException(
                    'Required property "' . $property->name . '" of class '
                    . $rc->getName()
                    . ' is missing in JSON data'
                );
            }
        }
    }

    /**
     * Get additional properties setter method for the class.
     *
     * @param ReflectionClass $rc Reflection class to check
     *
     * @return ReflectionMethod    Method or null if disabled.
     */
    protected function getAdditionalPropertiesMethod(ReflectionClass $rc)
    {
        if ($this->bExceptionOnUndefinedProperty === false
            && $this->sAdditionalPropertiesCollectionMethod !== null
        ) {
            $additionalPropertiesMethod = null;
            try {
                $additionalPropertiesMethod
                    = $rc->getMethod($this->sAdditionalPropertiesCollectionMethod);
                if (!$additionalPropertiesMethod->isPublic()) {
                    throw new  \InvalidArgumentException(
                        $this->sAdditionalPropertiesCollectionMethod .
                        " method is not public on the given class."
                    );
                }
                if ($additionalPropertiesMethod->getNumberOfParameters() < 2) {
                    throw new  \InvalidArgumentException(
                        $this->sAdditionalPropertiesCollectionMethod .
                        ' method does not receive two args, $key and $value.'
                    );
                }
            } catch (\ReflectionException $e) {
                throw new  \InvalidArgumentException(
                    $this->sAdditionalPropertiesCollectionMethod .
                    " method is not available on the given class."
                );
            }
            return $additionalPropertiesMethod;
        } else {
            return null;
        }
    }

    /**
     * Map an array
     *
     * @param array         $json             JSON array structure from json_decode()
     * @param mixed         $array            Array or ArrayObject that gets filled with
     *                                        data from $json
     * @param string|object $class            Class name for children objects.
     *                                        All children will get mapped
     *                                        onto this type. Supports class
     *                                        names and simple types like
     *                                        "string".
     * @param bool          $forMultipleTypes True if looking to map for multiple types, Default: false
     *
     * @return mixed Mapped $array is returned
     */
    public function mapArray($json, $array, $class = null, $forMultipleTypes = false)
    {
        foreach ($json as $key => $jvalue) {
            if ($class === null) {
                $array[$key] = $jvalue;
            } else if ($this->isFlatType(gettype($jvalue))) {
                //use constructor parameter if we have a class
                // but only a flat type (i.e. string, int)
                if ($jvalue === null) {
                    $array[$key] = null;
                } else {
                    if ($this->isSimpleType($class)) {
                        settype($jvalue, $class);
                        $array[$key] = $jvalue;
                    } else {
                        $array[$key] = new $class($jvalue);
                    }
                }
            } else {
                $instance = $this->createInstance($class, $jvalue, $forMultipleTypes);
                $array[$key] = $this->map($jvalue, $instance, $forMultipleTypes);
            }
        }
        return $array;
    }

    /**
     * Map an array
     *
     * @param array|null $jsonArray        JSON array structure from json_decode()
     * @param string     $type             Class name
     * @param bool       $forMultipleTypes True if looking to map for multiple types, Default: false
     *
     * @return array|null           A new array containing object of $type
     *                              which is mapped from $jsonArray
     * @throws ReflectionException|JsonMapperException
     */
    public function mapClassArray($jsonArray, $type, $forMultipleTypes = false)
    {
        if ($jsonArray === null) {
            return null;
        }

        $array = array();
        foreach ($jsonArray as $key => $jvalue) {
            $array[$key] = $this->mapClass($jvalue, $type, $forMultipleTypes);
        }

        return $array;
    }

    /**
     * Try to find out if a property exists in a given class.
     * Checks property first, falls back to setter method.
     *
     * @param ReflectionClass $rc   Reflection class to check
     * @param string          $name Property name
     *
     * @return array First value: if the property exists
     *               Second value: the accessor to use (
     *                 ReflectionMethod or ReflectionProperty, or null)
     *               Third value: type of the property
     *               Fourth value: factory method
     */
    protected function inspectProperty(ReflectionClass $rc, $name)
    {
        $rmeth = null;
        $annotations = [];
        $typeOfs = null;
        foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $annotations = $this->parseAnnotations($method->getDocComment());
            if ($name === $this->getMapAnnotationFromParsed($annotations)) {
                $rmeth = $method;
                $typeOfs = $this->getMapAnnotationFromParsed($annotations, 1);
                break;
            }
        }

        if ($rmeth === null) {
            //try setter method
            $setter = 'set' . str_replace(
                ' ', '', ucwords(str_replace('_', ' ', $name))
            );
            if ($rc->hasMethod($setter)) {
                $rmeth = $rc->getMethod($setter);
                $annotations = $this->parseAnnotations($rmeth->getDocComment());
            }
        }
        if ($rmeth !== null && $rmeth->isPublic()) {
            $type = null;
            $factoryMethod = null;
            $rparams = $rmeth->getParameters();
            if (count($rparams) > 0) {
                $type = $this->getParameterType($rparams[0]);
            }

            if (($type === null || $type === 'array' || $type === 'array|null')
                && isset($annotations['param'][0])
            ) {
                list($type) = explode(' ', trim($annotations['param'][0]));
            }

            //support "@factory method_name"
            if (isset($annotations['factory'])) {
                $factoryMethod = $annotations['factory'];
            }

            return array(true, $rmeth, $type, $factoryMethod, $typeOfs);
        }

        $rprop = null;
        // check for @maps annotation for hints
        foreach ($rc->getProperties(\ReflectionProperty::IS_PUBLIC) as $p) {
            $mappedName = $this->getMapAnnotation($p);
            if ($mappedName !== null && $name == $mappedName) {
                $typeOfs = $this->getMapAnnotation($p, 1);
                $rprop = $p;
                break;
            }
        }

        //now try to set the property directly
        if ($rprop === null) {
            if ($rc->hasProperty($name)
                && $this->getMapAnnotation($rc->getProperty($name)) === null
            ) {
                $rprop = $rc->getProperty($name);
            } else {
                //case-insensitive property matching
                foreach ($rc->getProperties(\ReflectionProperty::IS_PUBLIC) as $p) {
                    if ((strcasecmp($p->name, $name) === 0)
                        && $this->getMapAnnotation($p) === null
                    ) {
                        $rprop = $p;
                        break;
                    }
                }
            }
        }

        if ($rprop !== null) {
            if ($rprop->isPublic()) {
                $docblock      = $rprop->getDocComment();
                $annotations   = $this->parseAnnotations($docblock);
                $type          = null;
                $factoryMethod = null;

                //support "@var type description"
                if (isset($annotations['var'][0])) {
                    list($type) = explode(' ', $annotations['var'][0]);
                }

                //support "@factory method_name"
                if (isset($annotations['factory'])) {
                    $factoryMethod = $annotations['factory'];
                }

                return array(true, $rprop, $type, $factoryMethod, $typeOfs);
            } else {
                //no setter, private property
                return array(true, null, null, null, $typeOfs);
            }
        }

        //no setter, no property
        return array(false, null, null, null, $typeOfs);
    }

    /**
     * Get Phpdoc typehint for parameter
     *
     * @param \ReflectionParameter $param ReflectionParameter instance for parameter
     *
     * @return string|null
     */
    protected function getParameterType(\ReflectionParameter $param)
    {
        if (PHP_VERSION_ID < 80000 && null !== $class = $param->getClass()) {
            return "\\" . $class->getName();
        }

        if (is_callable([$param, 'hasType']) && $param->hasType()) {
            $type = $param->getType();
            if ($type->isBuiltIn()) {
                $typeName = static::reflectionTypeToString($type);
            } else {
                $typeName = "\\" . static::reflectionTypeToString($type);
            }
            return $type->allowsNull() ? "$typeName|null" : $typeName;
        }

        return null;
    }

    /**
     * Get name for a ReflectionType instance
     *
     * @param \ReflectionType $type Reflection type instance
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    protected static function reflectionTypeToString($type)
    {
        if (\class_exists('ReflectionNamedType')
            && $type instanceof \ReflectionNamedType
        ) {
            return $type->getName();
        } else {
            return (string)$type;
        }
    }

    /**
     * Get map annotation value for a property
     *
     * @param object $property Property of a class
     * @param int    $index    Position of value to be fetched
     *
     * @return string|null     Map annotation value
     */
    protected function getMapAnnotation($property, $index = 0)
    {
        $annotations = $this->parseAnnotations($property->getDocComment());
        return $this->getMapAnnotationFromParsed($annotations, $index);
    }

    /**
     * Get map annotation value from a parsed annotation list
     *
     * @param array $annotations Parsed annotation list
     * @param int   $index       Position of value to be fetched
     *
     * @return string|null       Map annotation value
     */
    protected function getMapAnnotationFromParsed($annotations, $index = 0)
    {
        if (isset($annotations['maps'][0])) {
            $mapsName = explode(' ', $annotations['maps'][0]);
            if (isset($mapsName[$index])) {
                return $mapsName[$index];
            }
        }
        return null;
    }

    /**
     * Set a property on a given object to a given value.
     *
     * Checks if the setter or the property are public are made before
     * calling this method.
     *
     * @param object $object   Object to set property on
     * @param object $accessor ReflectionMethod or ReflectionProperty
     * @param mixed  $value    Value of property
     *
     * @return void
     */
    protected function setProperty(
        $object, $accessor, $value
    ) {
        if ($accessor instanceof \ReflectionProperty) {
            $object->{$accessor->getName()} = $value;
        } else {
            $object->{$accessor->getName()}($value);
        }
    }

    /**
     * Create a new object of the given type.
     *
     * @param string $class            Class name to instantiate
     * @param object $jobject          Use jobject for constructor args
     * @param bool   $forMultipleTypes True if looking to map for multiple types, Default: false
     *
     * @return object Freshly created object
     * @throws ReflectionException|JsonMapperException
     */
    protected function createInstance($class, &$jobject = null, $forMultipleTypes = false)
    {
        $rc = new ReflectionClass($class);
        $ctor = $rc->getConstructor();
        if ($ctor === null
            || 0 === $ctorReqParamsCount = $ctor->getNumberOfRequiredParameters()
        ) {
            return new $class();
        } else if ($jobject === null) {
            throw new JsonMapperException(
                "$class class requires " . $ctor->getNumberOfRequiredParameters()
                . " arguments in constructor but none provided"
            );
        }

        $ctorRequiredParams = array_slice(
            $ctor->getParameters(),
            0,
            $ctorReqParamsCount
        );
        $ctorRequiredParamsName = array_map(
            function (\ReflectionParameter $param) {
                return $param->getName();
            }, $ctorRequiredParams
        );
        $ctorRequiredParams = array_combine(
            $ctorRequiredParamsName,
            $ctorRequiredParams
        );
        $ctorArgs = [];

        foreach ($jobject as $key => $jvalue) {
            if (count($ctorArgs) === $ctorReqParamsCount) {
                break;
            }

            // Store the property inspection results so we don't have to do it
            // again for subsequent objects of the same type
            if (!isset($this->arInspectedClasses[$class][$key])) {
                $this->arInspectedClasses[$class][$key]
                    = $this->inspectProperty($rc, $key);
            }

            list($hasProperty, $accessor, $type, $factoryMethod, $typeOfs)
                = $this->arInspectedClasses[$class][$key];

            if (!$hasProperty) {
                // if no matching property or setter method found
                if (isset($ctorRequiredParams[$key])) {
                    $rp = $ctorRequiredParams[$key];
                    $jtype = null;
                } else {
                    continue;
                }
            } else if ($accessor instanceof \ReflectionProperty) {
                // if a property was found
                if (isset($ctorRequiredParams[$accessor->getName()])) {
                    $rp = $ctorRequiredParams[$accessor->getName()];
                    $jtype = $type;
                } else {
                    continue;
                }
            } else {
                // if a setter method was found
                $methodName = $accessor->getName();
                $methodName = substr($methodName, 0, 3) === 'set' ?
                    lcfirst(substr($methodName, 3)) : $methodName;
                if (isset($ctorRequiredParams[$methodName])) {
                    $rp = $ctorRequiredParams[$methodName];
                    $jtype = $type;
                } else {
                    continue;
                }
            }

            $ttype = $this->getParameterType($rp);
            if (($ttype !== null && $ttype !== 'array' && $ttype !== 'array|null')
                || $jtype === null
            ) {
                // when $ttype is too generic, fallback to $jtype
                $jtype = $ttype;
            }

            $ctorArgs[$rp->getPosition()] = $this->getMappedValue(
                $jvalue,
                $jtype,
                $typeOfs,
                $factoryMethod,
                $rc->getNamespaceName(),
                $rc->getName(),
                $forMultipleTypes
            );

            if (!$forMultipleTypes) {
                unset($jobject->{$key});
            }
            unset($ctorRequiredParamsName[$rp->getPosition()]);
        }

        if (count($ctorArgs) < $ctorReqParamsCount) {
            throw new JsonMapperException(
                "Could not find required constructor arguments for $class: "
                . \implode(", ", $ctorRequiredParamsName)
            );
        }

        ksort($ctorArgs);
        return $rc->newInstanceArgs($ctorArgs);
    }

    /**
     * Checks if the given type is a "simple type"
     *
     * @param string $type type name from gettype()
     *
     * @return boolean True if it is a simple PHP type
     */
    protected function isSimpleType($type)
    {
        return $type == 'string'
            || $type == 'boolean' || $type == 'bool'
            || $type == 'integer' || $type == 'int'   || $type == 'float'
            || $type == 'double'  || $type == 'array' || $type == 'object';
    }

    /**
     * Checks if the object is of this type or has this type as one of its parents
     *
     * @param string $type  class name of type being required
     * @param mixed  $value Some PHP value to be tested
     *
     * @return boolean True if $object has type of $type
     */
    protected function isObjectOfSameType($type, $value)
    {
        if (false === is_object($value)) {
            return false;
        }

        return is_a($value, $type);
    }

    /**
     * Checks if the given type is a type that is not nested
     * (simple type except array and object)
     *
     * @param string $type type name from gettype()
     *
     * @return boolean True if it is a non-nested PHP type
     */
    protected function isFlatType($type)
    {
        return $type == 'NULL'
            || $type == 'string'
            || $type == 'boolean' || $type == 'bool'
            || $type == 'integer' || $type == 'int'
            || $type == 'double';
    }

    /**
     * Is type registered with mapper
     *
     * @param string $type Class name
     *
     * @return boolean     True if registered with $this->arChildClasses
     */
    protected function isRegisteredType($type)
    {
        return isset($this->arChildClasses[ltrim($type, "\\")]);
    }

    /**
     * Checks if the given type is nullable
     *
     * @param string $type type name from the phpdoc param
     *
     * @return boolean True if it is nullable
     */
    protected function isNullable($type)
    {
        return stripos('|' . $type . '|', '|null|') !== false;
    }

    /**
     * Remove the 'null' section of a type
     *
     * @param string $type type name from the phpdoc param
     *
     * @return string The new type value
     */
    protected function removeNullable($type)
    {
        return substr(
            str_ireplace('|null|', '|', '|' . $type . '|'),
            1, -1
        );
    }

    /**
     * Copied from PHPUnit 3.7.29, Util/Test.php
     *
     * @param string $docblock Full method docblock
     *
     * @return array
     */
    protected static function parseAnnotations($docblock)
    {
        $annotations = array();
        // Strip away the docblock header and footer
        // to ease parsing of one line annotations
        $docblock = substr($docblock, 3, -2);

        $re = '/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m';
        if (preg_match_all($re, $docblock, $matches)) {
            $numMatches = count($matches[0]);

            for ($i = 0; $i < $numMatches; ++$i) {
                $annotations[$matches['name'][$i]][] = $matches['value'][$i];
            }
        }

        return $annotations;
    }

    /**
     * Log a message to the $logger object
     *
     * @param string $level   Logging level
     * @param string $message Text to log
     * @param array  $context Additional information
     *
     * @return null
     */
    protected function log($level, $message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger PSR-3 compatible logger object
     *
     * @return null
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
?>
