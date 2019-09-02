<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use login\classes\VipTestPassport;

return hook(function ($passport) {
    $passport = new VipTestPassport();

    return $passport;
});