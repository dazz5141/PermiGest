<?php

namespace App\Console\Commands;

use App\Models\Auditoria;
use App\Models\EstadoSolicitud;
use App\Models\Solicitud;
use App\Notifications\SolicitudPendienteRecordatorio;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class EnviarRecordatoriosSolicitudesPendientes extends Command
{
    private const ACCION_AUDITORIA = 'recordatorio_solicitud_pendiente';

    private const TIMEZONE = 'America/Santiago';

    protected $signature = 'solicitudes:recordar-pendientes';

    protected $description = 'Envia recordatorios diarios de solicitudes pendientes a sus validadores';

    public function handle(): int
    {
        $limiteAntiguedad = now()->subHours(24);
        $ahoraLocal = now(self::TIMEZONE);
        $inicioDiaUtc = $ahoraLocal->copy()->startOfDay()->utc();
        $finDiaUtc = $ahoraLocal->copy()->addDay()->startOfDay()->utc();

        $enviados = 0;
        $omitidos = 0;
        $fallidos = 0;

        Solicitud::query()
            ->select('solicitudes.id')
            ->where('created_at', '<=', $limiteAntiguedad)
            ->whereHas('estado', fn ($query) => $query->where('codigo', EstadoSolicitud::CODIGO_PENDIENTE))
            ->whereHas('validador', fn ($query) => $query->where('activo', true))
            ->orderBy('solicitudes.id')
            ->chunkById(100, function ($solicitudes) use (
                $limiteAntiguedad,
                $inicioDiaUtc,
                $finDiaUtc,
                &$enviados,
                &$omitidos,
                &$fallidos
            ): void {
                foreach ($solicitudes as $candidata) {
                    try {
                        $enviado = $this->procesarSolicitud(
                            (int) $candidata->id,
                            $limiteAntiguedad,
                            $inicioDiaUtc,
                            $finDiaUtc
                        );

                        $enviado ? $enviados++ : $omitidos++;
                    } catch (Throwable $exception) {
                        report($exception);
                        $this->eliminarMarcaFallida(
                            (int) $candidata->id,
                            $inicioDiaUtc,
                            $finDiaUtc
                        );
                        $fallidos++;
                        $this->error("Solicitud #{$candidata->id}: no fue posible registrar el recordatorio.");
                    }
                }
            }, 'solicitudes.id', 'id');

        $this->info("Recordatorios enviados: {$enviados}. Omitidos: {$omitidos}. Fallidos: {$fallidos}.");

        return $fallidos > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function procesarSolicitud(
        int $solicitudId,
        CarbonInterface $limiteAntiguedad,
        CarbonInterface $inicioDiaUtc,
        CarbonInterface $finDiaUtc
    ): bool {
        return DB::transaction(function () use (
            $solicitudId,
            $limiteAntiguedad,
            $inicioDiaUtc,
            $finDiaUtc
        ): bool {
            $solicitud = Solicitud::with(['estado', 'usuario', 'tipo', 'validador'])
                ->lockForUpdate()
                ->find($solicitudId);

            if (! $this->sigueElegible($solicitud, $limiteAntiguedad)) {
                return false;
            }

            $yaRegistrado = Auditoria::query()
                ->where('tabla', 'solicitudes')
                ->where('registro_id', $solicitud->id)
                ->where('accion', self::ACCION_AUDITORIA)
                ->where('created_at', '>=', $inicioDiaUtc)
                ->where('created_at', '<', $finDiaUtc)
                ->exists();

            if ($yaRegistrado) {
                return false;
            }

            Auditoria::create([
                'user_id' => null,
                'tabla' => 'solicitudes',
                'registro_id' => $solicitud->id,
                'accion' => self::ACCION_AUDITORIA,
                'datos_anteriores' => null,
                'datos_nuevos' => [
                    'destinatario_user_id' => $solicitud->validador->id,
                    'fecha_local' => now(self::TIMEZONE)->toDateString(),
                ],
                'ip' => null,
                'navegador' => 'scheduler',
            ]);

            $solicitud->validador->notify(new SolicitudPendienteRecordatorio($solicitud));

            return true;
        }, 3);
    }

    private function sigueElegible(?Solicitud $solicitud, CarbonInterface $limiteAntiguedad): bool
    {
        return $solicitud !== null
            && $solicitud->estado?->codigo === EstadoSolicitud::CODIGO_PENDIENTE
            && $solicitud->created_at?->lessThanOrEqualTo($limiteAntiguedad)
            && $solicitud->validador !== null
            && $solicitud->validador->activo;
    }

    private function eliminarMarcaFallida(
        int $solicitudId,
        CarbonInterface $inicioDiaUtc,
        CarbonInterface $finDiaUtc
    ): void {
        try {
            Auditoria::query()
                ->where('tabla', 'solicitudes')
                ->where('registro_id', $solicitudId)
                ->where('accion', self::ACCION_AUDITORIA)
                ->where('created_at', '>=', $inicioDiaUtc)
                ->where('created_at', '<', $finDiaUtc)
                ->delete();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
