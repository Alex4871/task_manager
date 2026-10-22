<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'Не выполнена';
    case Completed = 'Выполнена';
}
