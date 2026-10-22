<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'Низкий';
    case Medium = 'Средний';
    case High = 'Высокий';
}
