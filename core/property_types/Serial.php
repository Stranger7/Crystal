<?php
/**
 * This file is part of the Crystal framework.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 06.01.2015
 * Time: 13:30
 */

namespace core\property_types;

/**
 * Class Serial
 * @package core\property_types
 */
class Serial extends Integer
{
    /**
     * @return string
     */
    public function type()
    {
        return 'SERIAL';
    }
}