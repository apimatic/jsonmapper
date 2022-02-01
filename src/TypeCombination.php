<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Asad Ali <asad.ali@apimatic.io>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */
namespace apimatic\jsonmapper;

/**
 * Data class to hold the groups of multiple types.
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Asad Ali <asad.ali@apimatic.io>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */
class TypeCombination
{ // make it private
    /**
     * Name of this typeCombinator group i.e. oneOf/anyOf.
     *
     * @var string
     */
    private $_groupName;

    /**
     * Array of string types or TypeCombination objects
     *
     * @var array
     */
    private $_types;

    /**
     * Array/Map dimension of this group
     *
     * @var int
     */
    private $_dimension;

    /**
     * A list of factory methods to deserialize the given object,
     * for one of the wrapped types in this group
     *
     * @var string[]
     */
    private $_deserializers;

    /**
     * Private constructor for TypeCombination class
     *
     * @param string   $groupName     group name value
     * @param array    $types         types value
     * @param int      $dimension     dimension value
     * @param string[] $deserializers deserializers value
     */
    private function __construct($groupName, $types, $dimension, $deserializers)
    {
        $this->_groupName = $groupName;
        $this->_types = $types;
        $this->_dimension = $dimension;
        $this->_deserializers = $deserializers;
    }

    /**
     * Name of this typeCombinator group i.e. oneOf/anyOf.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->_groupName;
    }

    /**
     * Array of string types or TypeCombination objects
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->_types;
    }

    /**
     * A list of factory methods to deserialize the given object,
     * for one of the wrapped types in this group
     *
     * @return string[]
     */
    public function getDeserializers()
    {
        return $this->_deserializers;
    }

    /**
     * Array/Map dimension of this group
     *
     * @return int
     */
    public function getDimension()
    {
        return $this->_dimension;
    }

    /**
     * Decrease the dimension for the given group type by 1
     *
     * @return void
     */
    public function decreaseDimension()
    {
        $this->_dimension--;
    }

    /**
     * Increase the dimension for the given group type by 1
     *
     * @return void
     */
    public function increaseDimension()
    {
        $this->_dimension++;
    }

    /**
     * Converts the given typeCombination into its string format.
     *
     * @param TypeCombination|string $typeCombination Combined types/Single type.
     *
     * @return string
     */
    public static function generateTypeString($typeCombination)
    {
        if (is_string($typeCombination)) {
            return $typeCombination;
        }
        $flatten = [];
        array_map(
            function ($a) use (&$flatten) {
                $flatten[] = self::generateTypeString($a);
            },
            $typeCombination->getTypes()
        );
        $dimension = $typeCombination->getDimension();
        $dimensionString = '';
        while ($dimension > 0) {
            $dimensionString .= '[]';
            $dimension--;
        }
        return '(' . join(',', $flatten) . ')' . $dimensionString;
    }

    /**
     * Wrap the given typeGroup string in the TypeCombination class,
     * i.e. getTypes() method will return all the grouped types, and
     * getDimension() will return the dimensions of the current group,
     * while deserializing factory methods can be obtained by
     * getDeserializers() and group name can be obtained from getGroupName()
     *
     * @param string    $typeGroup    Format of multiple types i.e. oneOf(int,bool)[]
     *                                or onyOf(int[],bool,anyOf(string,float)[],...),
     *                                here [] represents dimensions of each type, and
     *                                oneOf/anyOf are group names, while default
     *                                group name is anyOf.
     * @param string[]  $deserializer Callable factory methods for the property
     * @param int|false $start        Start index of types in group, default: false.
     * @param int|false $end          Ending index of types in group, default: false.
     *
     * @return TypeCombination
     */
    public static function generateTypeCombination(
        $typeGroup,
        $deserializer,
        $start = false,
        $end = false
    ) {
        $groupName = 'anyOf';
        $dimension = 0;

        $start = $start == false ? strpos($typeGroup, '(') : $start;
        $end = $end == false ? strrpos($typeGroup, ')') : $end;
        if ($start !== false && $end !== false) {
            $name = substr($typeGroup, 0, $start);
            $groupName = empty($name) ? $groupName : $name;
            $dimension = substr_count($typeGroup, '[]', $end);
            $typeGroup = substr($typeGroup, $start + 1, -2 * $dimension - 1);
        }
        $types = [];
        $type = '';
        $groupCount = 0;
        foreach (str_split($typeGroup) as $c) {
            if ($c == '(') {
                $groupCount++;
            }
            if ($c == ')') {
                $groupCount--;
            }
            if ($c == ',' && $groupCount == 0) {
                self::_insertType($types, $type, $deserializer);
                $type = '';
                continue;
            }
            $type .= $c;
        }
        self::_insertType($types, $type, $deserializer);
        return new self($groupName, $types, $dimension, $deserializer);
    }

    /**
     * Insert the type in the types array which is passed by reference,
     * Also check if type is not empty
     *
     * @param array    $types         types array reference
     * @param string   $type          type to be inserted
     * @param string[] $deserializers deserializer for the type group
     *
     * @return void
     */
    private static function _insertType(&$types, $type, $deserializers)
    {
        $start = strpos($type, '(');
        $end = strrpos($type, ')');
        if ($start !== false && $end !== false) {
            $type = self::generateTypeCombination(
                $type,
                $deserializers,
                $start,
                $end
            );
        }
        if (!empty($type)) {
            array_push($types, $type);
        }
    }

}
