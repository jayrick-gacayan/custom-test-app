<?php

namespace App\Enums;

enum FriendStatus: string
{
  case PENDING = "pending";
  case DECLINED = "declined";
  case ACCEPTED = "accepted";
}
