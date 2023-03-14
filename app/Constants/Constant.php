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
    public const UNITS = [
        1=>'株',
        2=>'颗',
        3=>'丛',
        4=>'斤',
        5=>'吨',
        6=>'芽',
        7=>'个',
        8=>'两',
    ];
}
