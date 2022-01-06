<?php

namespace apimatic\jsonmapper;

/**
 * Data class to hold the groups of multiple types
 */
class TypeCombination
{
    /**
     * @var string Name of this typeCombinator group i.e. oneOf/anyOf
     */
    private $groupName;

    /**
     * @var array Array of types or other type combination
     */
    private $types;

    /**
     * @var int Array/Map dimension of this group
     */
    private $dimension;

    /**
     * @param string $groupName
     * @param array $types
     * @param int $dimension
     */
    private function __construct($groupName, $types, $dimension)
    {
        $this->groupName = $groupName;
        $this->types = $types;
        $this->dimension = $dimension;
    }

    /**
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return int
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * @param int $dimension
     */
    public function setDimension($dimension)
    {
        $this->dimension = $dimension;
    }

    /**
     * Decrease the dimension for the given group type by 1
     */
    public function decreaseDimension()
    {
        $this->dimension--;
    }

    /**
     * Decrease the dimension for the given group type by 1
     */
    public function increaseDimension()
    {
        $this->dimension++;
    }

    /**
     * Wrap the given typesGroup string in the TypeCombination class,
     * i.e. getTypes() method will return all the grouped types, and
     * getDimension() will return the dimensions of the current group,
     * and group name can be obtained from getGroupName()
     *
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
    public static function GenerateTypeCombination(
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
                self::insertType($types, $type);
                $type = '';
                continue;
            }
            $type .= $c;
        }
        self::insertType($types, $type);
        return new self($groupName, $types, $dimension);
    }

    /**
     * @param $types array
     * @param $type  string
     */
    private static function insertType(&$types, $type)
    {
        $start = strpos($type, '(');
        if ($start !== false) {
            $end = strrpos($type, ')');
            if ($end !== false) {
                $type = self::GenerateTypeCombination($type, $start, $end);
            }
        }
        if (!empty($type)) {
            array_push($types , $type);
        }
    }

}