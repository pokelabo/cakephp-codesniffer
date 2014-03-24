<?php
/**
 * Pokelabo_Sniffs_NamingConventions_ValidFunctionNameSniff
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_Pokelabo
 * @author    Juan Basso <jrbasso@gmail.com>
 * @copyright Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer_Pokelabo
 */

if (class_exists('PHP_CodeSniffer_Standards_AbstractScopeSniff', true) === false) {
	throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractScopeSniff not found');
}

/**
 * Pokelabo_Sniffs_NamingConventions_ValidFunctionNameSniff.
 *
 * Ensures method names are correct depending on whether they are public
 * or private, and that functions are named correctly.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_Pokelabo
 * @author    Juan Basso <jrbasso@gmail.com>
 * @copyright Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer_Pokelabo
 */
class Pokelabo_Sniffs_NamingConventions_ValidFunctionNameSniff extends PHP_CodeSniffer_Standards_AbstractScopeSniff {

/**
 * A list of all PHP magic methods.
 *
 * @var array
 */
	protected $_magicMethods = array(
		'construct',
		'destruct',
		'call',
		'callStatic',
		'get',
		'set',
		'isset',
		'unset',
		'sleep',
		'wakeup',
		'toString',
		'set_state',
		'clone',
		'invoke',
	);

/**
 * Constructs a PEAR_Sniffs_NamingConventions_ValidFunctionNameSniff.
 */
	public function __construct() {
		parent::__construct(array(T_CLASS, T_INTERFACE), array(T_FUNCTION), true);
	}

/**
 * Processes the tokens within the scope.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
 * @param int $stackPtr The position where this token was found.
 * @param int $currScope The position of the current scope.
 * @return void
 */
	protected function processTokenWithinScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $currScope) {
		$methodName = $phpcsFile->getDeclarationName($stackPtr);
		if ($methodName === null) {
			// Ignore closures.
			return;
		}

		$className = $phpcsFile->getDeclarationName($currScope);
		$errorData = array($className . '::' . $methodName);

		// PHP4 constructors are allowed to break our rules.
		if ($methodName === $className) {
			return;
		}

		// PHP4 destructors are allowed to break our rules.
		if ($methodName === '_' . $className) {
			return;
		}

		// Ignore magic methods
		if (preg_match('/^__(' . implode('|', $this->_magicMethods) . ')$/', $methodName)) {
			return;
		}

		$methodProps = $phpcsFile->getMethodProperties($stackPtr);
		if ($methodProps['scope_specified'] === false) {
			$error = 'All methods must have a scope specified';
			$phpcsFile->addError($error, $stackPtr, 'NoScopeSpecified', $errorData);
			return;
		}

		$isPublic = $methodProps['scope'] === 'public';
		$isProtected = $methodProps['scope'] === 'protected';
		$isPrivate = $methodProps['scope'] === 'private';
		$scope = $methodProps['scope'];

		if ($isPublic === true) {
			if ($methodName[0] === '_') {
				$error = 'Public method name "%s" must not be prefixed with underscore';
				$phpcsFile->addError($error, $stackPtr, 'PublicWithUnderscore', $errorData);
				return;
			}
			// Underscored public methods in controller are allowed to break our rules.
			if (substr($className, -10) === 'Controller') {
				return;
			}
			// Underscored public methods in shells are allowed to break our rules.
			if (substr($className, -5) === 'Shell') {
				return;
			}
		} elseif ($isPrivate === true) {
			// 現状、privateメソッド名の先頭にアンダースコアを付与するルールはない
			if ($methodName[0] === '_') {
				$error = 'Private method name "%s" must not be prefixed with underscore';
				$phpcsFile->addError($error, $stackPtr, 'PrivateWithUnderscore', $errorData);
				return;
			}

//			if (substr($methodName, 0, 2) !== '__') {
//				$error = 'Private method name "%s" must be prefixed with 2 underscores';
//				$phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $errorData);
//				return;
//			} else {
//				$filename = $phpcsFile->getFilename();
//				if (strpos($filename, '/lib/Cake/') === true) {
//					$warning = 'Private method name "%s" in Pokelabo core is discouraged';
//					$phpcsFile->addWarning($warning, $stackPtr, 'PrivateMethodInCore', $errorData);
//				}
//			}
		} else {
			// 現状、protectedメソッド名の先頭にアンダースコアを付与するルールはない
			if ($methodName[0] === '_') {
				$error = 'Protected method name "%s" must not be prefixed with underscore';
				$phpcsFile->addError($error, $stackPtr, 'ProtectedWithUnderscore', $errorData);
				return;
			}

//			if ($methodName[0] !== '_' || substr($methodName, 0, 2) === '__') {
//				$error = 'Protected method name "%s" must be prefixed with one underscore';
//				$phpcsFile->addError($error, $stackPtr, 'ProtectedNoUnderscore', $errorData);
//				return;
//			}
		}

		$testMethodName = ltrim($methodName, '_');
		if (PHP_CodeSniffer::isCamelCaps($testMethodName, false, true, false) === false) {
			$error = '%s method name "%s" is not in camel caps format';
			$data = array(
				ucfirst($scope),
				$methodName,
			);
			$phpcsFile->addError($error, $stackPtr, 'ScopeNotCamelCaps', $data);
			return;
		}
	}

/**
 * Processes the tokens outside the scope.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
 * @param int $stackPtr  The position where this token was found.
 * @return void
 */
	protected function processTokenOutsideScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
	}

}
