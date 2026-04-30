<?php
declare(strict_types=1);

namespace local_curriculum_math\model;

final class framework {
    public function __construct(
        public readonly string $id,
        public readonly string $state,
        public readonly string $stage,
        public readonly string $version,
    ) {
    }
}
