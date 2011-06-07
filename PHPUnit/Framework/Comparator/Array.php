<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.6.0
 */

/**
 * Compares arrays for equality.
 *
 * @package    PHPUnit
 * @subpackage Framework_Comparator
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.6.0
 */
class PHPUnit_Framework_Comparator_Array extends PHPUnit_Framework_Comparator
{
    /**
     * Returns whether the comparator can compare two values.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @return boolean
     * @since  Method available since Release 3.6.0
     */
    public function accepts($expected, $actual)
    {
        return is_array($expected) && is_array($actual);
    }

    /**
     * Asserts that two values are equal.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @param  float $delta The allowed numerical distance between two values to
     *                      consider them equal
     * @param  bool  $canonicalize If set to TRUE, arrays are sorted before
     *                             comparison
     * @param  bool  $ignoreCase If set to TRUE, upper- and lowercasing is
     *                           ignored when comparing string values
     * @throws PHPUnit_Framework_ComparisonFailure Thrown when the comparison
     *                           fails. Contains information about the
     *                           specific errors that lead to the failure.
     * @since  Method available since Release 3.6.0
     */
    public function assertEquals($expected, $actual, $delta = 0, $canonicalize = FALSE, $ignoreCase = FALSE, array &$processed = array())
    {
        if ($canonicalize) {
            sort($expected);
            sort($actual);
        }

        $remaining = $actual;
        $expString = $actString = "Array\n(\n";
        $equal = TRUE;

        foreach ($expected as $key => $value) {
            unset($remaining[$key]);

            if (!array_key_exists($key, $actual)) {
                $expString .= sprintf(
                  "    [%s] => %s\n",

                  print_r($key, true),
                  $this->shortenedExport($value)
                );
                $equal = FALSE;
                continue;
            }

            try {
                self::getInstance($value, $actual[$key])->assertEquals($value, $actual[$key], $delta, $canonicalize, $ignoreCase, $processed);
                $expString .= sprintf(
                  "    [%s] => %s\n",

                  print_r($key, true),
                  $this->shortenedExport($value)
                );
                $actString .= sprintf(
                  "    [%s] => %s\n",

                  print_r($key, true),
                  $this->shortenedExport($actual[$key])
                );
            }

            catch (PHPUnit_Framework_ComparisonFailure $e) {
                $expString .= sprintf(
                  "    [%s] => %s\n",

                  print_r($key, true),
                  $e->getExpectedAsString()
                    ? $this->indent($e->getExpectedAsString())
                    : print_r($e->getExpected(), true)
                );
                $actString .= sprintf(
                  "    [%s] => %s\n",

                  print_r($key, true),
                  $e->getActualAsString()
                    ? $this->indent($e->getActualAsString())
                    : print_r($e->getActual(), true)
                );
                $equal = FALSE;
            }
        }

        foreach ($remaining as $key => $value) {
            $actString .= sprintf(
              "    [%s] => %s\n",

              print_r($key, true),
              $this->shortenedExport($value)
            );
            $equal = FALSE;
        }

        $expString .= ')';
        $actString .= ')';

        if (!$equal) {
            throw new PHPUnit_Framework_ComparisonFailure(
              $expected,
              $actual,
              $expString,
              $actString,
              FALSE,
             'Failed asserting that two arrays are equal.'
            );
        }
    }

    protected function indent($lines)
    {
        return trim(str_replace("\n", "\n        ", $lines));
    }

    protected function shortenedExport($value)
    {
        if (is_array($value)) {
            if (count($value) > 0) {
                return 'Array (...)';
            }

            return 'Array';
        }

        if (is_object($value)) {
            return get_class($value) . ' Object (...)';
        }

        return print_r($value, true);
    }
}