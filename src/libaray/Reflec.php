<?php
/**
 * Created by PhpStorm.
 * User: song
 * Date: 2019/5/23
 * Time: 21:51
 * Email: songyz@guahao.com
 */

namespace Songyongzhan\Library;

class Reflec {

  private $_instance = NULL;

  public function __construct($class) {
    $this->_instance = new \ReflectionClass($class);
  }

  public function getFileName() {
    return $this->_instance->getFileName();
  }

  public function getFileTime() {
    return filemtime($this->getFileName());
  }

  public function getClassComment() {
    return $this->_instance->getDocComment();
  }

  /**
   * 获取指定方法的注解
   * @param string $methodname 方法名
   * @return string|false
   * @throws ReflectionException
   */
  public function getMethodComment($methodname) {
    $method = $this->_instance->getMethod($methodname); //ReflectionMethod

    return $method->getDocComment();
  }

  public function getMethodParams($methodname, $isDefaultValue = FALSE) {
    $params = $this->_instance->getMethod($methodname)->getParameters();

    $result = [];
    foreach ($params as $param) { //ReflectionParameter
      $paramName = $param->getName();
      if ($isDefaultValue && $param->isDefaultValueAvailable()) { //isOptional
        $result[$paramName] = $param->getDefaultValue();
      } else $result[] = $paramName;
    }

    return $result;
  }

  public function isMethod($methodname) {
    return $this->_instance->hasMethod($methodname);
  }

  public function getAllComment($regular, array $ignore = []) {
    foreach ($this->_instance->getMethods() as $method) {
      if ($ignore && in_array($method->class, $ignore)) continue; //getDeclaringClass

      if (!$method->isPublic() && !$method->isStatic()) continue;

      if (!$comment = $method->getDocComment()) continue;

      if (preg_match_all($regular, $comment, $result)) {
        yield $method->getName() => $result;
      }
    }
  }

}
