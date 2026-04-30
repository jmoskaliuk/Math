<?php
declare(strict_types=1);

namespace mod_mathetrainer\output;

use renderable;
use renderer_base;
use templatable;

final class attempt_feedback implements renderable, templatable {
    public function __construct(
        private readonly bool $iscorrect,
        private readonly string $explanation,
    ) {
    }

    public function export_for_template(renderer_base $output): array {
        return [
            'iscorrect' => $this->iscorrect,
            'explanation' => $this->explanation,
        ];
    }
}
