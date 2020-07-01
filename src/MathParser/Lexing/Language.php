<?php
/*
 * @package     Lexical analysis
 * @author      Ingo Dahn <dahn@dahn-research.eu>
 * @copyright   2019 Ingo Dahn
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 *
 */

/**
 * @namespace MathParser::Lexing
 * Lexer and Token related classes.
 *
 * [Lexical analysis](https://en.wikipedia.org/wiki/Lexical_analysis)
 * or *lexing* is the process of converting an input string into a sequence
 * of tokens, representing discrete parts of the input each carrying certain meaning.
 *
 */
namespace MathParser\Lexing;
/**
 * Class Language keeps definitions of variables and constants
 * Maybe extended later by function and relation symbols
 */
class Language
{
  private $constants=[];
  private $variables=['[a-zA-Z]'];
  /**
   * setting the list of constnts
   * @retval void
   * @param Array $carray Array of constants
   */
  public function setConstants(Array $carray) {
    $this->constants=$carray;
  }
  /**
   * Adding a list of constants
   * @retval void
   * @param Array $carray Array of constants
   */
  public function addConstants(Array $carray) {
    $oldconsts=$this->constants;
    $this->constants=array_merge($oldconsts,$carray);
  }
  /**
   * Removing a list of constants
   * @retval void
   * @param Array $carray Array of constnts
   */
  public function removeConstants(Array $carray) {
    $oldconsts=$this->constants;
    $this->constants=array_values(array_diff($oldconsts,$carray));
  }
  /**
   * getting the list of constnts
   * @retval Array of constants
   */
  public function getConstants() {
    return $this->constants;
  }

  /**
   * setting the list of variables
   * As default, all letters are variables
   * @retval void
   * @param Array $carray Array of variables
   */
  public function setVariables(Array $carray) {
    $this->variables=$carray;
  }
  /**
   * Adding a list of variables
   * @retval void
   * @param Array $carray Array of variables
   */
  public function addVariables(Array $carray) {
    $oldvars=$this->variables;
    $this->variables=array_merge($oldvars,$carray);
  }
  /**
   * Removing a list of variables
   * Use removeVariables(['[a-zA-Z]']) to remove the default single letter variable declaration
   * @retval void
   * @param Array $carray Array of variables
   */
  public function removeVariables(Array $carray) {
    $oldvars=$this->variables;
    $this->variables=array_values(array_diff($oldvars,$carray));
  }
  /**
   * getting the list of variables
   * @retval Array of variables
   */
  public function getVariables() {
    return $this->variables;
  }
}