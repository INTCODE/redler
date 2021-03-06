<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model;

/**
 * Temando Dispatch Provider
 *
 * @package  Temando\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
interface DispatchProviderInterface
{
    /**
     * @return DispatchInterface
     */
    public function getDispatch();

    /**
     * @param DispatchInterface $dispatch
     * @return void
     */
    public function setDispatch(DispatchInterface $dispatch);
}
