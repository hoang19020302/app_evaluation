<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Trạng thái của response trả về
 */
final class ServiceStatus extends Enum
{
    // Thành công
    const Success = 1;

    // Thất bại
    const Fail = 2;

    // Lỗi
    const Error = 3;
}