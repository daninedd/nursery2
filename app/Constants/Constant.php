<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class Constant extends AbstractConstants
{
    public const UNITS = [1, 2, 3, 4, 5, 6, 7, 8, 9];
}
