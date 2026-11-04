<?php

namespace Database\Seeders;

use App\Enums\Level;

/**
 * EXAMPLE score objects and capability tags per level.
 *
 * Marked clearly: this is NOT a final rulebook. It only demonstrates the
 * dynamic configuration capability of the system.
 */
class ExampleChallengeData
{
    /** @return array<int, array{code:string, name:string, type:string, points:int, max:?int, evidence:bool}> */
    public static function scoreObjects(Level $level): array
    {
        return match ($level) {
            Level::Sd => [
                ['code' => 'SD-MOVE', 'name' => 'Bergerak maju', 'type' => 'achievement', 'points' => 20, 'max' => null, 'evidence' => false],
                ['code' => 'SD-TURN', 'name' => 'Belok sesuai instruksi', 'type' => 'achievement', 'points' => 15, 'max' => null, 'evidence' => false],
                ['code' => 'SD-STOP', 'name' => 'Berhenti tepat target', 'type' => 'achievement', 'points' => 15, 'max' => null, 'evidence' => false],
                ['code' => 'SD-SEQUENCE', 'name' => 'Urutan instruksi benar', 'type' => 'achievement', 'points' => 25, 'max' => null, 'evidence' => false],
                ['code' => 'SD-COLOR', 'name' => 'Baca checkpoint warna', 'type' => 'checkpoint', 'points' => 12, 'max' => 4, 'evidence' => true],
                ['code' => 'SD-DELIVER', 'name' => 'Antar objek ringan', 'type' => 'object', 'points' => 15, 'max' => 3, 'evidence' => true],
                ['code' => 'SD-HOME', 'name' => 'Kembali ke home', 'type' => 'achievement', 'points' => 20, 'max' => null, 'evidence' => false],
                ['code' => 'SD-PENALTY-LOST', 'name' => 'Penalti objek hilang', 'type' => 'penalty', 'points' => 10, 'max' => null, 'evidence' => false],
            ],
            Level::Smp => [
                ['code' => 'SMP-LINE', 'name' => 'Line following', 'type' => 'achievement', 'points' => 20, 'max' => null, 'evidence' => false],
                ['code' => 'SMP-INTERSECT', 'name' => 'Intersection benar', 'type' => 'checkpoint', 'points' => 12, 'max' => 4, 'evidence' => false],
                ['code' => 'SMP-LOGIC', 'name' => 'IF/ELSE-loop-variable tepat', 'type' => 'achievement', 'points' => 30, 'max' => null, 'evidence' => true],
                ['code' => 'SMP-RGB', 'name' => 'Deteksi RGB checkpoint', 'type' => 'checkpoint', 'points' => 10, 'max' => 5, 'evidence' => true],
                ['code' => 'SMP-OBSTACLE', 'name' => 'Deteksi rintangan', 'type' => 'achievement', 'points' => 20, 'max' => null, 'evidence' => false],
                ['code' => 'SMP-PICK', 'name' => 'Pick objek', 'type' => 'object', 'points' => 15, 'max' => 3, 'evidence' => true],
                ['code' => 'SMP-DELIVER', 'name' => 'Deliver objek', 'type' => 'object', 'points' => 15, 'max' => 3, 'evidence' => true],
                ['code' => 'SMP-RETURN', 'name' => 'Return ke home', 'type' => 'achievement', 'points' => 20, 'max' => null, 'evidence' => false],
                ['code' => 'SMP-PENALTY-COLLIDE', 'name' => 'Penalti tabrakan', 'type' => 'penalty', 'points' => 10, 'max' => null, 'evidence' => false],
            ],
            Level::Sma => [
                ['code' => 'SMA-PID', 'name' => 'PID stabil', 'type' => 'achievement', 'points' => 25, 'max' => null, 'evidence' => false],
                ['code' => 'SMA-ENCODER', 'name' => 'Encoder tepat', 'type' => 'achievement', 'points' => 20, 'max' => null, 'evidence' => false],
                ['code' => 'SMA-ROUTE', 'name' => 'Route planning / state machine', 'type' => 'achievement', 'points' => 30, 'max' => null, 'evidence' => true],
                ['code' => 'SMA-HAZARD', 'name' => 'Hindari hazard', 'type' => 'checkpoint', 'points' => 15, 'max' => 4, 'evidence' => true],
                ['code' => 'SMA-TARGET', 'name' => 'Identifikasi target', 'type' => 'achievement', 'points' => 25, 'max' => null, 'evidence' => true],
                ['code' => 'SMA-SUPPLY', 'name' => 'Kirim supply', 'type' => 'object', 'points' => 20, 'max' => 2, 'evidence' => true],
                ['code' => 'SMA-RETURN', 'name' => 'Return to base', 'type' => 'achievement', 'points' => 20, 'max' => null, 'evidence' => false],
                ['code' => 'SMA-PENALTY-HAZARD', 'name' => 'Penalti sentuh hazard', 'type' => 'penalty', 'points' => 15, 'max' => null, 'evidence' => false],
            ],
        };
    }

    /** @return array<int, array{code:string, name:string}> */
    public static function capabilityTags(Level $level): array
    {
        return match ($level) {
            Level::Sd => [
                ['code' => 'motion.motor_straight', 'name' => 'Gerak maju'],
                ['code' => 'motion.turn', 'name' => 'Belok'],
                ['code' => 'motion.stop', 'name' => 'Berhenti'],
                ['code' => 'logic.sequence', 'name' => 'Urutan instruksi'],
                ['code' => 'sensor.color', 'name' => 'Sensor warna'],
                ['code' => 'grip.carry_light', 'name' => 'Angkut objek ringan'],
            ],
            Level::Smp => [
                ['code' => 'sensor.line', 'name' => 'Line following'],
                ['code' => 'sensor.rgb', 'name' => 'Sensor RGB'],
                ['code' => 'sensor.ultrasonic', 'name' => 'Deteksi rintangan'],
                ['code' => 'logic.branch_loop', 'name' => 'IF/ELSE-loop-variable'],
                ['code' => 'servo.pick_deliver', 'name' => 'Pick-deliver-return'],
            ],
            Level::Sma => [
                ['code' => 'control.pid', 'name' => 'Kontrol PID'],
                ['code' => 'sensor.encoder', 'name' => 'Encoder'],
                ['code' => 'logic.state_machine', 'name' => 'State machine / route planning'],
                ['code' => 'sensor.hazard', 'name' => 'Identifikasi hazard'],
                ['code' => 'mission.supply_drop', 'name' => 'Supply & return-to-base'],
            ],
        };
    }
}
