# Datenschutz

- Keine Übertragung personenbezogener Schülerdaten an externe Systeme (KI, Bloom oder andere).
- Bloom-Pakete enthalten ausschließlich Aufgaben, Lösungen und Erklärtexte – keine Ergebnisse.
- Bewertung und Fehlerdiagnose laufen vollständig lokal und regelbasiert.
- Gespeichert werden nur Durchgänge, Antworten mit Fehlerkategorie und Lernstand je Kompetenz.
- Moodle Privacy API (`classes/privacy/provider.php`): Auskunft (Export) und Löschung je Person, je
  Kontext und für mehrere Personen; getestet in `tests/privacy_provider_test.php`.
- `local_curriculum_math` speichert keine personenbezogenen Daten (Null-Provider).
- Kurs-Reset löscht alle Durchgänge und Lernstände einer Aktivität.
- Rollen und Rechte: `mod/mathetrainer:attempt` (Lernende), `mod/mathetrainer:viewreports`
  (Lehrkräfte, mit RISK_PERSONAL gekennzeichnet).
