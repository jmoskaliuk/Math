<?php
declare(strict_types=1);

namespace mod_mathetrainer\generator;

final class task_generator {
    /**
     * @return array<string, mixed>
     */
    public function generate(string $templateid, int $seed): array {
        return [
            'templateid' => $templateid,
            'seed' => $seed,
            'status' => 'skeleton',
        ];
    }
}
