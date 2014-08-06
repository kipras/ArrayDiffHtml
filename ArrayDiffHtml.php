<?php

/**
 * Class ArrayDiffHtml
 *
 * PHP class for pretty-printing the difference between two arrays/objects using HTML, CSS and JavaScript,
 * providing buttons to expand/collapse each level.
 *
 * Usage:
 *      ArrayDiffHtml::diff($first, $second, [$strictEquality], [$opt])
 *
 * Options can be passed as an associative array in the $opt parameter.
 * Available options:
 *      - title1: Title of the $first array (default = 'First')
 *      - title2: Title of the $second array (default = 'Second')
 *      - noSecond: If there is no second array (we are only printing the contents of the first array
 *          and no comparison should be done) - this should be set to True. (default = False)
 *
 * If this file is run directly - it runs tests. However, tests results need to be checked manually.
 *
 * Requirements:
 *      PHP >= 5.3 (uses static:: keyword)
 *
 * @version 1.0
 */
class ArrayDiffHtml
{
    /**
     * @param mixed $first
     * @param mixed $second
     * @param bool $strictEquality Whether to compare values using strict equality (=== instead of ==), by default
     * non-strict equality is used
     * @param array $opt Options
     * @return string
     */
    public static function diff($first, $second, $strictEquality = FALSE, $opt = Array())
    {
        $opt = array_merge(Array(
                'title1' => 'First',
                'title2' => 'Second',
                'noSecond' => FALSE,
            ), $opt);

        $first = static::toArray($first);
        $second = static::toArray($second);

        static $_diffIndex = 0;
        $_diffIndex++;

        $divId = "__arrayDiffHtml_{$_diffIndex}";

        ob_start();
        ?>

        <style type="text/css">
            /*<![CDATA[*/
            .__arrayDiffHtml {
                font-family:arial;

            }
            .__arrayDiffHtml table {
                border-collapse: collapse;
            }

            .__arrayDiffHtml,
            .__arrayDiffHtml table {
                font-size:11px;
            }

            .__arrayDiffHtml table,
            .__arrayDiffHtml table td,
            .__arrayDiffHtml table th {
                border:1px solid #000000;
            }

            .__arrayDiffHtml table td,
            .__arrayDiffHtml table th {
                vertical-align:top;
                text-align:left;
            }

            .__arrayDiffHtml table tr.__arrayDiffHtmlRowEqual {
                background-color:#6F6;
            }

            .__arrayDiffHtml table tr.__arrayDiffHtmlRowEqualButTypeDifferent {
                background-color:#CFC;
            }

            .__arrayDiffHtml table tr.__arrayDiffHtmlRowNotEqual {
                background-color:#FCC;
            }

            .__arrayDiffHtml td.__arrayDiffHtmlNoValue {
                background-color:#F88;
            }

            .__arrayDiffHtml .__arrayDiffHtmlPlus,
            .__arrayDiffHtml .__arrayDiffHtmlMinus {
                border:1px solid #666666;
                font-size:20px;
                line-height:12px;
                cursor:pointer;
                text-align:center;
            }
            .__arrayDiffHtml .__arrayDiffHtmlMinus span {
                position:relative;
                top:-2px;
            }

            /*]]>*/
        </style>

        <div id="<?php echo $divId ?>" class="__arrayDiffHtml">
            <table>
                <tr>
                    <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    <th>Field</th>
                    <th><?php echo $opt['title1'] ?> array</th>
                    <?php if (empty($opt['noSecond'])): ?>
                        <th><?php echo $opt['title2'] ?> array</th>
                    <? endif ?>
                </tr>

                <?php echo static::getArrayDiffHtml($first, $second, $strictEquality, $opt, $_diffIndex) ?>

            </table>
        </div>

        <script type="text/javascript">
            /*<![CDATA[*/
            (function ()
            {
                function getRowPlusMinusDiv(row)
                {
                    var trDivs = row.getElementsByTagName('div');
                    for (var k = 0; k < trDivs.length; k++) {
                        if (trDivs[k].className.match(/__arrayDiffHtmlMinus/) || trDivs[k].className.match(/__arrayDiffHtmlPlus/)) {
                            return trDivs[k];
                        }
                    }

                    return null;
                }

                var arrayDiffHtmlDiv = document.getElementById('<?php echo $divId ?>');
                for (var i = 0; i < arrayDiffHtmlDiv.childNodes.length; i++) {
                    if (arrayDiffHtmlDiv.childNodes[i] instanceof HTMLTableElement) {
                        var table = arrayDiffHtmlDiv.childNodes[i];
                        var tbody = table.childNodes[1];
                        var trList = tbody.childNodes;

                        for (var j = 1; j < trList.length; j++) {
                            var tr = trList.item(j);
                            if (tr instanceof HTMLTableRowElement) {
                                var plusMinusDiv = getRowPlusMinusDiv(tr);
                                if (plusMinusDiv) {
                                    (function (minusTr, plusMinusDiv)
                                    {
                                        var id = plusMinusDiv.id.replace(/^__arrayDiffHtmlPlusMinus_/, '');
                                        var expandableName = '__arrayDiffHtmlExpandable_' + id;

                                        var rowsBelongingToThisExpandable = [];
                                        var trList = table.getElementsByTagName('tr');
                                        for (var l = 0; l < trList.length; l++)
                                            if (trList[l].className.match(expandableName))
                                                rowsBelongingToThisExpandable.push(trList[l]);

                                        plusMinusDiv.onclick = function ()
                                        {
                                            if (plusMinusDiv.className == '__arrayDiffHtmlMinus') {
                                                // Collapse (hide everything under) the subtree of this item
                                                for (var m = 0; m < rowsBelongingToThisExpandable.length; m++) {
                                                    rowsBelongingToThisExpandable[m].style.display = 'none';
                                                }

                                                minusTr.style.display = '';

                                                plusMinusDiv.className = '__arrayDiffHtmlPlus';
                                                plusMinusDiv.innerHTML = '<span>+</span>';
                                            } else {
                                                // Expand (show everything under) the subtree of this item
                                                // The tricky part of this is that, if there are inner trees
                                                // within this item, that are collapsed - we shouldn't expand them
                                                var nextTr = minusTr.nextSibling;
                                                var collapsedSubTrees = [];
                                                do {
                                                    if (nextTr) {
                                                        if (nextTr instanceof HTMLTableRowElement) {
                                                            // If this row does not have the class of this expandable
                                                            // anymore - that means that we finished processing the
                                                            // tree of this expandable, so break out of the loop
                                                            if (nextTr.className.match(expandableName) == null) {
                                                                break;
                                                            }

                                                            var innerPlusMinusDiv = getRowPlusMinusDiv(nextTr);
                                                            if (innerPlusMinusDiv && innerPlusMinusDiv.className == '__arrayDiffHtmlPlus') {
                                                                var innerId = innerPlusMinusDiv.id.replace(/^__arrayDiffHtmlPlusMinus_/, '');
                                                                var innerExpandableName = '__arrayDiffHtmlExpandable_' + innerId;
                                                                collapsedSubTrees.push(innerExpandableName);

                                                                // If this is the row, that starts the collapsed subtree -
                                                                // show this row only
                                                                nextTr.style.display = '';
                                                            } else {
                                                                var nextTrIsAPartOfACollapsedTree = false;
                                                                for (var n = 0; n < collapsedSubTrees.length; n++) {
                                                                    if (nextTr.className.match(collapsedSubTrees[n])) {
                                                                        nextTrIsAPartOfACollapsedTree = true;
                                                                        break;
                                                                    }
                                                                }
                                                                if (nextTrIsAPartOfACollapsedTree == false)
                                                                    nextTr.style.display = '';
                                                            }
                                                        }
                                                    }

                                                    nextTr = nextTr.nextSibling;
                                                } while (nextTr);

                                                plusMinusDiv.className = '__arrayDiffHtmlMinus';
                                                plusMinusDiv.innerHTML = '<span>&ndash;</span>';
                                            }
                                        };

                                    })(tr, plusMinusDiv);
                                }
                            }
                        }
                    }
                }
            })();
            /*]]>*/
        </script>

        <?php
        return ob_get_clean();
    }

