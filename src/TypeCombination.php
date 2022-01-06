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
{
    /**
     * Name of this typeCombinator group i.e. oneOf/anyOf.
     *
     * @var string
     */
    private $_groupName;

    /**
     * Array of types or other type combination
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
     * Private constructor for TypeCombination class
     *
     * @param string $groupName group name value
     * @param array  $types     types value
     * @param int    $dimension dimension value
     */
    private function __construct($groupName, $types, $dimension)
    {
        $this->_groupName = $groupName;
        $this->_types = $types;
        $this->_dimension = $dimension;
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
     * Array of types or other type combination
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->_types;
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
     * Wrap the given typesGroup string in the TypeCombination class,
     * i.e. getTypes() method will return all the grouped types, and
     * getDimension() will return the dimensions of the current group,
     * and group name can be obtained from getGroupName()
     *
     * @param string    $typesGroup Format of multiple types i.e. oneOf(int,bool)[]
     *                              or onyOf(int[],bool,anyOf(string,float)[],...),
     *                              here [] represents dimensions of each type, and
     *                              oneOf/anyOf and group names, while default group
     *                              name is anyOf.
     * @param int|false $start      Starting index of types in group, default: false.
     * @param int|false $end        Ending index of types in group, default: false.
     *
     * @return TypeCombination
     */
    public static function generateTypeCombination(
        $typesGroup,
        $start = false,
        $end = false
    ) {
        $groupName = 'anyOf';
        $dimension = 0;

        $start = $start == false ? strpos($typesGroup, '(') : $start;
        $end = $end == false ? strrpos($typesGroup, ')') : $end;
        if ($start !== false && $end !== false) {
            $name = substr($typesGroup, 0, $start);
            $groupName = empty($name) ? $groupName : $name;
            $dimension = substr_count($typesGroup, '[]', $end);
            $typesGroup = substr($typesGroup, $start + 1, -2 * $dimension - 1);
        }
        $types = [];
        $type = '';
        $groupCount = 0;
        foreach (str_split($typesGroup) as $c) {
            if ($c == '(') {
                $groupCount++;
            }
            if ($c == ')') {
                $groupCount--;
            }
            if ($c == ',' && $groupCount == 0) {
                self::_insertType($types, $type);
                $type = '';
                continue;
            }
            $type .= $c;
        }
        self::_insertType($types, $type);
        return new self($groupName, $types, $dimension);
    }

    /**
     * Insert the type in the types array which is passed by reference,
     * Also check if type is not empty
     *
     * @param $types array
     * @param $type  string
     *
     * @return void
     */
    private static function _insertType(&$types, $type)
    {
        $start = strpos($type, '(');
        if ($start !== false) {
            $end = strrpos($type, ')');
            if ($end !== false) {
                $type = self::generateTypeCombination($type, $start, $end);
            }
        }
        if (!empty($type)) {
            array_push($types, $type);
        }
    }

}
