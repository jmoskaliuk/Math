<?php
declare(strict_types=1);

namespace mod_mathetrainer\validator;

final class numeric_validator {
    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    public function validate(array $expected, array $response): array {
        $expectedvalue = isset($expected['value']) ? (float)$expected['value'] : 0.0;
        $responsevalue = isset($response['value']) ? (float)$response['value'] : NAN;

        $iscorrect = is_finite($responsevalue) && abs($expectedvalue - $responsevalue) < 0.00001;
        $errortag = null;

        if (!$iscorrect && is_finite($responsevalue)) {
            $errortag = ($responsevalue * $expectedvalue < 0) ? 'sign_error' : 'value_mismatch';
        }

        return [
            'iscorrect' => $iscorrect,
            'expected' => $expectedvalue,
            'received' => $responsevalue,
            'error_tag' => $errortag,
        ];
    }
}
