<?php
/**
 * File BooleanType.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\Swagger\Entity\Parameters\HeaderParameter;

use Epfremme\Swagger\Entity\Mixin\Primitives;
use Epfremme\Swagger\Entity\Parameters\AbstractTypedParameter;

/**
 * Class BooleanType
 *
 * @package Epfremme\Swagger
 * @subpackage Entity\Parameters\HeaderParameter
 */
class BooleanType extends AbstractTypedParameter
{
    use Primitives\BooleanPrimitiveTrait;
}