    /**
     * Given an array - recursively converts its properties that are not arrays into empty arrays.
     * @param mixed $item
     * @return array
     */
    protected static function toArray($item)
    {
        $arr = is_array($item) ? $item : [];

        foreach ($arr as $k => $v) {
            if (is_object($v)) {
                $arr[$k] = static::toArray($v);
            }
        }

        return $arr;
    }

    /**
     * Recursive function, which goes through both given arrays, compares them and outputs
     * the comparison result as HTML table rows.
     */
    protected static function getArrayDiffHtml($a, $b, $strictEquality, $opt, $diffIndex)
    {
        $html = '';

        $diff = static::getArrayDiff($a, $b, $strictEquality, $opt);
        $expandableIndex = 0;

        foreach ($diff as $diffRow) {
            $html .= static::getRowHtml($diffRow, $opt, $diffIndex, $expandableIndex);
        }

        return $html;
    }

    protected static function getRowHtml(
        $row, $opt, $diffIndex, &$expandableIndex, $fieldsSoFar = '', $classesSoFar = '', $hide = FALSE)
    {
        ob_start();

        $hasInnerRows = (array_key_exists('rows', $row) AND count($row['rows']) > 0);
        $collapseInnerRows = $row['equal'];

        if ($hasInnerRows) {
            $expandableIndex++;
            $thisExpandableIndex = $expandableIndex;
            $classesSoFar .= ' __arrayDiffHtmlExpandable_' . $diffIndex . '_' . $thisExpandableIndex;
        } else {
            $thisExpandableIndex = NULL;
        }

        $field = $fieldsSoFar . "[{$row['field']}]";
        ?>
        <tr <?php if ($hide): ?>style="display:none"<?php endif ?>
            class="__arrayDiffHtmlRow<?php if ($row['equal'] == FALSE): ?>Not<?php endif ?>Equal <?php echo $classesSoFar ?>
                <?php if (!empty($row['equalButTypeDifferent'])): ?>__arrayDiffHtmlRowEqualButTypeDifferent<?php endif ?>">

            <td>
                <?
                if ($hasInnerRows):
                    if ($collapseInnerRows): ?>
                        <div class="__arrayDiffHtmlPlus" id="__arrayDiffHtmlPlusMinus_<?php echo $diffIndex?>_<?php echo $thisExpandableIndex?>"><span>+</span></div>
                    <?php else: ?>
                        <div class="__arrayDiffHtmlMinus" id="__arrayDiffHtmlPlusMinus_<?php echo $diffIndex?>_<?php echo $thisExpandableIndex?>"><span>&ndash;</span></div>
                    <?php
                    endif;
                endif;
                ?>
            </td>
            <th><?php echo $field ?></th>

            <?php if ($row['aEmpty']): ?>
                <td class="__arrayDiffHtmlNoValue">&nbsp;</td>
            <?php elseif (array_key_exists('aVal', $row)): ?>
                <td>
                    <?php var_dump($row['aVal']) ?>
                </td>
            <?php else: ?>
                <td>Array(<? echo $row['aCount'] ?>)</td>
            <?php endif ?>

            <?php if (empty($opt['noSecond'])): ?>
                <?php if ($row['bEmpty']): ?>
                    <td class="__arrayDiffHtmlNoValue">&nbsp;</td>
                <?php elseif (array_key_exists('bVal', $row)): ?>
                    <td>
                        <?php var_dump($row['bVal']) ?>
                    </td>
                <?php else: ?>
                    <td>Array(<? echo $row['bCount'] ?>)</td>
                <?php endif ?>
            <?php endif ?>
        </tr>

        <?php

        if ($hasInnerRows)
            foreach ($row['rows'] as $innerRow) {
                echo static::getRowHtml($innerRow, $opt, $diffIndex, $expandableIndex, $field, $classesSoFar, $collapseInnerRows);
            }

        return ob_get_clean();
    }

