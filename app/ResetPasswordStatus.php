<?php

namespace App;

enum ResetPasswordStatus: int
{

    case ACTIVE = 0;
    case EXPIRED = 1;

    case USED = 2;

}
