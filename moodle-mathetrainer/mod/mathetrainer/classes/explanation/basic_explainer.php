<?php
declare(strict_types=1);

namespace mod_mathetrainer\explanation;

final class basic_explainer {
    /**
     * @param array<string, mixed> $validation
     */
    public function explain(array $validation): string {
        if (!empty($validation['iscorrect'])) {
            return 'Richtig. Du hast die Zahl auf der Zahlengeraden korrekt bestimmt.';
        }

        if (($validation['error_tag'] ?? '') === 'sign_error') {
            return 'Achte auf das Vorzeichen. Prüfe, ob du nach links (minus) oder rechts (plus) gehst.';
        }

        return 'Prüfe deinen Rechenweg schrittweise auf der Zahlengeraden.';
    }
}