    /**
     * Returns array difference in a handy array, which makes it easy to render a diff HTML table
     * from it. Each array row represents one field.
     * Array row format:
     *  A) if both are atomic values
     *  field -> field name
     *  rowCount -> 1
     *  equal -> whether or not the values are equal
     *  aVal -> value
     *  bVal -> value
     *
     *  B) if A is an array, and B is a value
     *  field -> field name
     *  rowCount -> total rows in A
     *  equal -> FALSE
     *  rows -> inner fields (taken from A)
     *  bVal -> value
     *
     *  C) if A is a value and B is an array
     *  field -> field name
     *  rowCount -> total rows in B
     *  equal -> FALSE
     *  aVal -> value
     *  rows -> inner fields (taken from B)
     *
     *  D) if both are arrays
     *  field -> field name
     *  rowCount -> total rows in A and B
     *  equal -> whether or not the inner arrays are equal
     *  rows -> inner fields
     */
    protected static function getArrayDiff($a, $b, $strictEquality = FALSE, $opt = Array())
    {
        $diff = Array();

        if (is_array($a) AND is_array($b)) {
            $keys = array_unique(array_merge(array_keys($a), array_keys($b)));
            foreach ($keys as $key) {
                if (array_key_exists($key, $a) AND array_key_exists($key, $b)) {
                    $innerRows = static::getArrayDiff($a[$key], $b[$key], $strictEquality, $opt);
                    if (isset($innerRows['rowCount'])) {
                        $innerRows['field'] = $key;
                        $diff[] = $innerRows;
                    } else {
                        $rowCount = 1;
                        $equal = TRUE;
                        $equalButTypeDifferent = FALSE;

                        foreach ($innerRows as $row) {
                            $rowCount += $row['rowCount'];
                            if ($row['equal'] == FALSE) {
                                $equal = FALSE;
                            } else if (!empty($row['equalButTypeDifferent'])) {
                                $equalButTypeDifferent = TRUE;
                            }
                        }

                        $diff[] = Array(
                            'field' => $key,
                            'rowCount' => $rowCount,
                            'equal' => $equal,
                            'equalButTypeDifferent' => $equalButTypeDifferent,
                            'rows' => $innerRows,
                            'aCount' => count($a[$key]),
                            'bCount' => count($b[$key]),
                            'aEmpty' => FALSE,
                            'bEmpty' => FALSE,
                        );
                    }
                } else if (array_key_exists($key, $a)) {
                    if (is_array($a[$key])) {
                        $innerDiff = static::getArrayDiff($a[$key], NULL, $strictEquality, $opt);
                        unset($innerDiff['bVal']);
                        $innerDiff['field'] = $key;
                        $innerDiff['bEmpty'] = TRUE;
                        $diff[] = $innerDiff;
                    } else {
                        $diff[] = Array(
                            'field' => $key,
                            'rowCount' => 1,
                            'equal' => (!empty($opt['noSecond']) ? TRUE : FALSE),
                            'aVal' => $a[$key],
                            'aEmpty' => FALSE,
                            'bEmpty' => TRUE,
                        );
                    }
                } else {
                    if (is_array($b[$key])) {
                        $innerDiff = static::getArrayDiff(NULL, $b[$key], $strictEquality, $opt);
                        unset($innerDiff['aVal']);
                        $innerDiff['field'] = $key;
                        $innerDiff['aEmpty'] = TRUE;
                        $diff[] = $innerDiff;
                    } else {
                        $diff[] = Array(
                            'field' => $key,
                            'rowCount' => 1,
                            'equal' => FALSE,
                            'bVal' => $b[$key],
                            'aEmpty' => TRUE,
                            'bEmpty' => FALSE,
                        );
                    }
                }
            }
        } else if (is_array($a)) {
            $innerRows = static::getArrayInnerRows($a, 'a', 'b', $opt);
            $rowCount = 1;
            foreach ($innerRows as $row) {
                $rowCount += $row['rowCount'];
            }

            $diff = Array(
                'rowCount' => $rowCount,
                'equal' => (!empty($opt['noSecond']) ? TRUE : FALSE),
                'rows' => $innerRows,
                'aCount' => count($a),
                'bVal' => $b,
                'aEmpty' => FALSE,
                'bEmpty' => FALSE,
            );
        } else if (is_array($b)) {
            $innerRows = static::getArrayInnerRows($b, 'b', 'a', $opt);
            $rowCount = 1;
            foreach ($innerRows as $row) {
                $rowCount += $row['rowCount'];
            }

            $diff = Array(
                'rowCount' => $rowCount,
                'equal' => FALSE,
                'aVal' => $a,
                'bCount' => count($b),
                'rows' => $innerRows,
                'aEmpty' => FALSE,
                'bEmpty' => FALSE,
            );
        } else {
            $diff = Array(
                'rowCount' => 1,
                'equal' => ($strictEquality ? $a === $b : $a == $b),
                'equalButTypeDifferent' => ($strictEquality == FALSE AND $a == $b AND $a !== $b),
                'aVal' => $a,
                'bVal' => $b,
                'aEmpty' => FALSE,
                'bEmpty' => FALSE,
            );
        }

        return $diff;
    }

    protected static function getArrayInnerRows($array, $arrayName, $otherArrayName, $opt)
    {
        $innerRows = Array();

        foreach ($array as $key => $val)
        {
            $row = Array();

            if (is_array($val)) {
                $valInnerRows = static::getArrayInnerRows($val, $arrayName, $otherArrayName, $opt);
                $rowCount = 1;
                foreach ($valInnerRows as $field) {
                    $rowCount += $field['rowCount'];
                }
                $row = Array(
                    'field' => $key,
                    'rowCount' => $rowCount,
                    'equal' => (!empty($opt['noSecond']) ? TRUE : FALSE),
                    'rows' => $valInnerRows,
                    "{$arrayName}Count" => count($val),
                    "{$arrayName}Empty" => FALSE,
                    "{$otherArrayName}Empty" => TRUE,
                );
            } else {
                $row = Array(
                    'field' => $key,
                    'rowCount' => 1,
                    'equal' => (!empty($opt['noSecond']) ? TRUE : FALSE),
                    "{$arrayName}Val" => $val,
                    "{$arrayName}Empty" => FALSE,
                    "{$otherArrayName}Empty" => TRUE,
                );
            }

            $innerRows[] = $row;
        }

        return $innerRows;
    }
}


// if executing this file directly, run unit tests
if (str_replace('\\', '/', __FILE__) !== $_SERVER["PHP_SELF"]
AND str_replace('\\', '/', __FILE__) !== $_SERVER['DOCUMENT_ROOT'] . substr($_SERVER["PHP_SELF"], 1))
    if (str_replace('\\', '/', __FILE__) !== $_SERVER['SCRIPT_FILENAME'])
        return;


$testIndex = 0;

$testIndex++;
echo "{$testIndex}. Initial test - should only display row [c][foo1] as equal";
$result = Array(
    'a' => 'b',
    'c' => Array(
        'foo1' => 'bar2',
        'foo2' => Array(
            'bar2',
            'bar3',
        ),
    ),
);
$expected = Array(
    'a' => Array(
        'bla1',
        'bla2',
        'bla3' => Array(),
        'bla4' => Array(
            'test',
        ),
    ),
    'c' => Array(
        'foo1' => 'bar2',
        'foo2' => Array(
            'bar3',
        ),
    ),
    'd',
    'e' => Array(),
);
echo ArrayDiffHtml::diff($result, $expected, true);
echo "<br />";

$testIndex++;
echo "{$testIndex}. Reverse - should only display row [0] as not matching";
$result = Array(
    'a' => Array(
        'bla1',
        'bla2',
        'bla3' => Array(),
        'bla4' => Array(
            'test',
        ),
    ),
    'c' => Array(
        'foo1' => 'bar2',
        'foo2' => Array(
            'bar3',
        ),
    ),
    'd',
    'e' => Array(),
);
$expected = Array(
    'a' => Array(
        'bla1',
        'bla2',
        'bla3' => Array(),
        'bla4' => Array(
            'test',
        ),
    ),
    'c' => Array(
        'foo1' => 'bar2',
        'foo2' => Array(
            'bar3',
        ),
    ),
    'd1',
    'e' => Array(),
);
echo ArrayDiffHtml::diff($result, $expected, true);
echo "<br />";

$testIndex++;
echo "{$testIndex}. Should display [c][foo2][0] as not matching, and it's parent containers - [c][foo2] and [c]";
$result = Array(
    'a' => Array(
        'bla1',
        'bla2',
        'bla3' => Array(),
        'bla4' => Array(
            'test',
        ),
    ),
    'c' => Array(
        'foo1' => 'bar2',
        'foo2' => Array(
            'bar3',
        ),
    ),
    'd',
    'e' => Array(),
);
$expected = Array(
    'a' => Array(
        'bla1',
        'bla2',
        'bla3' => Array(),
        'bla4' => Array(
            'test',
        ),
    ),
    'c' => Array(
        'foo1' => 'bar2',
        'foo2' => Array(
            'bar4',
        ),
    ),
    'd',
    'e' => Array(),
);
echo ArrayDiffHtml::diff($result, $expected, true);
echo "<br />";

$testIndex++;
echo "{$testIndex}. Test not type strict checking. Everything should match (item [c][foo2][0] matches if we do non-identical check)";
$result = Array(
    'a' => Array(
        'bla1',
        'bla2',
        'bla3' => Array(),
        'bla4' => Array(
            'test',
        ),
    ),
    'c' => Array(
        'foo1' => 'bar2',
        'foo2' => Array(
            1,
        ),
    ),
    'd',
    'e' => Array(),
);
$expected = Array(
    'a' => Array(
        'bla1',
        'bla2',
        'bla3' => Array(),
        'bla4' => Array(
            'test',
        ),
    ),
    'c' => Array(
        'foo1' => 'bar2',
        'foo2' => Array(
            '1',
        ),
    ),
    'd',
    'e' => Array(),
);
echo ArrayDiffHtml::diff($result, $expected, false);
echo "<br />";

$testIndex++;
echo "{$testIndex}. This should not break (we pass an empty array as the second array), and only display first array";
$result = Array(
    'c' => Array(
        'foo1' => 'bar2',
        'foo2' => Array(
            'bar2',
            'bar3',
        ),
    ),
);
$expected = Array();
echo ArrayDiffHtml::diff($result, $expected, true);
echo "<br />";
